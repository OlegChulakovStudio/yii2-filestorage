<?php
/**
 * Файл класса File
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models;

/**
 * Псевдоним базовой модели файла
 *
 * @package chulakov\filestorage\models
 */
class File extends BaseFile
{
    /**
     * Инициализация корректной модели файла
     *
     * @param array $row
     */
    public static function instantiate($row): static
    {
        return new static();
    }
}
