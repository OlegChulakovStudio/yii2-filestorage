<?php
/**
 * Файл класса BasicModel
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use chulakov\filestorage\models\Image;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class BasicModel
 *
 * @property integer $id
 * @property Image $image
 */
class BasicModel extends ActiveRecord
{
    public function getImage(): ActiveQuery
    {
        return Image::find()->andWhere(['id' => $this->id]);
    }
}
