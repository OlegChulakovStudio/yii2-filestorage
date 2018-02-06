<?php
/**
 * Файл трейта UploaderMockTrait
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\uploaders\UploadedFile;

/**
 * Trait UploaderMockTrait
 * @package chulakov\filestorage\tests
 */
trait UploaderMockTrait
{

    /**
     * Генерирование фейковых загрузчиков
     */
    protected function generateFakeUploader()
    {
        $this->createFileUploader();
        $this->createImageUploader();
    }

    /**
     * Сохрание загрузчика с файлом
     */
    protected function createFileUploader()
    {
        return $this->createUploader(
            'filename.txt',
            12345,
            'text/plain',
            'txt'
        );
    }

    /**
     * Создание загрузчика с изображением
     */
    protected function createImageUploader()
    {
        return $this->createUploader(
            'filename.png',
            12345,
            'image/png',
            'png'
        );
    }

    /**
     * Создание загрузчика
     *
     * @param string $name
     * @param integer $size
     * @param string $mime
     * @param string $ext
     * @return UploadedFile
     */
    protected function createUploader($name, $size, $mime, $ext)
    {
        $uploader = new UploadedFile();
        $uploader->setName($name);
        $uploader->setSize($size);
        $uploader->setType($mime);
        $uploader->setExtension($ext);
        return $uploader;
    }
}