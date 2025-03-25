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
     * Ширина
     */
    public int $width;
    /**
     * Высота
     */
    public int $height;
    /**
     * Расширение
     */
    public ?string $extension = null;
    /**
     * Желаемое расширение файла
     */
    public ?string $encode = null;
    /**
     * Качество
     */
    public int $quality = 100;
    /**
     * Путь к файлу с watermark
     */
    public ?string $watermarkPath = null;
    /**
     * Позиция watermark
     */
    public ?string $watermarkPosition = null;
    /**
     * Позиция при cover
     */
    public string $coverPosition = Position::CENTER;
    /**
     * Категория файлов
     */
    public string $group = 'images';
    /**
     * Шаблон сохранения thumbnails файлов
     */
    public string $pathPattern = '{relay}/{group}/{basename}/{type}_{width}x{height}.{ext}';
    /**
     * Шаблон удаления файлов.
     * Использует glob для поиска всех файлов.
     */
    public string $searchPattern = '{relay}/{group}/{basename}/*';

    /**
     * Конструктор параметров
     */
    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
        $this->addOption('type', $this->group);
    }

    /**
     * Выдача скомпонованных параметров
     */
    public function config(): array
    {
        return array_merge(parent::config(), [
            '{group}' => $this->group,
            '{width}' => $this->width,
            '{height}' => $this->height,
            '{ext}' => $this->extension,
        ]);
    }
}
