<?php
/**
 * Файл класса UploadParams
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

/**
 * Class UploadParams
 * @package chulakov\filestorage\params
 */
class UploadParams
{
    /**
     * Категория файла
     *
     * @var string
     */
    public $group_code;
    /**
     * Идентификатор родительской модели
     *
     * @var integer
     */
    public $object_id = 0;
    /**
     * Тип объекта
     *
     * @var string
     */
    public $object_type = null;
    /**
     * Роль доступа
     *
     * @var integer
     */
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
