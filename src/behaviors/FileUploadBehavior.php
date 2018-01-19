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
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\uploaders\UploadInterface;
use chulakov\filestorage\exceptions\NoAccessException;
use chulakov\filestorage\exceptions\NotUploadFileException;

/**
 * Class FileUploadBehavior
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
    public $attribute;
    /**
     * @var string
     */
    public $group = 'default';
    /**
     * @var string|UploadInterface
     */
    public $repository;
    /**
     * @var array
     */
    public $repositoryOptions;
    /**
     * @var string|array|FileStorage
     */
    public $fileStorage;
    /**
     * @var string|Item
     */
    public $accessRole;

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
        ];
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

    /**
     * Загрузка и сохранение файлов
     *
     * @return mixed
     * @throws NotUploadFileException
     * @throws NoAccessException
     */
    public function upload()
    {
        /** @var UploadInterface $files */
        $files = $this->owner->{$this->attribute};
        if (!$this->isInstances($files)) {
            throw new NotUploadFileException('Нет файлов для сохранения.');
        }
        $params = new UploadParams($this->group);
        $params->accessRole = $this->accessRole;
        if (method_exists($this->owner, 'getPrimaryKey')) {
            $params->object_id = $this->owner->getPrimaryKey();
        }
        return $this->fileStorage->uploadFile($files, $params);
    }
}
