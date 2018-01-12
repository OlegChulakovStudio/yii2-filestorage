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
 * Class Image
 * @package chulakov\filestorage\models
 *
 * @method string thumb($w = 0, $h = 0, $q = 0, $p = null)
 * @method bool removeAllThumbs()
 *
 * @method bool contain($width, $height, $quality)          Вписывание изображения в область путем пропорционального масштабирования без обрезки
 * @method bool cover($width, $height, $quality, $position) Заполнение обаласти частью изображения с обрезкой исходного, отталкиваясь от точки позиционировани
 * @method bool widen($width, $quality)                     Масштабирование по ширине без обрезки краев
 * @method bool heighten($height, $quality)                 Масштабирование по высоте без обрезки краев
 */
class Image extends BaseFile
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            ImageBehavior::className(),
        ]);
    }
}
