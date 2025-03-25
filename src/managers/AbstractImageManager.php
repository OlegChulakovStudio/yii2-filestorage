<?php
/**
 * Файл класса AbstractImageManager
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\managers;

use chulakov\filestorage\FileStorage;
use chulakov\filestorage\image\ImageContainer;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\observer\Event;
use chulakov\filestorage\observer\ListenerInterface;
use chulakov\filestorage\observer\ObserverInterface;
use chulakov\filestorage\params\ImageParams;
use chulakov\filestorage\uploaders\UploadInterface;
use Exception;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\FileHelper;

/**
 * Class AbstractImageManager
 * @package chulakov\filestorage\managers
 */
abstract class AbstractImageManager extends BaseObject implements ListenerInterface
{
    /**
     * Ширина
     */
    public int $width;
    /**
     * Высота
     */
    public int $height;
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
     */
    public ?string $encode = null;
    /**
     * Качество изображения в процентах
     */
    public int $quality = 100;
    /**
     * Путь к картинке с водяной меткой
     */
    public ?string $watermarkPath = null;
    /**
     * Позиционирование
     */
    public string $watermarkPosition = Position::CENTER;
    /**
     * Компонент обработки изображений
     */
    public ImageComponent|string $imageComponent = 'imageComponent';
    /**
     * Класс параметрической модели информации об изменении изображения
     */
    public string $imageParamsClass = 'chulakov\filestorage\params\ImageParams';
    /**
     * Компонент работы с файлами
     */
    public FileStorage|string $fileStorage = 'fileStorage';
    protected UploadInterface $uploader;
    protected ?ImageContainer $image = null;
    protected ?ImageParams $params = null;

    /**
     * Обработка файла
     */
    abstract public function handle(Event $event): void;

    /**
     * Обработка события удаления
     */
    public function handleDelete(Event $event): void {}

    /**
     * Присоединение к Observer
     */
    public function attach(ObserverInterface $observer): void
    {
        $observer->on(Event::SAVE_EVENT, [$this, 'handle']);
        $observer->on(Event::DELETE_EVENT, [$this, 'handleDelete']);
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->fileStorage = Instance::ensure($this->fileStorage);
    }

    /**
     * Валидация файла для обработки
     *
     * @throws Exception
     */
    protected function validate(object $uploader): bool
    {
        // Проверка корректного типа отправителя
        if ($uploader instanceof UploadInterface === false) {
            return false;
        }
        // Проверка файла по типу изменяемого
        $this->uploader = $uploader;
        return $this->isImage();
    }

    /**
     * Проверка файла на изображение
     *
     * @throws Exception
     */
    public function isImage(): bool
    {
        return str_contains($this->getType(), 'image');
    }

    /**
     * Получение MIME типа файла
     *
     * @throws Exception
     */
    public function getType(): string
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
     * @throws Exception
     */
    public function getExtension(): string
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
     * @throws Exception
     */
    public function getSize(): int
    {
        if ($this->image && $this->image->isSaved()) {
            return $this->image->getFileSize();
        }
        return $this->uploader->getSize();
    }

    /**
     * Обработка файла
     * @throws Exception
     */
    protected function processing(): void
    {
        $this->image = $this
            ->getImageManager()
            ->createImage(
                $this->uploader->getFile(),
                $this->getImageParams(),
            );
    }

    /**
     * Получение параметров обработки изображения
     * @throws Exception
     */
    public function getImageParams(): ImageParams
    {
        return $this->params ??= $this->makeImageParams();
    }

    /**
     * Получение параметров обработки изображения
     * @throws Exception
     */
    public function makeImageParams(): ImageParams
    {
        $ext = $this->encode ?? $this->getExtension();
        $params = new $this->imageParamsClass($this->width, $this->height);
        $params->extension = $ext;
        $params->quality = $this->quality;
        $params->watermarkPath = $this->watermarkPath;
        $params->watermarkPosition = $this->watermarkPosition;
        return $params;
    }

    /**
     * Сохранение изображения
     * @throws Exception
     */
    protected function saveImage(string $savedPath): bool
    {
        return (bool) $this->image?->save($savedPath, $this->quality);
    }

    /**
     * Удаление изображения
     * @throws Exception
     */
    protected function deleteImage(string $savedPath): void
    {
        $this->image?->delete($savedPath);
    }

    /**
     * Обновление пути сохранения файла
     * @throws Exception
     */
    protected function updatePath(string $savedPath): string
    {
        return $this->fileStorage->getAbsolutePath(
            $this->fileStorage->makePath($savedPath, $this->getImageParams()),
        );
    }

    /**
     * Получить менеджер работы с изображениями
     * @throws Exception
     */
    protected function getImageManager(): ImageComponent
    {
        $this->imageComponent = Instance::ensure($this->imageComponent);
        return $this->imageComponent;
    }
}
