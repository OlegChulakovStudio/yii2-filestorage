<?php
/**
 * Файл класса ImageParams
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

use chulakov\filestorage\image\Position;

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
    public $coverPosition = Position::POSITION_CENTER;
    /**
     * Категория файлов
     *
     * @var string
     */
    public $group = 'images';
    /**
     * Шаблон сохранения thumbnails файлов
     *
     * @var string
     */
    public $pathPattern = '{relay}/{group}/{basename}/{type}_{width}x{height}.{ext}';
    /**
     * Шаблон удаления файлов.
     * Испольует glob для поиска всех файлов.
     *
     * @var string
     */
    public $searchPattern = '{relay}/{group}/{basename}/*';

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
        $this->addOption('type', $this->group);
    }

    /**
     * Выдача скомпанованных параметров
     *
     * @return array
     */
    public function config()
    {
        return array_merge(parent::config(), [
            '{group}' => $this->group,
            '{width}' => $this->width,
            '{height}' => $this->height,
            '{ext}' => $this->extension,
        ]);
    }
}