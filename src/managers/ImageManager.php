<?php
/**
 * Файл класса ImageManager
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\managers;

use yii\di\Instance;
use yii\base\BaseObject;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\uploaders\Event;
use chulakov\filestorage\uploaders\ObserverInterface;
use chulakov\filestorage\uploaders\UploadInterface;

/**
 * Class SaveManager
 * @package chulakov\filestorage\managers
 */
class ImageManager extends BaseObject implements FileInterface, ListenerInterface
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
     * Название компонента для работы с изображениями
     *
     * @var string
     */
    public $imageClass;
    /**
     * @var ImageComponent
     */
    public $imageComponent;
    /**
     * @var UploadInterface
     */
    protected $uploader;

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
     * Событие на сохранение
     *
     * @param Event $event
     * @throws \Exception
     */
    public function handle(Event $event)
    {
        // Проверка корректного типа отправителя
        if (!$this->validate($event->sender)) {
            return;
        }
        // Обработка изображения
        $this->processing();
        $this->updateFileInfo();
        // Сохранение изображения
        $newPath = $this->updatePath($event->savedPath);
        if ($this->saveImage($newPath)) {
            $this->uploader->setSize($this->getSize());
            $event->needSave = false;
        }
    }

    /**
     * Обновить информацию о файле
     *
     * @throws \Exception
     */
    protected function updateFileInfo()
    {
        $item = explode('.', $this->uploader->getName());
        $this->uploader->setName(array_shift($item) . '.' . $this->getExtension());

        $this->uploader->setExtension($this->getExtension());
        $this->uploader->setType($this->getType());
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
        return true;
    }

    /**
     * Обработка файла
     *
     * @return bool
     * @throws \Exception
     */
    protected function processing()
    {
        $manager = $this->getImageManager();
        if ($manager->make($this->uploader->getFile())) {
            $manager->resize($this->width, $this->height);
            $manager->watermark($this->watermarkPath, $this->watermarkPosition);
            $manager->convert($this->encode);
            return true;
        }
        return false;
    }

    /**
     * Сохранение изображения
     *
     * @param string $savedPath
     * @return \Intervention\Image\Image
     * @throws \Exception
     */
    protected function saveImage($savedPath)
    {
        return $this->getImageManager()->save($savedPath, $this->quality);
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
     * Получить расширение файла
     *
     * @return mixed
     * @throws \Exception
     */
    public function getExtension()
    {
        if (!empty($this->encode)) {
            return $this->encode;
        }
        if ($ext = $this->getImageManager()->getExtension()) {
            return $ext;
        }
        return $this->uploader->getExtension();
    }

    /**
     * Получение MIME типа файла
     *
     * @return string
     * @throws \Exception
     */
    public function getType()
    {
        if ($this->getImageManager()->hasImage()) {
            return $this->getImageManager()->getMimeType();
        }
        return $this->uploader->getType();
    }

    /**
     * Получение размера файла
     *
     * @return integer
     * @throws \Exception
     */
    public function getSize()
    {
        if ($size = $this->getImageManager()->getFileSize()) {
            return $size;
        }
        return $this->uploader->getSize();
    }

    /**
     * Обновление пути для сохранения
     *
     * @param string $originalPath
     * @return string
     * @throws \Exception
     */
    protected function updatePath($originalPath)
    {
        $path = dirname($originalPath);
        $name = $this->uploader->getSysName();


        return implode(DIRECTORY_SEPARATOR, array_filter([$path, $name]));
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
