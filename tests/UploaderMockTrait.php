<?php
/**
 * Файл трейта UploaderMockTrait
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\observer\Event;
use chulakov\filestorage\uploaders\UploadedFile;

/**
 * Trait UploaderMockTrait
 * @package chulakov\filestorage\tests
 */
trait UploaderMockTrait
{
    /**
     * @var UploadedFile
     */
    protected static $imageUploader;
    /**
     * @var UploadedFile
     */
    protected static $fileUploader;

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
        $uploader = $this->createUploader(
            'filename.txt',
            12345,
            'text/plain',
            'txt'
        );
        self::$fileUploader = $uploader;
    }

    /**
     * Создание загрузчика с изображением
     */
    protected function createImageUploader()
    {
        $uploader = $this->createUploader(
            'filename.png',
            12345,
            'image/png',
            'png'
        );
        self::$imageUploader = $uploader;
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

    /**
     * Создание события
     *
     * @param string $path
     * @param bool $deleteFile
     * @return Event
     */
    protected function createEvent($path = 'dummy_path', $deleteFile = false)
    {
        $event = new Event();

        $event->filePath = $path;
        $event->needDelete = $deleteFile;

        $event->needSave = false;
        $event->sender = $this;

        return $event;
    }
}