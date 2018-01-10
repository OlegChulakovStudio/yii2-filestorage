<?php
/**
 * Файл интерфейса ImageComponentInterface
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage;

use Intervention\Image\Facades\Image;

/**
 * Interface ImageInterface
 * @package chulakov\filestorage
 */
interface ImageComponentInterface
{
    /**
     * Установить изображение в компонент
     *
     * @param string $file
     * @return bool
     */
    public function make($file);

    /**
     * Сохранение файла
     *
     * @param string $path
     * @param integer $quality
     * @return Image
     */
    public function save($path, $quality);

    /**
     * Нанесение водяной метки на изображение
     *
     * @param string $watermarkPath
     * @param string $position
     */
    public function watermark($watermarkPath, $position);

    /**
     * Изменить размер изображения
     *
     * @param integer $width
     * @param integer $height
     */
    public function resize($width, $height);

    /**
     * Ресет компонента
     */
    public function reset();

    /**
     * Изменение кодировки изображения
     *
     * Доступные разрешения для изображений
     *
     * jpg — return JPEG encoded image data
     * png — return Portable Network Graphics (PNG) encoded image data
     * gif — return Graphics Interchange Format (GIF) encoded image data
     * tif — return Tagged Image File Format (TIFF) encoded image data
     * bmp — return Bitmap (BMP) encoded image data
     * ico — return ICO encoded image data
     * psd — return Photoshop Document (PSD) encoded image data
     * webp — return WebP encoded image data
     * data-url — encode current image data in data URI scheme (RFC 2397)
     *
     * @param $encode
     */
    public function convert($encode);
}