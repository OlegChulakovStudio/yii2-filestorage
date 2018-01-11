<?php
/**
 * Файл класса Image
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models;

use yii\helpers\ArrayHelper;
use chulakov\filestorage\params\ThumbParams;
use chulakov\filestorage\params\ImageMakeParams;
use chulakov\filestorage\behaviors\ImageBehavior;

/**
 * Class Image
 * @package chulakov\filestorage\models
 *
 * @method string thumb(ThumbParams $thumbParams)
 * @method bool removeAllThumbs()
 *
 * @method bool contain(ImageMakeParams $params)   Вписывание изображения в область путем пропорционального масштабирования без обрезки
 * @method bool cover(ImageMakeParams $params)     Заполнение обаласти частью изображения с обрезкой исходного, отталкиваясь от точки позиционировани
 * @method bool widen(ImageMakeParams $params)     Масштабирование по ширине без обрезки краев
 * @method bool heighten(ImageMakeParams $params)  Масштабирование по высоте без обрезки краев
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
