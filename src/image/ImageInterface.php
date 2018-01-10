<?php
/**
 * Файл класса ImageInterface
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\image;

use chulakov\filestorage\ImageComponent;

interface ImageInterface
{
    /**
     * Получить ширину
     *
     * @return int
     */
    public function getWidth();

    /**
     * Получить высоту
     *
     * @return int
     */
    public function getHeight();

    /**
     * Получение информации о текущем типе файла
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Получение расширения файла
     *
     * @return string
     */
    public function getExtension();

    /**
     * Получение размера изображения
     *
     * @return mixed
     */
    public function getFileSize();

    /**
     * Нанесение водяной метки на изображение
     *
     * @param string $watermarkPath
     * @param string $position
     */
    public function watermark($watermarkPath, $position = ImageComponent::POSITION_CENTER);

    /**
     * Изменить размер изображения
     *
     * @param integer $width
     * @param integer $height
     */
    public function resize($width, $height);

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

    /**
     * Сохранение файла
     *
     * @param string $path
     * @param integer $quality
     * @return boolean
     */
    public function save($path, $quality);
}