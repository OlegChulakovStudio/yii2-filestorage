<?php
/**
 * Файл класса Migration
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\controllers;

use chulakov\filestorage\FileStorage;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\uploaders\LocalUploadedFile;
use chulakov\filestorage\exceptions\NoAccessException;
use chulakov\filestorage\exceptions\NotUploadFileException;

/**
 * Класс дополнительного функционала загрузки файла
 */
abstract class Migration extends \yii\db\Migration
{
    /**
     * Компонент FileStorage
     *
     * @var FileStorage|string|array
     */
    public $fileStorage = 'fileStorage';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->fileStorage = \Yii::$app->get($this->fileStorage);
    }

    /**
     * Загрузка файла
     *
     * @param string $path
     * @param UploadParams $params
     * @return array|BaseFile|null
     * @throws NoAccessException
     * @throws NotUploadFileException
     */
    public function upload($path, UploadParams $params)
    {
        return $this->fileStorage->uploadFile(
            new LocalUploadedFile($path),
            $params
        );
    }
}