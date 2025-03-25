<?php
/**
 * Файл класса ImageContainer
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\image;

use chulakov\filestorage\params\ImageParams;
use chulakov\filestorage\storage\StorageInterface;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use yii\base\Exception;

/**
 * Class ImageContainer
 * @package chulakov\filestorage\image
 */
class ImageContainer implements ImageInterface
{
    /**
     * Сохранено ли изображение
     */
    protected bool $saved = false;

    /**
     * Конструктор контейнера обработки изображения
     */
    public function __construct(
        protected Image $image,
        protected StorageInterface $storage,
    ) {}

    /**
     * Проверка, сохранен ли файл
     */
    public function isSaved(): bool
    {
        return $this->saved;
    }

    /**
     * @inheritdoc
     */
    public function getWidth(): int
    {
        return $this->image->getWidth();
    }

    /**
     * @inheritdoc
     */
    public function getHeight(): int
    {
        return $this->image->getHeight();
    }

    /**
     * @inheritdoc
     */
    public function getMimeType(): string
    {
        return $this->image->mime;
    }

    /**
     * @inheritdoc
     */
    public function getExtension(): string
    {
        return $this->image->extension;
    }

    /**
     * @inheritdoc
     */
    public function getFileSize(): int|false
    {
        return $this->image->filesize();
    }

    /**
     * @inheritdoc
     */
    public function watermark(string $watermarkPath, string $position = Position::CENTER): void
    {
        if (empty($watermarkPath) === false) {
            $this->image->insert($watermarkPath, $position);
        }
    }

    /**
     * @inheritdoc
     */
    public function resize(int $width, int $height): void
    {
        $currentWidth = $this->getWidth();
        $currentHeight = $this->getHeight();

        if (empty($width) === false && empty($height) === false) {
            if ($this->checkSizeForResize($width, $height)) {
                $this->image->resize($width, $height, static function (Constraint $constraint) {
                    $constraint->aspectRatio();
                });
            } elseif ($currentWidth < $width) {
                $this->image->widen($currentWidth);
            } elseif ($currentHeight < $height) {
                $this->image->heighten($currentHeight);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($encode): void
    {
        $this->image->encode($encode);
    }

    /**
     * @inheritdoc
     */
    public function save(string $path, int $quality): bool
    {
        return $this->saved = $this->storage->saveImage($this->image, $path, $quality);
    }

    /**
     * @inheritdoc
     */
    public function delete(string $path): void
    {
        $this->storage->removeFile($path);
    }

    /**
     * Получить текущее изображение
     */
    public function getImage(): Image
    {
        return $this->image;
    }

    /**
     * Проверка размера изображения
     */
    protected function checkSizeForResize(int $width, int $height): bool
    {
        return ($this->getWidth() > $width) && ($this->getHeight() > $height);
    }

    /**
     * Вписывание изображения в область путем пропорционального масштабирования без обрезки
     * @throws Exception
     */
    public function contain(string $savePath, ImageParams $params): bool
    {
        $this->image->resize(
            $params->width,
            $params->height,
            static function (Constraint $constraint) {
                $constraint->aspectRatio();
            },
        );
        return $this->save($savePath, $params->quality);
    }

    /**
     * Масштабирование по ширине без обрезки краев
     * @throws Exception
     */
    public function widen(string $savePath, ImageParams $params): bool
    {
        $this->image->widen($params->width);
        return $this->save($savePath, $params->quality);
    }

    /**
     * Масштабирование по высоте без обрезки краев
     * @throws Exception
     */
    public function heighten(string $savePath, ImageParams $params): bool
    {
        $this->image->heighten($params->height);
        return $this->save($savePath, $params->quality);
    }

    /**
     * Заполнение области частью изображения с обрезкой исходного,
     * отталкиваясь от точки позиционирования
     * @throws Exception
     */
    public function cover(string $savePath, ImageParams $params): bool
    {
        $this->image->fit($params->width, $params->height, null, $params->coverPosition);
        return $this->save($savePath, $params->quality);
    }

    /**
     * Генерация thumbnail
     * @throws Exception
     */
    public function thumb(string $savePath, ImageParams $params): bool
    {
        if (isset($params->watermarkPath)) {
            $this->image->insert($params->watermarkPath, $params->watermarkPosition);
        }
        if (isset($params->encode)) {
            $this->image->encode($params->encode);
        }
        $this->resize($params->width, $params->height);
        return $this->save($savePath, $params->quality);
    }
}
