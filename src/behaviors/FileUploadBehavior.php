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
use chulakov\filestorage\FileStorage;
use chulakov\filestorage\observer\UploadEvent;
use chulakov\filestorage\params\UploadParams;
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
     * @var Model
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
     * @var bool
     */
    protected $isUploaded = false;

    /**
     * @throws \yii\base\InvalidConfigException
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
            if (count($event->uploadedFiles) > 1) {
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
     */
    public function onUpload($event)
    {
        if ($this->isUploaded) {
            return;
        }
        /** @var UploadInterface $files */
        $files = $this->owner->{$this->attribute};
        if (!$this->isInstances($files)) {
            if (empty($files)) {
                throw new NotUploadFileException('Нет файлов для сохранения.');
            }
            return;
        }
        $params = new UploadParams($this->group);
        $params->accessRole = $this->accessRole;
        $params->object_type = $this->type;
        if (method_exists($this->owner, 'getPrimaryKey')) {
            $params->object_id = $this->owner->getPrimaryKey();
        }
        $event->addUploadedFile($this->fileStorage->uploadFile($files, $params));
        $this->isUploaded = true;
    }

    /**
     * Проверка, объявлены ли уже данные в модели
     *
     * @param mixed $model
     * @return bool
     */
    protected function isInstances($model)
    {
        return $model instanceof $this->repository;
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
