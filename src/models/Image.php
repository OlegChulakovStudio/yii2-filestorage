<?php
/**
 * Файл класса Image
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models;

use chulakov\filestorage\behaviors\ThumbBehavior;
use chulakov\filestorage\params\ThumbParams;
use sem\helpers\ArrayHelper;

/**
 * Class Image
 * @package chulakov\filestorage\models
 *
 * @method string thumb(ThumbParams $thumbParams)
 */
class Image extends BaseFile
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(),
            [
                [
                    'class' => ThumbBehavior::className()
                ]
            ]);
    }
}
