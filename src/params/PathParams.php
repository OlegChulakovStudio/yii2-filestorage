<?php
/**
 * Файл класса PathParams.php
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;


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
    public $pathPattern = '{root}/{group}/{basename}.{ext}';
    /**
     * Шаблон удаления файлов.
     * Испольует glob для поиска всех файлов.
     *
     * @var string
     */
    public $deletePattern = '{root}/{group}/{basename}*';

    /**
     * Получение расширенного списка параметров для генерации пути файла
     *
     * @return array
     */
    public function config()
    {
        return [
            'group' => $this->group,
        ];
    }
}