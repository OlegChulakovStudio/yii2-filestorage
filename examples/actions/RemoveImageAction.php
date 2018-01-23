<?php
/**
 * Файл класса RemoveImageAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use yii\base\Action;
use chulakov\filestorage\models\Image;

/**
 * Class RemoveImageAction
 * @package backend\controllers\actions
 */
class RemoveImageAction extends Action
{
    /**
     * Удаление дочерних изображений модели
     */
    public function run()
    {
        $image = Image::findOne(['id' => 1]);

        if ($image) {
            $image->removeAllImages();
        }
    }
}