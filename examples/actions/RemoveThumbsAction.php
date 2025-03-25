<?php
/**
 * Файл класса RemoveThumbsAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use chulakov\filestorage\models\Image;
use yii\base\Action;

/**
 * Class RemoveThumbsAction
 * @package backend\controllers\actions
 */
class RemoveThumbsAction extends Action
{
    /**
     * Удаление thumbnail изображения
     */
    public function run(): void
    {
        /** @var Image $image */
        $image = Image::findOne(['id' => 1]);

        $image?->removeAllThumbs();
    }
}
