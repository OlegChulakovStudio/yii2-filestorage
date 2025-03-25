<?php
/**
 * Файл класса Migration
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\controllers;

use chulakov\filestorage\exceptions\NoAccessException;
use chulakov\filestorage\exceptions\NotUploadFileException;
use chulakov\filestorage\FileStorage;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\uploaders\LocalUploadedFile;
use Yii;

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
    public function init(): void
    {
        parent::init();

        $this->fileStorage = Yii::$app->get($this->fileStorage);
    }

    /**
     * Загрузка файла
     *
     * @return BaseFile[]|BaseFile|null
     * @throws NoAccessException
     * @throws NotUploadFileException
     */
    public function upload(string $path, UploadParams $params): BaseFile|array|null
    {
        return $this->fileStorage->uploadFile(new LocalUploadedFile($path), $params);
    }
}
