<?php
/**
 * Файл класса ImageMakeParams
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

use chulakov\filestorage\ImageComponent;

/**
 * Class ImageMakeParams
 * @package chulakov\filestorage\params
 */
class ImageMakeParams
{
    /**
     * Ширина
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
     * Путь сохраняемого файла
     *
     * @var string
     */
    public $savePath;
    /**
     * Качество изображения
     *
     * @var integer
     */
    public $quality;
    /**
     * Должен ли результирующий файл быть
     * не больше чем он был изначально
     *
     * @var bool
     */
    public $upsize;
    /**
     * Позиция
     *
     * @var string
     */
    public $position;

    /**
     * Конструктор класса ImageMakeParams
     *
     * @param string $savePath
     * @param integer $quality
     * @param bool $upsize
     */
    public function __construct($savePath, $quality = 80, $upsize = true)
    {
        $this->savePath = $savePath;
        $this->quality = $quality;
        $this->upsize = $upsize;
    }
}