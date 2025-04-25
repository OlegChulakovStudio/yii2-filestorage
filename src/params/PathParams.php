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
     * Категория превью
     */
    public string $group = 'cache';
    /**
     * Шаблон сохранения thumbnails файлов
     */
    public string $pathPattern = '{relay}/{group}/{basename}.{ext}';
    /**
     * Шаблон удаления файлов.
     * Использует glob для поиска всех файлов.
     */
    public string $searchPattern = '{relay}/{group}/{basename}*';

    /**
     * Расширенные опции.
     * Добавляются последними и переопределяют все вышестоящие токены
     */
    protected array $options = [];

    /**
     * Получение расширенного списка параметров для генерации пути файла
     */
    public function config(): array
    {
        return [
            '{group}' => $this->group,
        ];
    }

    /**
     * Получение динамических опций
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Назначение токена для генерации пути
     */
    public function addOption(string $name, string $value): void
    {
        $this->options['{' . $name . '}'] = $value;
    }

    /**
     * Массовое назначение токенов для генерации пути
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $name => $option) {
            $this->addOption($name, $option);
        }
    }

    /**
     * Конфигурирование
     */
    public function configure(array $config): void
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Установка значения через функцию сеттера
     * @throws UnknownPropertyException
     */
    public function __set(string $name, mixed $value): void
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }
}
