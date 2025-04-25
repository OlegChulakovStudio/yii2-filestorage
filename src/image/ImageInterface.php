<?php
/**
 * Файл класса ImageInterface
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\image;

interface ImageInterface
{
    /**
     * Получить ширину
     */
    public function getWidth(): int;

    /**
     * Получить высоту
     */
    public function getHeight(): int;

    /**
     * Получение информации о текущем типе файла
     */
    public function getMimeType(): string;

    /**
     * Получение расширения файла
     */
    public function getExtension(): string;

    /**
     * Получение размера изображения
     */
    public function getFileSize(): int|false;

    /**
     * Нанесение водяной метки на изображение
     */
    public function watermark(string $watermarkPath, string $position = Position::CENTER): void;

    /**
     * Изменить размер изображения
     */
    public function resize(int $width, int $height): void;

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
    public function convert($encode): void;

    /**
     * Сохранение файла
     */
    public function save(string $path, int $quality): bool;

    /**
     * Удаление файла
     */
    public function delete(string $path): void;
}
