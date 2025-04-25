<?php
/**
 * Файл класса ImageComponent
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage;

use chulakov\filestorage\image\ImageContainer;
use chulakov\filestorage\params\ImageParams;
use chulakov\filestorage\storage\StorageInterface;
use Intervention\Image\ImageManager;
use yii\base\Component;

/**
 * Class ImageComponent
 * @package chulakov\filestorage
 */
class ImageComponent extends Component
{
    /**
     * Драйвер обработки изображений - GD
     */
    public const DRIVER_GD = 'gd';
    /**
     * Драйвер обработки изображений - Imagick
     */
    public const DRIVER_IMAGICK = 'imagick';

    /**
     * Конфигурация драйвера
     */
    public string|array $driver = self::DRIVER_GD;
    protected ?ImageManager $manager = null;

    public function __construct(
        private readonly StorageInterface $storage,
        $config = [],
    ) {
        parent::__construct($config);
    }

    /**
     * Установить изображение в компонент
     */
    public function make(string $file): ImageContainer
    {
        $image = $this->getManager()->make($file);
        return new ImageContainer($image, $this->storage);
    }

    /**
     * Создание изображения
     */
    public function createImage(string $path, ImageParams $params): ImageContainer
    {
        $image = $this->make($path);

        if (isset($params->watermarkPath)) {
            $image->watermark($params->watermarkPath, $params->watermarkPosition);
        }
        if (isset($params->extension)) {
            $image->convert($params->extension);
        }
        $image->resize($params->width, $params->height);
        return $image;
    }

    /**
     * Инициализация и конфигурация менеджера изображений
     */
    protected function getManager(): ImageManager
    {
        return $this->manager ??= $this->makeManager();
    }

    protected function makeManager(): ImageManager
    {
        return new ImageManager([
            'driver' => $this->driver,
        ]);
    }
}
