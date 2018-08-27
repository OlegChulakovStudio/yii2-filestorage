<?php
/**
 * Файл класса FileUploadBehavior
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\behaviors;

use yii\rbac\Item;
use yii\base\Model;
use yii\di\Instance;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\base\InvalidConfigException;
use chulakov\filestorage\FileStorage;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\observer\UploadEvent;
use chulakov\filestorage\uploaders\UploadInterface;
use chulakov\filestorage\exceptions\NoAccessException;
use chulakov\filestorage\exceptions\NotUploadFileException;

/**
 * Поведение автоматической инициализации файла и загрузки его согласно настроек
 *
 * @package chulakov\filestorage\behaviors
 */
class FileUploadBehavior extends Behavior
{
    /**
     * @var Model|ActiveRecord
     */
    public $owner;
    /**
     * @var string
     */
    public $attribute = 'file';
    /**
     * @var string
     */
    public $group = 'default';
    /**
     * @var string
     */
    public $type = null;
    /**
     * @var bool
     */
    public $skipOnEmpty = false;
    /**
     * @var bool Устанавливать ошибку вместо исключения
     */
    public $setErrors = false;
    /**
     * @var string
     */
    public $uploadClass = 'chulakov\filestorage\params\UploadParams';
    /**
     * @var string
     */
    public $uploadPattern;
    /**
     * @var array|callable
     */
    public $uploadOptions;
    /**
     * @var string|UploadInterface
     */
    public $repository = 'chulakov\filestorage\uploaders\UploadedFile';
    /**
     * @var array
     */
    public $repositoryOptions = [];
    /**
     * @var string|array|FileStorage
     */
    public $fileStorage = 'fileStorage';
    /**
     * @var string|Item
     */
    public $accessRole = null;
    /**
     * @var string Класс модели, который необходимо создать при сохранении информации о файле
     */
    public $modelClass = null;

    /**
     * @var bool
     */
    protected $isUploaded = false;
    /**
     * @var bool
     */
    protected $isMultiple = false;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->fileStorage = Instance::ensure($this->fileStorage);
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Model::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            UploadEvent::UPLOAD_EVENT => 'onUpload',
        ];
    }

    /**
     * Загрузка файлов через вызов события загрузки
     */
    public function upload()
    {
        $event = new UploadEvent();
        $this->owner->trigger(UploadEvent::UPLOAD_EVENT, $event);
        if (!empty($event->uploadedFiles)) {
            if ($this->isMultiple) {
                return $event->uploadedFiles;
            }
            return array_shift($event->uploadedFiles);
        }
        return null;
    }

    /**
     * Проверка и инициализация данных перед валидацией модели
     */
    public function beforeValidate()
    {
        if ($this->isInstances($this->owner->{$this->attribute})) {
            return;
        }
        $files = $this->getInstances();
        if (!empty($files)) {
            $this->configureInstances($files);
            if ($this->isInstances($files)) {
                $this->owner->{$this->attribute} = $files;
            }
        }
    }

    /**
     * Загрузка и сохранение файлов
     *
     * @param UploadEvent $event
     * @throws NoAccessException
     * @throws NotUploadFileException
     * @throws InvalidConfigException
     */
    public function onUpload($event)
    {
        if ($this->isUploaded) {
            return;
        }
        /** @var UploadInterface $files */
        $files = $this->owner->{$this->attribute};
        if (!$this->isInstances($files)) {
            if (empty($files) && !$this->skipOnEmpty) {
                throw new NotUploadFileException('Нет файлов для сохранения.');
            }
            return;
        }
        try {
            /** @var UploadParams $params */
            $params = \Yii::$container->get(
                $this->uploadClass, [$this->group], $this->getUploadProperties()
            );
        } catch (\Exception $e) {
            throw new NotUploadFileException('Не удалось инициализировать DTO.', 0, $e);
        }
        try {
            $event->addUploadedFile($this->fileStorage->uploadFile($files, $params));
        } catch (NotUploadFileException $e) {
            if ($this->setErrors) {
                $this->owner->addError($this->attribute, $e->getMessage());
            } else {
                throw $e;
            }
        }
        $this->isUploaded = true;
    }

    /**
     * Формирование параметров для загрузки файла
     *
     * @return array
     */
    protected function getUploadProperties()
    {
        $properties = [
            'object_type' => $this->type,
            'accessRole'  => $this->accessRole,
            'modelClass'  => $this->modelClass,
            'pathPattern' => $this->uploadPattern,
        ];
        // Идентификатор связки с моделью
        if ($this->owner->hasMethod('getPrimaryKey')) {
            $primaryKeys = $this->owner->getPrimaryKey();
            if (is_array($primaryKeys)) {
                $primaryKeys = implode('-', $primaryKeys);
            }
            $properties['object_id'] = $primaryKeys;
        }
        // Расширенные параметры для формирования пути сохранения
        if ($extraProperties = $this->uploadOptions) {
            if ($extraProperties instanceof \Closure) {
                $extraProperties = call_user_func($extraProperties, $properties);
            }
            $properties['options'] = (array)$extraProperties;
        }
        return $properties;
    }

    /**
     * Проверка, объявлены ли уже данные в модели
     *
     * @param mixed $model
     * @return bool
     */
    protected function isInstances($model)
    {
        return $model instanceof UploadInterface;
    }

    /**
     * Инициализация данных для модели
     *
     * @return mixed
     */
    protected function getInstances()
    {
        $repository = $this->repository;
        $file = $repository::getInstance($this->owner, $this->attribute);
        if (empty($file)) {
            $file = $repository::getInstanceByName($this->attribute);
        }
        return $file;
    }

    /**
     * Конфигурация загруженного файла
     *
     * @param UploadInterface $file
     */
    protected function configureInstances($file)
    {
        if (!empty($this->repositoryOptions)) {
            $file->configure($this->repositoryOptions);
        }
    }
}
