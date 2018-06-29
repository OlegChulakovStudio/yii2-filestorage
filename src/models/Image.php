<?php
/**
 * Файл класса Image
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models;

use yii\helpers\ArrayHelper;
use chulakov\filestorage\behaviors\ImageBehavior;

/**
 * Модель представления загруженного файла изображения
 *
 * @property integer $width
 * @property integer $height
 *
 * @method string thumb($w = 195, $h = 144, $q = 80, $p = null)
 *
 * @method string contain($w, $h, $q = 80)          Вписывание изображения в область путем пропорционального масштабирования без обрезки
 * @method string cover($w, $h, $q = 80, $p = null) Заполнение обаласти частью изображения с обрезкой исходного, отталкиваясь от точки позиционировани
 * @method string widen($w, $q = 80)                Масштабирование по ширине без обрезки краев
 * @method string heighten($h, $q = 80)             Масштабирование по высоте без обрезки краев
 *
 * @method bool removeAllThumbs()                   Удаление всех превью данной модели
 * @method bool removeAllImages()                   Удаление всех превью данной мод
 *
 * @package chulakov\filestorage\models
 */
class Image extends BaseFile
{
    /**
     * @var array Сохраненный размер изображения
     */
    protected $imageSize;

    /**
     * Инициализация корректной модели файла
     *
     * @param array $row
     * @return static
     */
    public static function instantiate($row)
    {
        return new static();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            ImageBehavior::class,
        ]);
    }

    /**
     * Получение информации о размерах от исходного изображения
     *
     * @return array
     */
    public function getSize()
    {
        if (empty($this->imageSize)) {
            list($width, $height) = getimagesize($this->getPath());
            $this->imageSize = [
                'width' => $width,
                'height' => $height,
            ];
        }
        return $this->imageSize;
    }

    /**
     * Получение информации о ширине изображения
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->getSize()['width'];
    }

    /**
     * Получение информации о высоте изображения
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->getSize()['height'];
    }

    /**
     * Корректировка размера изображения при изменении размеров
     *
     * @param int $width
     * @param int $height
     * @return array
     */
    public function resolveSize($width = 0, $height = 0)
    {
        $rw = $this->width  * ($height / $this->height);
        $rh = $this->height * ($width  / $this->width);
        return [
            $width  ?: $rw,
            $height ?: $rh,
        ];
    }
}
