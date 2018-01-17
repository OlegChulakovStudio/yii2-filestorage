<?php
/**
 * Файл класса AbstractImageManager
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\managers;

use yii\di\Instance;
use yii\base\BaseObject;
use yii\helpers\FileHelper;
use chulakov\filestorage\FileStorage;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\observer\Event;
use chulakov\filestorage\params\ImageParams;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\image\ImageContainer;
use chulakov\filestorage\uploaders\UploadInterface;
use chulakov\filestorage\observer\ListenerInterface;
use chulakov\filestorage\observer\ObserverInterface;

/**
 * Class AbstractImageManager
 * @package chulakov\filestorage\managers
 */
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
    public $watermarkPosition = Position::POSITION_CENTER;
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
     * Компонент работы с файлами
     *
     * @var FileStorage
     */
    public $storageComponent = 'chulakov\filestorage\FileStorage';
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
     * @inheritdoc
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->storageComponent = Instance::ensure($this->storageComponent);
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
            if ($mime = $this->image->getMimeType()) {
                return $mime;
            }
            if ($this->encode && $mime = FileHelper::getMimeTypeByExtension('.' . $this->encode)) {
                return $mime;
            }
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
        if ($this->image && $this->image->isSaved()) {
            if ($ext = $this->image->getExtension()) {
                return $ext;
            }
        }
        if ($this->encode) {
            return $this->encode;
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
        if ($this->image && $this->image->isSaved()) {
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
     * @throws \Exception
     */
    public function getImageParams()
    {
        if (is_null($this->params)) {
            $ext = !empty($this->encode) ? $this->encode : $this->getExtension();
            $this->params = new $this->imageParamsClass($this->width, $this->height);
            $this->params->extension = $ext;
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
        return $this->image->save($savedPath, $this->quality);
    }

    /**
     * Обновление пути сохранения файла
     * @param string $savedPath
     * @return string
     * @throws \Exception
     */
    protected function updatePath($savedPath)
    {
        $pathPattern = $this->storageComponent->getSavePathFromParams($savedPath, $this->getImageParams());
        return $this->storageComponent->getAbsolutePath($pathPattern);
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