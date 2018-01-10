<?php
/**
 * Файл класса ImageParams
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

/**
 * Class ImageParams
 * @package chulakov\filestorage\params
 */
class ImageParams
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
     * ThumbParams constructor.
     * @param integer $width
     * @param integer $height
     */
    public function __construct($width = 195, $height = 195)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Получить путь файла относительно параметров
     *
     * @param string $path
     * @return string
     */
    public function getSavePath($path)
    {
        return $path;
    }

    /**
     * Конфигурирование
     *
     * @param array $config
     */
    public function configure($config = [])
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }
}