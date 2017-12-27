<?php
/**
 * Файл класса BaseFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

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
abstract class BaseFile extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Установка системного пути до сохраненого файла
     *
     * @param string $name
     * @param string|null $path
     */
    public function setSystemFile($name, $path = null)
    {
        $this->sys_file = implode('/', array_filter([$path, $name]));
    }

    /**
     * Проверка файла на изображение
     *
     * @return bool
     */
    public function isImage()
    {
        return strpos($this->mime, 'image') === 0;
    }
}
