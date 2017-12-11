<?php
/**
 * Файл класса File.php
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models;

use yii\web\UploadedFile;

class File extends BaseFile
{
    /**
     * File constructor.
     * @param UploadedFile $uploadedFile
     * @param array $config
     */
    public function __construct(UploadedFile $uploadedFile, array $config = [])
    {
        parent::__construct($uploadedFile, $config);
    }
}