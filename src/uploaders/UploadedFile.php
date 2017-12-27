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
     * @inheritdoc
     */
    public function saveAs($file, $deleteFile = true)
    {
        if ($this->beforeSave($file, $deleteFile)) {
            parent::saveAs($file, $deleteFile);
        }
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
     * Получение MIME типа файла
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * Установить расширение файла
     *
     * @param string $extension
     * @return mixed
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Установить mime тип файла
     *
     * @param string $mime
     * @return mixed
     */
    public function setType($mime)
    {
        $this->type = $mime;
    }

    /**
     * Установить размер файла
     *
     * @param integer $size
     * @return mixed
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Обновление названия файла
     *
     * @param string path
     * @return mixed
     */
    public function uploadPath($path)
    {
        // TODO: Implement uploadPath() method.
    }
}