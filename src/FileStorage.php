<?php
/**
 * Файл класса FileStorage
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage;

use Yii;
use yii\rbac\Item;
use yii\base\Component;
use yii\base\UnknownClassException;
use yii\base\InvalidConfigException;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\services\PathService;
use chulakov\filestorage\services\FileService;
use chulakov\filestorage\uploaders\UploadInterface;
use chulakov\filestorage\observer\ObserverInterface;
use chulakov\filestorage\exceptions\DBModelException;
use chulakov\filestorage\exceptions\NoAccessException;
use chulakov\filestorage\exceptions\NotFoundFileException;
use chulakov\filestorage\exceptions\NotUploadFileException;

/**
 * Class FileStorage
 * @package chulakov\filestorage
 */
class FileStorage extends Component
{
    /**
     * Базовый URL, который будет подставляться при генерации url к файлу.
     * Если false, то будет использован текущий хост при генерации абсолютных URL-адресов
     *
     * @var string|false
     */
    public $storageBaseUrl = false;
    /**
     * Базовый путь к доступной из web директории,
     * в которой будет размещаться директория для хранения файлов [[$storageDir]]
     *
     * @var string
     */
    public $storagePath = '@webroot';
    /**
     * Наименование директории для хранения файлов
     *
     * @var string
     */
    public $storageDir = 'upload';
    /**
     * Если заданы права, то после создания файла они будут принудительно назначены
     *
     * @var number|null
     */
    public $fileMode = 0775;
    /**
     * Патерн генерации пути сохранения файлов
     * Допустимые токены:
     *  {id} - Подставляет в путь идентифиатор модели, для которой загружается файл (object_id)
     *  {group} - Подставляет в путь группу файлов (group_code)
     *
     * @var string
     */
    public $storagePattern = '{group}/{id}';
    /**
     * @var FileService
     */
    protected $service;
    /**
     * @var PathService
     */
    protected $pathService;

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
     * При инициализации проверяем необходимые конфигурационные переменные
     *
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
        $this->storagePath = Yii::getAlias($this->storagePath);

        if (!$this->storageDir) {
            throw new InvalidConfigException("Параметр 'storageDir' должен быть указан");
        }

