<?php
/**
 * Файл класса UploadedFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

/**
 * Class UploadedFile
 * @package chulakov\filestorage\uploaders
 */
class UploadedFile extends \yii\web\UploadedFile implements UploadInterface, ObserverInterface
{
    /**
     * Подключение реализации функционала Observer
     */
    use ObserverTrait;

    /**
     * Конфигурация компонента
     *
     * @param array $config
     * @return mixed|void
     * @throws \yii\base\InvalidConfigException
     */
    public function configure($config)
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
        $this->initListener();
    }

    /**
     * @inheritdoc
     */
    public function saveAs($file, $deleteFile = true)
    {
        if ($this->beforeSave($file, $deleteFile)) {
            parent::saveAs($file, false);
            $this->name = basename($file);
        }
        if ($deleteFile) {
            $this->deleteFile($this->tempName);
        }
    }

    /**
     * Удаление файла
     *
     * @param string $filePath
     * @return bool
     */
    protected function deleteFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        return true;
    }

    /**
     * Псевдособытие сохранения
     *
     * @param string $filePath
     * @param bool $deleteFile
     * @return bool
     */
    protected function beforeSave($filePath, $deleteFile = true)
    {
        $event = new Event($filePath, $deleteFile);

        $event->needSave = true;
        $event->sender = $this;

        $this->trigger(Event::SAVE_EVENT, $event);
        return $event->needSave;
    }

    /**
     * Получить ссылку на файл
     *
     * @return string
     */
    public function getFile()
    {
        return $this->tempName;
    }

    /**
     * @return string Контент файла
     */
    public function getContent()
    {
        return file_get_contents($this->tempName);
    }

    /**
     * Получение имени файла после сохранения
     *
     * @return string
     */
    public function getSavedName()
    {
        return $this->name;
    }

    /**
     * Установка полного имени файла
     *
     * @param string $name
     */
    public function setName($name)
    {
         $this->name = $name;
    }

    /**
     * Получение MIME типа файла
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Установить mime тип файла
     *
     * @param string $mime
     */
    public function setType($mime)
    {
        $this->type = $mime;
    }

    /**
     * Получение размера файла
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Установить размер файла
     *
     * @param integer $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }
}