<?php
/**
 * Файл класса RemoveThumbsAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use yii\base\Action;
use chulakov\filestorage\models\Image;

/**
 * Class RemoveThumbsAction
 * @package backend\controllers\actions
 */
class RemoveThumbsAction extends Action
{
    /**
     * Удаление thumbnail изображения
     */
    public function run()
    {
        /** @var Image $image */
        $image = Image::findOne(['id' => 1]);

        if ($image) {
            $image->removeAllThumbs();
        }
    }
}