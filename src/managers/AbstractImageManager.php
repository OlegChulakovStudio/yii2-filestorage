<?php
/**
 * Файл класса AbstractImageManager
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\managers;

use chulakov\filestorage\image\ImageContainer;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\observer\Event;
use chulakov\filestorage\observer\ListenerInterface;
use chulakov\filestorage\observer\ObserverInterface;
use chulakov\filestorage\params\ImageParams;
use chulakov\filestorage\uploaders\UploadInterface;
use yii\base\BaseObject;
use yii\di\Instance;

abstract class AbstractImageManager extends BaseObject implements ListenerInterface
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
     * Качество изображения в процентах
     *
     * @var integer
     */
    public $quality = 100;
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
     * Название компонента для работы с изображениями
     *
     * @var string
     */
    public $imageClass;
    /**
     * Компонент обработки изображений
     *
     * @var ImageComponent
     */
    public $imageComponent;
    /**
     * Класс параметрической модели информации об изменении изображения
     *
     * @var string
     */
    public $imageParamsClass = 'chulakov\filestorage\params\ImageParams';

    /**
     * @var UploadInterface
     */
    protected $uploader;
    /**
     * @var ImageContainer
     */
    protected $image;
    /**
     * @var ImageParams
     */
    protected $params;

    /**
     * Обработка изображения
     *
     * @param Event $event
     * @return mixed
     */
    abstract public function handle(Event $event);

    /**
     * Присоединение к Observer
     *
     * @param ObserverInterface $observer
     */
    public function attach(ObserverInterface $observer)
    {
        $observer->on(Event::SAVE_EVENT, [$this, 'handle']);
    }

    /**
     * Валидация файла для обработки
     *
     * @param object $uploader
     * @return bool
     * @throws \Exception
     */
    protected function validate($uploader)
    {
        // Проверка корректного типа отправителя
        if (!($uploader instanceof UploadInterface)) {
            return false;
        }
        // Проверка файла по типу изменяемого
        $this->uploader = $uploader;
        if (!$this->isImage()) {
            return false;
        }
        return $this->processing();
    }

    /**
     * Проверка файла на изображение
     *
     * @return bool
     * @throws \Exception
     */
    public function isImage()
    {
        return strpos($this->getType(), 'image') !== false;
    }

    /**
     * Получение MIME типа файла
     *
     * @return string
     * @throws \Exception
     */
    public function getType()
    {
        if ($this->image) {
            return $this->image->getMimeType();
        }
        return $this->uploader->getType();
    }

    /**
     * Получить расширение файла
     *
     * @return mixed
     * @throws \Exception
     */
    public function getExtension()
    {
        if ($this->image) {
            return $this->image->getExtension();
        }
        return $this->uploader->getExtension();
    }

    /**
     * Получение размера файла
     *
     * @return integer
     * @throws \Exception
     */
    public function getSize()
    {
        if ($this->image) {
            return $this->image->getFileSize();
        }
        return $this->uploader->getSize();
    }


    /**
     * Обработка файла
     *
     * @return bool
     * @throws \Exception
     */
    protected function processing()
    {
        $this->image = $this->getImageManager()
            ->createImage(
                $this->uploader->getFile(),
                $this->getImageParams()
            );
        return !empty($this->image);
    }

    /**
     * Получение параметров обработки изображения
     *
     * @return ImageParams
     */
    public function getImageParams()
    {
        if (is_null($this->params)) {
            $this->params = new $this->imageParamsClass($this->width, $this->height);
            $this->params->extension = $this->encode;
            $this->params->quality = $this->quality;
            $this->params->watermarkPath = $this->watermarkPath;
            $this->params->watermarkPosition = $this->watermarkPosition;
        }
        return $this->params;
    }

    /**
     * Сохранение изображения
     *
     * @param string $savedPath
     * @return boolean
     * @throws \Exception
     */
    protected function saveImage($savedPath)
    {
        return $this->image->save($this->updatePath($savedPath), $this->quality);
    }

    /**
     * Обновление пути сохранения файла
     *
     * @param $savedPath
     * @return string
     */
    protected function updatePath($savedPath)
    {
        return $this->getImageParams()->getSavePath($savedPath);
    }

    /**
     * Геттер для работы с imageComponent
     * Получить менеджер работы с изображениями
     *
     * @return ImageComponent
     * @throws \Exception
     */
    protected function getImageManager()
    {
        if (empty($this->imageComponent)) {
            $this->imageComponent = $this->imageClass;
            if (is_array($this->imageComponent) && empty($this->imageComponent['class'])) {
                $this->imageComponent['class'] = $this->imageComponent;
            }
            $this->imageComponent = Instance::ensure($this->imageComponent);
        }
        return $this->imageComponent;
    }
}