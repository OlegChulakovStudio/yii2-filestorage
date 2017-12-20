<?php
/**
 * Файл класса ThumbParams
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

class ThumbParams
{
    /**
     *  Ширина
     *
     * @var integer
     */
    public $width;
    /**
     * Высота
     *
     * @var integer
     */
    public $height;
    /**
     * Расширение
     *
     * @var string
     */
    public $extension;
    /**
     * Качество
     *
     * @var int
     */
    public $quality = 100;
    /**
     * Путь к файлу с watermark
     *
     * @var string
     */
    public $watermarkPath;
    /**
     * Позиция watermark
     *
     * @var integer
     */
    public $watermarkPosition;
    /**
     * Куда сохранить изображение
     *
     * @var string
     */
    public $savedPath;

    /**
     * ThumbParams constructor.
     * @param integer $width
     * @param integer $height
     */
    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }
}