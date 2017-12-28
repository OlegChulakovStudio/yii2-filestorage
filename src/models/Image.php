<?php
/**
 * Файл класса Image
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models;

use yii\helpers\ArrayHelper;
use chulakov\filestorage\behaviors\ThumbBehavior;
use chulakov\filestorage\params\ThumbParams;

/**
 * Class Image
 * @package chulakov\filestorage\models
 *
 * @method string thumb(ThumbParams $thumbParams)
 */
class Image extends BaseFile
{
    /**
     * @inheritdoc
     */
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
