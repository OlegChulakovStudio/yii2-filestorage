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
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;
use chulakov\filestorage\models\File;
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
     * Файл, для которого производится вычисление путей
     * @var File|null
     */
    protected $fileModel;

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
     * Загрузить файл
     *
     * @param UploadedFile $file
     * @param array $config
     * @param bool $save
     * @return bool
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function upload(UploadedFile $file, array $config = [], $save = true)
    {
        /** @var FileService $fileService */
        $fileService = Yii::createObject(FileService::class);
        $this->fileModel = $fileService->create($file, $config);

        if (!$this->fileModel) {
            throw new NotUploadFileException('Файл не был загружен.');
        }

        if ($save && !$this->saveFile()) {
            return false;
        }

        return true;
    }

    /**
     * Сохранение файла
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    protected function saveFile()
    {
        if ($this->fileModel) {
            if (!$this->touchUploadDir()) {
                return false;
            }
            return $this->fileModel->file->saveAs(
                $this->getFilePath()
            );
        }
        return false;
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