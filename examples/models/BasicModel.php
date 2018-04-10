<?php
/**
 * Файл класса BasicModel
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use chulakov\filestorage\models\Image;

/**
 * Class BasicModel
 *
 * @property integer $id
 * @property Image $image
 */
class BasicModel extends \yii\db\ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return Image::find()->andWhere(['id' => $this->id]);
    }
}