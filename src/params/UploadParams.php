<?php
/**
 * Файл класса UploadParams
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

class UploadParams
{
    /**
     * @var string Категория файла
     */
    public $group_code;
    /**
     * @var integer Идентификатор родительской модели
     */
    public $object_id = null;
    public $accessRole = null;
    /**
     * Базовый конструктор параметров генерации пути
     *
     * @param string $group
     */
    public function __construct($group = 'default')
    {
        $this->group_code = $group;
    }
}
