<?php
/**
 * Файл класса UploadParams.php
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage;

class UploadParams
{
    public $group_code;
    public $object_id;
    public $save;

    public function __construct(array $config = [])
    {
        list(
            $this->group_code,
            $this->object_id,
            $this->save,
            ) = $config;
    }
}