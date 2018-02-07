<?php
/**
 * Файл класса UploadEvent
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

use chulakov\filestorage\models\BaseFile;

class UploadEvent extends \yii\base\Event
{
    /**
     * Событие загрузки файлов
     */
    const UPLOAD_EVENT = 'eventUpload';

    /**
     * @var BaseFile[]
     */
    public $uploadedFiles = [];

    /**
     * Добавление файла к событию для получения результата
     *
     * @param BaseFile $file
     */
    public function addUploadedFile($file)
    {
        if (!is_array($file)) {
            $file = [$file];
        }
        foreach ($file as $item) {
            $this->uploadedFiles[] = $item;
        }
    }
}
