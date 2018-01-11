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
use chulakov\filestorage\behaviors\ImageBehavior;

/**
 * Class Image
 * @package chulakov\filestorage\models
 *
 * @method string thumb(ThumbParams $thumbParams)
 * @method bool removeAllThumbs()
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
