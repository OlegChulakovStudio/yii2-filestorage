<?php
/**
 * Файл класса StorageBehavior
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\behaviors;

use yii\rbac\Item;
use yii\base\Behavior;

/**
 * Class StorageBehavior
 * @package chulakov\filestorage\behaviors
 */
class StorageBehavior extends Behavior
{
    /**
     * Компонент для работы с хранилищем
     *
     * @var string
     */
    public $storageComponent = 'fileStorage';

    /**
     * Возвращает абсолютный или относительный URL-адрес к файлу
     *
     * @param bool $isAbsolute
     * @param Item $role
     * @return string
     */
    public function getUrl($isAbsolute = false, $role = null)
    {
        return \Yii::$app->{$this->storageComponent}->getFileUrl($this->owner, $isAbsolute, $role);
    }

    /**
     * Возвращает полный путь к файлу в файловой системе
     *
     * @param Item $role
     * @return string
     */
    public function getPath($role)
    {
        return \Yii::$app->{$this->storageComponent}->getFilePath($this->owner, $role);
    }

    /**
     * Возвращает URL-адрес до директории нахождения файлов определенного типа
     * @param bool $isAbsolute
     * @return string
     */
    public function getUploadUrl($isAbsolute = false)
    {
        return \Yii::$app->{$this->storageComponent}->getUploadUrl($this->owner, $isAbsolute);
    }

    /**
     * Возвращает абсолютный путь к директории хранения файлов определенного типа
     * @return string
     */
    public function getUploadPath()
    {
        return \Yii::$app->{$this->storageComponent}->getUploadPath($this->owner);
    }
}