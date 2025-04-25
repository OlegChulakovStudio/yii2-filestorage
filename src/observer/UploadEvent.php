<?php
/**
 * Файл класса UploadEvent
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

use chulakov\filestorage\models\BaseFile;
use yii\base\Event as BaseEvent;

class UploadEvent extends BaseEvent
{
    /**
     * Событие загрузки файлов
     */
    public const UPLOAD_EVENT = 'eventUpload';
    /**
     * Событие после успешной загрузки файлов
     */
    public const AFTER_UPLOAD_EVENT = 'eventAfterUpload';

    /**
     * @var BaseFile[]
     */
    public array $uploadedFiles = [];

    /**
     * Добавление файла к событию для получения результата
     *
     * @param BaseFile|BaseFile[] $file
     */
    public function addUploadedFile(BaseFile|array $file): void
    {
        if (is_array($file) === false) {
            $file = [$file];
        }
        foreach ($file as $item) {
            $this->uploadedFiles[] = $item;
        }
    }
}
