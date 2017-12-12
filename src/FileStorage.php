<?php
/**
 * Файл класса FileStorage.php
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\UnknownClassException;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;
use chulakov\filestorage\models\File;
use chulakov\filestorage\uploaders\UploadInterface;
use chulakov\filestorage\models\services\FileService;
use chulakov\filestorage\exceptions\NotUploadFileException;

class FileStorage extends Component
{
    /**
     * Базовый URL, который будет подставляться при генерации url к файлу.
     * Если false, то будет использован текущий хост при генерации абсолютных URL-адресов
     * @var string|false
     */
    public $storageBaseUrl = false;
    /**
     * Базовый путь к доступной из web директории,
     * в которой будет размещаться директория для хранения файлов [[$storageDir]]
     * @var string
     */
    public $storagePath = '@webroot';
    /**
     * Наименование директории для хранения файлов
     * @var string
     */
    public $storageDir = 'upload';
    /**
     * @var string
     */
    public $storagePattern = '{group}/{id}';
    /**
     * Если заданы права, то после создания файла они будут принудительно назначены
     *
     * @var number|null
     */
    public $fileMode;

    /**
     * @var FileService
     */
    protected $service;

    /**
     * Конструктор с зависимостью от сервиса
     *
     * @param FileService $service
     * @param array $config
     */
    public function __construct(FileService $service, array $config = [])
    {
        $this->service = $service;
        parent::__construct($config);
    }

    /**
     * Загрузка файл
     *
     * @param UploadInterface|UploadInterface[] $files
     * @param UploadParams $params
     * @return mixed
     * @throws UnknownClassException
     * @throws NotUploadFileException
     * @throws \Exception
     */
    public function uploadFile($files, UploadParams $params)
    {
        if (!is_array($files)) {
            return $this->saveFile($files, $params);
        }

        $result = [];
        foreach ($files as $file) {
            try {
                $result[] = $this->saveFile($file, $params);
            } catch (\Exception $e) {
                foreach ($result as $item) {
                    $item->delete();
                }
                throw $e;
            }
        }
        return $result;
    }

    /**
     * @param UploadInterface $file
     * @param UploadParams $params
     * @return File|null
     * @throws UnknownClassException
     * @throws NotUploadFileException
     */
    protected function saveFile(UploadInterface $file, UploadParams $params)
    {
        $path = $this->getSavePath($params);
        $name = $this->getSaveName($file->getExtension());

        if (!$this->checkPath($path)) {
            throw new NotUploadFileException('Нет доступа к каталогу для сохранения файла.');
        }

        $full = $path . '/' . $name;
        $file->saveAs(Yii::getAlias($this->storagePath) . '/' . $full);

        $model = $this->service->createFile($file, $params);
        $model->sys_file = $full;
        if (!$model->save()) {
            throw new NotUploadFileException('Не удалось сохранить данные о файле: ' . $file->getBaseName());
        }

        return $model;
    }

    protected function getSavePath(UploadParams $params)
    {
        return implode('/', array_filter([
            $this->storageDir, strtr($this->storagePattern, [
                '{id}' => $params->object_id,
                '{group}' => $params->group_code,
            ])
        ]));
    }

    protected function getSaveName($ext)
    {
        return implode('.', array_filter([uniqid(), $ext]));
    }

    /**
     * Сохранение файла
     *
     * @return boolean
     */
    protected function checkPath($path)
    {
        $full = Yii::getAlias($this->storagePath) . '/' . $path;
        if (!is_dir($full)) {
            try {
                return FileHelper::createDirectory($full, $this->fileMode);
            } catch (Exception $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * Проверка файла на изображение
     *
     * @param array $allowExtensions
     * @return bool
     */
    protected function isImage(array $allowExtensions = [])
    {
        // возможность модификации разрешенных параметров для проверки на изображение
        if (empty($allowExtensions)) {
            $allowExtensions = [
                'jpg',
                'jpeg',
                'png'
            ];
        }

        return $this->hasMimeType($allowExtensions);
    }

    /**
     * Проверка файла на нужный тип файла
     *
     * @param array $mime
     * @return bool
     */
    protected function hasMimeType(array $mime = [])
    {
        $extensions = explode(', ', $this->fileModel->ori_extension);
        foreach ($mime as $value) {
            if (!in_array($value, $extensions)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Возвращает абсолютный путь к директории хранения файлов определенного типа
     *
     * @return string
     */
    public function getUploadPath()
    {
        $path = $this->storagePath . DIRECTORY_SEPARATOR . $this->storageDir . DIRECTORY_SEPARATOR . $this->fileModel->group_code;

        if ($this->fileModel->object_id) {
            $path .= DIRECTORY_SEPARATOR . $this->fileModel->object_id;
        }

        return FileHelper::normalizePath(Yii::getAlias($path));
    }

    /**
     * Возвращает полный путь к файлу в файловой системе
     * @return string
     */
    public function getFilePath()
    {
        return $this->uploadPath . DIRECTORY_SEPARATOR . $this->fileModel->sys_file;
    }

    /**
     * Возвращает URL-адрес до директории нахождения файлов определенного типа
     *
     * @param bool $isAbsolute возвращать абсолютный (полный) URL
     * @return string
     */
    public function getUploadUrl($isAbsolute = false)
    {
        $url = DIRECTORY_SEPARATOR . $this->storageDir . DIRECTORY_SEPARATOR . $this->fileModel->group_code;

        if ($this->fileModel->object_id) {
            $url .= DIRECTORY_SEPARATOR . $this->fileModel->object_id;
        }

        if ($this->storageBaseUrl !== false) {
            $url = Url::to($this->storageBaseUrl . $url, true);
        } else {
            if ($isAbsolute) {
                $url = Url::base(true) . $url;
            }
        }

        return $url;
    }

    /**
     * Возвращает абсолютный или относительный URL-адрес к файлу
     *
     * @param bool $isAbsolute возвращать абсолютный (полный) URL
     * @return string
     */
    public function getFileUrl($isAbsolute = false)
    {
        return $this->getUploadUrl($isAbsolute) . DIRECTORY_SEPARATOR . $this->fileModel->sys_file;
    }

    /**
     * Проверяет существование директории загрузок и если она не существует, то создает ее
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    public function touchUploadDir()
    {
        return $this->createDirectory($this->uploadPath);
    }

    /**
     * Проверяет существование директории загрузок кеша и если она не существует, то создает ее
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    public function touchUploadCacheDir()
    {
        return $this->createDirectory($this->uploadCachePath);
    }

    /**
     * @param $path
     * @return bool
     * @throws \yii\base\Exception
     */
    public function createDirectory($path)
    {
        return !file_exists($path) ? FileHelper::createDirectory($path) : true;
    }

    /**
     * Вернуть модель файла
     *
     * @return File|null
     */
    public function getFile()
    {
        return $this->fileModel;
    }

    /**
     * Получить загруженный файл
     *
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->fileModel->file;
    }

    /**
     * При инициализации проверяем необходимые конфигурационные переменные
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (is_null($this->storageBaseUrl) || ($this->storageBaseUrl !== false && trim($this->storageBaseUrl) === '')) {
            throw new InvalidConfigException("Параметр 'storageBaseUrl' имеет неверное значение");
        }

        if (!$this->storagePath) {
            throw new InvalidConfigException("Параметр 'storagePath' должен быть указан");
        }

        if (!$this->storageDir) {
            throw new InvalidConfigException("Параметр 'storageDir' должен быть указан");
        }
    }
}