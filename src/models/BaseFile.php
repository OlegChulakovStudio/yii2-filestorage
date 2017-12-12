<?php
/**
 * Файл класса BaseFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

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
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
}
