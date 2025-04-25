<?php
/**
 * Файл класса UploadParams
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

use yii\rbac\Item;

/**
 * Class UploadParams
 * @package chulakov\filestorage\params
 */
class UploadParams
{
    /**
     * Категория файла
     */
    public string $group_code;
    /**
     * Идентификатор родительской модели
     */
    public int $object_id = 0;
    /**
     * Тип объекта
     */
    public ?string $object_type = null;
    /**
     * Класс модели, который необходимо создать при сохранении информации о файле
     */
    public ?string $modelClass = null;
    /**
     * Роль доступа
     */
    public Item|string|null $accessRole = null;
    /**
     * Шаблон сохранения thumbnails файлов
     */
    public ?string $pathPattern = null;
    /**
     * Расширенные опции.
     * Добавляются последними и переопределяют все вышестоящие токены
     */
    public array $options = [];

    /**
     * Базовый конструктор параметров генерации пути
     */
    public function __construct(string $group = 'default')
    {
        $this->group_code = $group;
    }

    /**
     * Получение всех настроек для формирования пути
     */
    public function options(): array
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
