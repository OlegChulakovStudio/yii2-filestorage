<?php
/**
 * Файл класса ImageTrait
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\savers;

use chulakov\filestorage\ImageComponent;
use yii\di\Instance;

/**
 * Trait ImageTrait
 * @package chulakov\filestorage\savers
 *
 * @property ImageComponent $imageManager
 */
trait ImageTrait
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
    public $watermarkPosition = ImageComponent::POSITION_CENTER;
    /**
     * Качество изображения в процентах
     *
     * @var integer
     */
    public $quality = 100;
    /**
     * @var ImageComponent
     */
    public $imageComponent;
    /**
     * @var string Название компонента для работы с изображениями
     */
    public $imageClass;

    /**
     * Геттер для работы с imageComponent
     * Получить менеджер работы с изображениями
     *
     * @return ImageComponent
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    protected function getImageManager()
    {
        if (!empty($this->imageComponent)) {
            return $this->imageComponent;
        }
        $this->imageComponent = $this->imageClass;
        if (is_array($this->imageComponent) && empty($this->imageComponent['class'])) {
            $this->imageComponent['class'] = $this->imageComponent;
        }
        $this->imageComponent = Instance::ensure($this->imageComponent);
        return $this->imageComponent;
    }

    /**
     * Сеттер для работы с imageComponent
     *
     * @param $value
     */
    protected function setImageManager($value)
    {
        $this->imageComponent = $value;
    }

    /**
     * Производимые действия над файлом
     */
    protected function transformation()
    {
        $this->imageManager->resize($this->width, $this->height);
        $this->imageManager->convert($this->encode);
        $this->imageManager->watermark($this->watermarkPath, $this->watermarkPosition);
    }

    /**
     * Подгрузка файла
     *
     * @param string $content Path or content file
     */
    protected function loadImage($content)
    {
        $this->imageManager->make($content);
    }
}