<?php
/**
 * Файл класса UploadParams
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage;

class UploadParams
{
    public $group_code;
    public $object_id = null;
    public $save = false;

    public function __construct($group = 'default')
    {
        $this->group_code = $group;
    }
}
