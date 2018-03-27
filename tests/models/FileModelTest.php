<?php
/**
 * Файл класса FileModelTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use yii\base\Model;

/**
 * Class FileModelTest
 * @package chulakov\filestorage\tests
 */
class FileModelTest extends Model
{
    public $id;
    public $group_code;
    public $object_id;
    public $object_type;
    public $ori_name;
    public $ori_extension;
    public $sys_file;
    public $mime;
    public $size;
    public $created_at;
    public $created_by;
    public $link;

    public static function findById($id)
    {
        if (empty($id)) {
            return null;
        }
        return new static([
            'id' => $id,
        ]);
    }
}