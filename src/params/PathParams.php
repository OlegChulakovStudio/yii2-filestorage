<?php
/**
 * Файл класса PathParams
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

use yii\base\UnknownPropertyException;

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
     * Назначение токена для генерации пути
     *
     * @param string $name
     * @param string $value
     */
    public function addOption($name, $value)
    {
        $this->options['{' . $name . '}'] = $value;
    }

    /**
     * Массовое назначение токенов для генерации пути
     *
     * @param $options
     */
    public function setOptions($options)
    {
        foreach ((array)$options as $name => $option) {
            $this->addOption($name, $option);
        }
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
     * Установка значения через функцию сеттера
     *
     * @param string $name
     * @param mixed $value
     * @throws UnknownPropertyException
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }
}
