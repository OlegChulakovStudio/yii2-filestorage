<?php
/**
 * Файл класса ImageUploadedFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use Intervention\Image\ImageManager;

/**
 * Class ImageUploadedFile
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
 * @package chulakov\filestorage\uploaders
 */
class ImageUploadedFile extends UploadedFile
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
     * Кодировка картинки
     *
     * Возможные кодировки картинки
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
     * @var string
     */
    public $encode;
    /**
     * @var \Intervention\Image\Image
     */
    public $image;
    /**
     * Путь к картинке с водяной меткой
     *
     * @var string
     */
    public $watermarkPath;
    /**
     * Позиционирование
     *
     * @var string
     */
    public $watermarkPosition = WatermarkPosition::POSITION_CENTER;
    /**
     * Качество изображения в процентах
     *
     * @var integer
     */
    public $quality = 100;

    /**
     * @inheritdoc
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        $imageManager = new ImageManager();
        $this->image = $imageManager->make($this->tempName);

        $this->convert();
        $this->resize();
        $this->watermark();

        $this->save($file, $deleteTempFile);
    }

    /**
     * Нанесение водяной метки на изображение
     */
    protected function watermark()
    {
        if (!empty($this->watermarkPath)) {
            $this->image->insert($this->watermarkPath, $this->watermarkPosition);
        }
    }

    /**
     * Получить расширение файла
     *
     * @return string
     */
    public function getExtension()
    {
        if (!empty($this->encode)) {
            return $this->encode;
        }
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    /**
     * Сохранение файла
     *
     * @param $file
     * @param bool $deleteTempFile
     * @return bool
     */
    protected function save($file, $deleteTempFile = true)
    {
        if ($this->error == UPLOAD_ERR_OK) {
            $this->image->save($file, $this->quality);
            if ($deleteTempFile) {
                unlink($this->tempName);
            }
            return true;
        }
        return false;
    }

    /**
     * Изменить размер изображения
     */
    protected function resize()
    {
        $width = $this->image->getWidth();
        $height = $this->image->getHeight();

        if (!$this->isSizeEmpty()) {
            if ($this->checkSizeForResize($width, $height)) {
                $this->image->resize($this->width, $this->height, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } elseif (!empty($this->width) && ($this->width < $width)) {
                $this->image->widen($this->width);
            } elseif (!empty($this->height) && $this->height < $height) {
                $this->image->heighten($this->height);
            }
        }
    }

    /**
     * Проверка размера изображения
     *
     * @param $width
     * @param $height
     * @return bool
     */
    protected function checkSizeForResize($width, $height)
    {
        return ($this->width > $width) && ($this->height > $height);
    }

    /**
     * Проверить на наличие параметров размера изображения
     *
     * @return bool
     */
    protected function isSizeEmpty()
    {
        return !empty($this->width) && !empty($this->height);
    }

    /**
     * Изменение кодировки изображения
     */
    protected function convert()
    {
        if (!empty($this->encode)) {
            $this->image->encode($this->encode);
        }
    }
}