        // Инициализирование сервиса для работы с путями
        $this->pathService = new PathService($this->storagePath, $this->storageDir, $this->storageBaseUrl);
        $this->pathService->fileMode = $this->fileMode;
    }

    /**
     * Загрузка файла
     *
     * @param UploadInterface|UploadInterface[] $files
     * @param UploadParams $params
     * @return array|BaseFile|null
     * @throws NoAccessException
     * @throws NotUploadFileException
     */
    public function uploadFile($files, UploadParams $params)
    {
        $this->canAccess($params->accessRole);

        /** @var BaseFile[] $result */
        $result = [];
        if ($single = !is_array($files)) {
            $files = [$files];
        }
        try {
            /** @var array $files */
            foreach ($files as $file) {
                $result[] = $this->saveFile($file, $params);
            }
        } catch (\Exception $e) {
            $this->clearFiles($files, $params, $e);
            $this->clearModel($result);
            throw new NotUploadFileException('Не удалось сохранить файл', 0, $e);
        }
        return $single ? array_shift($result) : $result;
    }

    /**
     * Возвращает абсолютный путь к директории хранения файлов определенного типа
     *
     * @param BaseFile $model
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function getUploadPath($model)
    {
        return dirname($this->getFilePath($model));
    }

    /**
     * Возвращает URL-адрес до директории нахождения файлов определенного типа
     *
     * @param BaseFile $model
     * @param bool $isAbsolute возвращать абсолютный (полный) URL
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function getUploadUrl($model, $isAbsolute = false)
    {
        return $this->convertToUrl($this->getUploadPath($model), $isAbsolute);
    }

    /**
     * Возвращает полный путь к файлу в файловой системе
     *
     * @param BaseFile $model
     * @param Item $role
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function getFilePath($model, $role = null)
    {
        $this->canAccess($role, $model);
        return $this->pathService->findPath(
            $model->sys_file, $this->getPathFromModel($model)
        );
    }

    /**
     * Возвращает абсолютный или относительный URL-адрес к файлу
     *
     * @param BaseFile $model
     * @param bool $isAbsolute
     * @param string|Item $role
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function getFileUrl($model, $isAbsolute = false, $role = null)
    {
        return $this->pathService->convertToUrl($this->getFilePath($model, $role), $isAbsolute);
    }

    /**
     * Формирование абсолютного пути до файлов с созданием новой директории, если ее еще не существует
     *
     * @param string $path
     * @return string
     */
    public function getAbsolutePath($path)
    {
        return $this->pathService->getAbsolutePath($path);
    }

    /**
     * Удаление модели и сохраненого файла
     *
     * @param BaseFile $model
     */
    public function removeFile($model)
    {
        $this->pathService->removeFile($model->getPath());
    }

    /**
     * Формирование полного пути до файла по шаблону
     *
     * @param string $path
     * @param PathParams $params
     * @return string
     */
    public function makePath($path, PathParams $params)
    {
        return $this->pathService->makePath($path, $params);
    }

    /**
     * Получение файлов, подходящих для удаления
     *
     * @param string $path
     * @param PathParams $params
     * @return array
     */
    public function searchAllFiles($path, PathParams $params)
    {
        return $this->pathService->searchAllFiles($path, $params);
    }

    /**
     * Добавление в URL адрес исходной точки
     *
     * @param string $path
     * @param bool $isAbsolute
     * @return string
     */
    public function convertToUrl($path, $isAbsolute = false)
    {
        return $this->pathService->convertToUrl($path, $isAbsolute);
    }

    /**
     * Получить полный путь системного файла
     *
     * @param BaseFile $model
     * @return string
     */
    public function getFullSysPath($model)
    {
        return $this->getAbsolutePath($model->sys_file);
    }

    /**
     * Проверка прав доступа к файлу
     *
     * @param Item $role
     * @param BaseFile $model
     * @return bool
     * @throws NoAccessException
     */
    protected function canAccess($role = null, $model = null)
    {
        if (empty($role)) {
            return true;
        }
        $params = [];
        if (!is_null($model)) {
            $params['file'] = $model;
        }
        if (\Yii::$app->user->can($role, $params)) {
            return true;
        }
        throw new NoAccessException('Нет прав доступа не сохранение файла.');
    }

    /**
     * Сохранение файла и создание для него модели с мета данными
     *
     * @param UploadInterface $file
     * @param UploadParams $params
     * @return BaseFile|null
     * @throws NotUploadFileException
     * @throws DBModelException
     */
    protected function saveFile(UploadInterface $file, UploadParams $params)
    {
        // Генерация всех необходимых частей для сохранения файла
        $path = $this->getSavePath($params);
        $full = $this->getAbsolutePath($path);
        if (!$this->pathService->checkPath($full)) {
            throw new NotUploadFileException('Недостаточно прав для сохранения файла.');
        }
        // Сохранение файла и создание модели с данными о файле
        $file->saveAs($full . DIRECTORY_SEPARATOR . $file->getSysName());
        if ($model = $this->createModel($file, $params)) {
            $model->setSystemFile($file->getSysName(), $path);
            if ($this->service->save($model)) {
                return $model;
            }
        }
        throw new NotUploadFileException('Не удалось сохранить данные о файле: ' . $file->getBaseName());
    }

    /**
     * Формирование относительного пути с учетом настроек и переданных параметров
     *
     * @param UploadParams $params
     * @return string
     */
    protected function getSavePath(UploadParams $params)
    {
        $pathParams = new PathParams();
        $pathParams->group = $params->group_code;
        $pathParams->pathPattern = $this->storagePattern;
        return $this->pathService->savedPath($pathParams, [
            '{id}' => $params->object_id,
        ]);
    }

    /**
     * Создание модели мета информации о файле
     *
     * @param UploadInterface $file
     * @param UploadParams $params
     * @return BaseFile|null
     */
    protected function createModel(UploadInterface $file, UploadParams $params)
    {
        try {
            if ($this->isImage($file->getType())) {
                return $this->service->createImage($file, $params);
            }
            return $this->service->createFile($file, $params);
        } catch (UnknownClassException $e) {
            return null;
        }
    }

    /**
     * Проверка файла на изображение
     *
     * @param string $mime
     * @return bool
     */
    protected function isImage($mime)
    {
        return strpos($mime, 'image') !== false;
    }

    /**
     * Зачистка моделей
     *
     * @param BaseFile[] $models
     */
    protected function clearModel($models)
    {
        foreach ($models as $model) {
            try {
                $model->delete();
            } catch (\Exception $e) {
                Yii::error($e);
            } catch (\Throwable $t) {
                Yii::error($t);
            }
        }
    }

    /**
     * Зачистка файлов
     *
     * @param UploadInterface[]|ObserverInterface[] $files
     * @param UploadParams $params
     * @param \Exception|null $e
     */
    protected function clearFiles($files, $params, $e = null)
    {
        $path = $this->getAbsolutePath($this->getSavePath($params));
        foreach ($files as $file) {
            try {
                $file->deleteFile($path . DIRECTORY_SEPARATOR . $file->getSysName(), $e);
            } catch (\Exception $e) {
                Yii::error($e);
            }
        }
    }

    /**
     * Формирование пути до загружаемых файлов из данных модели
     *
     * @param BaseFile $model
     * @return string
     */
    protected function getPathFromModel($model)
    {
        return $this->getSavePath($this->getParamsFromModel($model));
    }

    /**
     * Получение параметров загрузки из модели
     *
     * @param BaseFile $model
     * @return UploadParams
     */
    protected function getParamsFromModel($model)
    {
        $params = new UploadParams($model->group_code);
        if ($model->object_id) {
            $params->object_id = $model->object_id;
        }
        return $params;
    }
}
