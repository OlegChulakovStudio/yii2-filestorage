<?php
/**
 * Файл класса RemoveImageAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use chulakov\filestorage\models\Image;
use yii\base\Action;

/**
 * Class RemoveImageAction
 * @package backend\controllers\actions
 */
class RemoveImageAction extends Action
{
    /**
     * Удаление дочерних изображений модели
     */
    public function run(): void
    {
        $image = Image::findOne(['id' => 1]);

        $image?->removeAllImages();
    }
}
