<?php
/**
 * Файл класса StorageBehavior
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\behaviors;

use yii\rbac\Item;
use yii\di\Instance;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use chulakov\filestorage\FileStorage;
use chulakov\filestorage\models\BaseFile;

/**
 * Class StorageBehavior
 * @package chulakov\filestorage\behaviors
 */
class StorageBehavior extends Behavior
{
    /**
     * @var BaseFile
     */
    public $owner;
    /**
     * Класс компонента хранилища
     *
     * @var FileStorage
     */
    public $storageComponent = 'fileStorage';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->storageComponent = Instance::ensure($this->storageComponent);
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => [$this, 'deleteFile']
        ];
    }

    /**
     * Возвращает абсолютный или относительный URL-адрес к файлу
     *
     * @param bool $isAbsolute
     * @param Item $role
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws \chulakov\filestorage\exceptions\NoAccessException
     */
    public function getUrl($isAbsolute = false, $role = null)
    {
        return $this->storageComponent->getFileUrl($this->owner, $isAbsolute, $role);
    }

    /**
     * Возвращает полный путь к файлу в файловой системе
     *
     * @param Item $role
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws \chulakov\filestorage\exceptions\NoAccessException
     * @throws \chulakov\filestorage\exceptions\NotFoundFileException
     */
    public function getPath($role)
    {
        return $this->storageComponent->getFilePath($this->owner, $role);
    }

    /**
     * Возвращает URL-адрес до директории нахождения файлов определенного типа
     *
     * @param bool $isAbsolute
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUploadUrl($isAbsolute = false)
    {
        return $this->storageComponent->getUploadUrl($this->owner, $isAbsolute);
    }

    /**
     * Возвращает абсолютный путь к директории хранения файлов определенного типа
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUploadPath()
    {
        return $this->storageComponent->getUploadPath($this->owner);
    }

    /**
     * Удаление файла
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function deleteFile()
    {
        $this->storageComponent->removeFile($this->owner);
    }
}