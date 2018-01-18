<?php
/**
 * Файл класса PathParams
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

/**
 * Class PathParams
 * @package chulakov\filestorage\params
 */
class PathParams
{
    /**
     * @var string Категория превью
     */
    public $group = 'cache';
    /**
     * Шаблон сохранения thumbnails файлов
     *
     * @var string
     */
    public $pathPattern = '{relay}/{group}/{basename}.{ext}';
    /**
     * Шаблон удаления файлов.
     * Испольует glob для поиска всех файлов.
     *
     * @var string
     */
    public $searchPattern = '{relay}/{group}/{basename}*';

    /**
     * Расширенные опции.
     * Добавлюяются последними и переопределяют все вышестоящие токены
     *
     * @var array
     */
    protected $options = [];

    /**
     * Получение расширенного списка параметров для генерации пути файла
     *
     * @return array
     */
    public function config()
    {
        return [
            '{group}' => $this->group,
        ];
    }

    /**
     * Получение динамических опций
     *
     * @return array
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * Конфигурирование
     *
     * @param array $config
     */
    public function configure($config)
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Переопределение токенов генерации пути
     *
     * @param string $name
     * @param string $value
     */
    public function addOption($name, $value)
    {
        $this->options['{' . $name . '}'] = $value;
    }
}