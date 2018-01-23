<?php
/**
 * Файл класса ImageContainer
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\image;

use yii\helpers\FileHelper;
use Intervention\Image\Image;
use Intervention\Image\Constraint;
use chulakov\filestorage\params\ImageParams;

/**
 * Class ImageContainer
 * @package chulakov\filestorage\image
 */
class ImageContainer implements ImageInterface
{
    /**
     * @var Image
     */
    protected $image;
    /**
     * Сохранено ли изображение
     *
     * @var bool
     */
    protected $saved = false;

    /**
     * Конструктор контейнера обработки изображения
     *
     * @param Image $image
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * Проверка, сохранен ли файл
     *
     * @return bool
     */
    public function isSaved()
    {
        return $this->saved;
    }

    /**
     * @inheritdoc
     */
    public function getWidth()
    {
        return $this->image->getWidth();
    }

    /**
     * @inheritdoc
     */
    public function getHeight()
    {
        return $this->image->getHeight();
    }

    /**
     * @inheritdoc
     */
    public function getMimeType()
    {
        return $this->image->mime;
    }

    /**
     * @inheritdoc
     */
    public function getExtension()
    {
        return $this->image->extension;
    }

    /**
     * @inheritdoc
     */
    public function getFileSize()
    {
        return $this->image->filesize();
    }

    /**
     * @inheritdoc
     */
    public function watermark($watermarkPath, $position = Position::CENTER)
    {
        if (!empty($watermarkPath)) {
            $this->image->insert($watermarkPath, $position);
        }
    }

    /**
     * @inheritdoc
     */
    public function resize($width, $height)
    {
        $currentWidth = $this->getWidth();
        $currentHeight = $this->getHeight();

        if (!empty($width) && !empty($height)) {
            if ($this->checkSizeForResize($width, $height)) {
                $this->image->resize($width, $height, function (Constraint $constraint) {
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
     * @inheritdoc
     */
    public function convert($encode)
    {
        $this->image->encode($encode);
    }

    /**
     * @inheritdoc
     * @throws \yii\base\Exception
     */
    public function save($path, $quality)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir);
        }
        return $this->saved = (bool)$this->image->save($path, $quality);
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
     * Проверка размера изображения
     *
     * @param integer $width
     * @param integer $height
     * @return bool
     */
    protected function checkSizeForResize($width, $height)
    {
        return ($this->getWidth() > $width) && ($this->getHeight() > $height);
    }

    /**
     * Вписывание изображения в область путем пропорционального масштабирования без обрезки
     *
     * @param string $savePath
     * @param ImageParams $params
     * @return bool
     * @throws \yii\base\Exception
     */
    public function contain($savePath, ImageParams $params)
    {
        if (!$this->image) {
            return false;
        }
        $this->image->resize(
            $params->width,
            $params->height,
            function (Constraint $constraint) {
                $constraint->aspectRatio();
            }
        );
        return $this->save($savePath, $params->quality);
    }

    /**
     * Масштабирование по ширине без обрезки краев
     *
     * @param string $savePath
     * @param ImageParams $params
     * @return bool
     * @throws \yii\base\Exception
     */
    public function widen($savePath, ImageParams $params)
    {
        if (!$this->image) {
            return false;
        }
        $this->image->widen($params->width);
        return $this->save($savePath, $params->quality);
    }

    /**
     * Масштабирование по высоте без обрезки краев
     *
     * @param string $savePath
     * @param ImageParams $params
     * @return bool
     * @throws \yii\base\Exception
     */
    public function heighten($savePath, ImageParams $params)
    {
        if (!$this->image) {
            return false;
        }
        $this->image->heighten($params->height);
        return $this->save($savePath, $params->quality);
    }

    /**
     * Заполнение обаласти частью изображения с обрезкой исходного,
     * отталкиваясь от точки позиционировани
     *
     * @param string $savePath
     * @param ImageParams $params
     * @return bool
     * @throws \yii\base\Exception
     */
    public function cover($savePath, ImageParams $params)
    {
        if (!$this->image) {
            return false;
        }
        $this->image->fit($params->width, $params->height);
        return $this->save($savePath, $params->quality);
    }

    /**
     * Генерация thumbnail
     *
     * @param $savePath
     * @param ImageParams $params
     * @return bool
     * @throws \yii\base\Exception
     */
    public function thumb($savePath, ImageParams $params)
    {
        if ($this->image) {
            if (!empty($params->watermarkPath)) {
                $this->image->insert($params->watermarkPath, $params->watermarkPosition);
            }
            if (!empty($params->encode)) {
                $this->image->encode($params->encode);
            }
            $this->resize($params->width, $params->height);
            return $this->save($savePath, $params->quality);
        }
        return false;
    }
}