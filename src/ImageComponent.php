<?php
/**
 * Файл класса ImageComponent
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage;

use yii\base\Component;
use Intervention\Image\ImageManager;
use chulakov\filestorage\params\ImageParams;
use chulakov\filestorage\image\ImageContainer;

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
     * Драйвер обработки изображений - GD
     */
    const DRIVER_GD = 'gd';
    /**
     * Драйвер обработки изображений - Imagick
     */
    const DRIVER_IMAGICK = 'imagick';

    /**
     * @var array Конфигурация драйвера
     */
    public $driver = self::DRIVER_GD;

    /**
     * @var ImageManager
     */
    protected $manager;

    /**
     * Установить изображение в компонент
     *
     * @param string $file
     * @return ImageContainer
     */
    public function make($file)
    {
        $image = $this->getManager()->make($file);
        return new ImageContainer($image);
    }

    /**
     * Создание изображения
     *
     * @param string $path
     * @param ImageParams $params
     * @return ImageContainer
     */
    public function createImage($path, ImageParams $params)
    {
        if ($image = $this->make($path)) {
            if (!empty($params->watermarkPath)) {
                $image->watermark($params->watermarkPath, $params->watermarkPosition);
            }
            if (!empty($params->extension)) {
                $image->convert($params->extension);
            }
            $image->resize($params->width, $params->height);
            return $image;
        }
        return null;
    }

    /**
     * Инициализация и конфигурация менеджера изображений
     *
     * @return ImageManager
     */
    protected function getManager()
    {
        if (is_null($this->manager)) {
            $config = [
                'driver' => $this->driver,
            ];
            $this->manager = new ImageManager($config);
        }
        return $this->manager;
    }
}