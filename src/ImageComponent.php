<?php
/**
 * Файл класса ImageComponent
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage;

use yii\base\Component;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

/**
 * Class ImageComponent
 * @package chulakov\filestorage
 */
class ImageComponent extends Component
{
    /**
     * Позиционирование от левого верхнего края
     */
    const POSITION_TOP_LEFT = 'top-left';
    /**
     * Позиционирование от верха
     */
    const POSITION_TOP = 'top';
    /**
     * Позиционирование от верхнего правого края
     */
    const POSITION_TOP_RIGHT = 'top-right';
    /**
     * Позиционирование от левого края
     */
    const POSITION_LEFT = 'left';
    /**
     * Позиционирование от ценрта (по-умолчанию)
     */
    const POSITION_CENTER = 'center';
    /**
     * Позиционирование от правого края
     */
    const POSITION_RIGHT = 'right';
    /**
     * Позиционирование от нижнего левого края
     */
    const POSITION_BOTTOM_LEFT = 'bottom-left';
    /**
     * Позиционирование от нижнего края
     */
    const POSITION_BOTTOM = 'bottom';
    /**
     * Позиционирование от нижнего правого края
     */
    const POSITION_BOTTOM_RIGHT = 'bottom-right';
    /**
     * Путь к файлу
     *
     * @var string
     */
    protected $filePath;
    /**
     * @var Image
     */
    protected $image;

    /**
     * Установить изображение в компонент
     *
     * @param string $path
     */
    public function make($path)
    {
        $this->filePath = $path;
        $this->image = (new ImageManager())->make($path);
    }

    /**
     * Получить текущее изображение
     *
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Проверка на использование
     *
     * @return bool
     */
    public function hasImage()
    {
        return !is_null($this->image);
    }

    /**
     * Нанесение водяной метки на изображение
     *
     * @param string $watermarkPath
     * @param string $position
     */
    public function watermark($watermarkPath, $position = self::POSITION_CENTER)
    {
        if (!empty($watermarkPath)) {
            $this->image->insert($watermarkPath, $position);
        }
    }

    /**
     * Изменить размер изображения
     *
     * @param integer $width
     * @param integer $height
     */
    public function resize($width, $height)
    {
        $currentWidth = $this->getWidth();
        $currentHeight = $this->getHeight();

        if (!empty($width) && !empty($height)) {
            if ($this->checkSizeForResize($width, $height)) {
                $this->image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } elseif (!empty($width) && $currentWidth < $width) {
                $this->image->widen($currentWidth);
            } elseif (!empty($height) && $currentHeight < $height) {
                $this->image->heighten($currentHeight);
            }
        }
    }

    /**
     * Ресет компонента
     */
    public function reset()
    {
        $this->image = null;
        $this->filePath = null;
    }

    /**
     * Получить ширину
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->image->getWidth();
    }

    /**
     * Получить высоту
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->image->getHeight();
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
        return ($this->getWidth() > $width) && ($this->getHeight() > $height);
    }

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
    public function convert($encode)
    {
        $this->image->encode($encode);
    }
}