<?php
/**
 * Файл класса ImageParams
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

use chulakov\filestorage\ImageComponent;

/**
 * Class ImageParams
 * @package chulakov\filestorage\params
 */
class ImageParams extends PathParams
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
     * Позицыя при cover
     *
     * @var string
     */
    public $coverPosition = ImageComponent::POSITION_CENTER;
    /**
     * Категория файлов
     *
     * @var string
     */
    public $group = 'images';

    /**
     * Конструктор параметров
     *
     * @param integer $width
     * @param integer $height
     */
    public function __construct($width, $height)
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
        $name = basename($path);
        $path = dirname($path);

        list($basename, $ext) = explode('.', $name);
        $ext = !empty($this->extension) ? $this->extension : $ext;

        return strtr($this->pathPattern, [
            '{root}' => $path,
            '{name}' => $name,
            '{basename}' => $basename,
            '{category}' => $this->category,
            '{width}' => $this->width,
            '{height}' => $this->height,
            '{ext}' => $ext,
        ]);
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