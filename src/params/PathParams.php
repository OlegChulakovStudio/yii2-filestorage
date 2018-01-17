<?php
/**
 * Файл класса PathParams
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

use yii\helpers\ArrayHelper;

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
    public $pathPattern = '{group}/{basename}.{ext}';
    /**
     * Шаблон удаления файлов.
     * Испольует glob для поиска всех файлов.
     *
     * @var string
     */
    public $deletePattern = '{group}/{basename}/*';

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
     * Получить путь файла относительно параметров
     *
     * @param string $path
     * @return array
     */
    public function getConfigWithPath($path)
    {
        $name = basename($path);

        list($basename, $ext) = explode('.', $name);
        $ext = !empty($this->extension) ? $this->extension : $ext;

        return ArrayHelper::merge([
            '{name}' => $name,
            '{basename}' => $basename,
            '{ext}' => $ext
        ], $this->config());
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
}