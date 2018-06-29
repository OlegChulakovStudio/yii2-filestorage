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
     * Класс модели, который необходимо создать при сохранении информации о файле
     *
     * @var string
     */
    public $modelClass = null;
    /**
     * Роль доступа
     *
     * @var string
     */
    public $accessRole = null;
    /**
     * Шаблон сохранения thumbnails файлов
     *
     * @var string
     */
    public $pathPattern;
    /**
     * Расширенные опции.
     * Добавлюяются последними и переопределяют все вышестоящие токены
     *
     * @var array
     */
    public $options = [];

    /**
     * Базовый конструктор параметров генерации пути
     *
     * @param string $group
     */
    public function __construct($group = 'default')
    {
        $this->group_code = $group;
    }

    /**
     * Получение всех настроек для формирования пути
     *
     * @return array
     */
    public function options()
    {
        $options = [];
        foreach ($this->options as $name => $value) {
            $options['{' . $name . '}'] = $value;
        }
        return array_merge([
            '{id}' => $this->object_id,
            '{type}' => $this->object_type,
        ], $options);
    }
}
