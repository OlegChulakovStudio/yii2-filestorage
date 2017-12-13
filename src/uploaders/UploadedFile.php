<?php
/**
 * Файл класса UploadedFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

class UploadedFile extends \yii\web\UploadedFile implements UploadInterface
{
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
}
