<?php
/**
 * Файл класса BaseFile.php
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models;

use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;

/**
 * Class File
 *
 * @property $mime
 * @property $ori_extension
 * @property $ori_name
 * @property $sys_file
 * @property $size
 * @property $group_code
 * @property $object_id
 */
abstract class BaseFile extends \yii\db\ActiveRecord
{
    /**
     * Базовая структура для group_code объекта
     *
     * Группы:
     *
     * GROUP_DEFAULT - default группа. Базовая группа для хранения данных.
     */

    const GROUP_DEFAULT = 10;

    /**
     * Загруженный файл
     *
     * @var $file \yii\web\UploadedFile
     */
    public $file;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className()
            ]
        ];
    }

    /**
     * BaseFile constructor.
     * @param UploadedFile $uploadedFile
     * @param array $config
     */
    public function __construct(UploadedFile $uploadedFile, array $config = [])
    {
        $this->file = $uploadedFile;

        $this->mime = $this->file->type;
        $this->ori_extension = $this->file->extension;
        $this->ori_name = $this->file->baseName;
        $this->sys_file = uniqid() . '.' . $this->file->extension;
        $this->size = $this->file->size;

        parent::__construct($config);
    }
}