<?php
/**
 * Файл класса ThumbParams
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

/**
 * Class ThumbParams
 * @package chulakov\filestorage\params
 */
class ThumbParams extends ImageParams
{
    /**
     * Категория превью
     */
    public string $group = 'thumbs';

    /**
     * Конструктор параметров
     */
    public function __construct(int $width = 195, int $height = 144)
    {
        parent::__construct($width, $height);
    }
}
