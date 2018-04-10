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
use chulakov\filestorage\exceptions\NoAccessException;
use chulakov\filestorage\exceptions\NotFoundFileException;

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
    public $fileStorage = 'fileStorage';

    /**
     * @inheritdoc
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
            ActiveRecord::EVENT_AFTER_DELETE => [$this, 'deleteFile']
        ];
    }

    /**
     * Возвращает абсолютный или относительный URL-адрес к файлу
     *
     * @param bool $isAbsolute
     * @param string|Item|null $role
     * @return string
     * @throws NoAccessException
     */
    public function getUrl($isAbsolute = false, $role = null)
    {
        try {
            return $this->fileStorage->getFileUrl($this->owner, $isAbsolute, $role);
        } catch (NotFoundFileException $e) {
            return $this->exceptionResult($e);
        }
    }

    /**
     * Возвращает полный путь к файлу в файловой системе
     *
     * @param string|Item|null $role
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function getPath($role = null)
    {
        return $this->fileStorage->getFilePath($this->owner, $role);
    }

    /**
     * Возвращает URL-адрес до директории нахождения файлов определенного типа
     *
     * @param bool $isAbsolute
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function getUploadUrl($isAbsolute = false)
    {
        return $this->fileStorage->getUploadUrl($this->owner, $isAbsolute);
    }

    /**
     * Возвращает абсолютный путь к директории хранения файлов определенного типа
     *
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function getUploadPath()
    {
        return $this->fileStorage->getUploadPath($this->owner);
    }

    /**
     * Удаление файла
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function deleteFile()
    {
        $this->fileStorage->removeFile($this->owner);
    }

    /**
     * Возвращаемое значение при отсутствии файла
     *
     * @param \Exception $e
     * @return bool|string
     */
    protected function exceptionResult($e)
    {
        \Yii::error($e);
        return $this->owner->isImage()
            ? $this->fileStorage->getNoImage()
            : '';
    }
}