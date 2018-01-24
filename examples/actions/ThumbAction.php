<?php
/**
 * Файл класса ThumbAction.php
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use yii\base\Action;
use chulakov\filestorage\models\Image;

class ThumbAction extends Action
{
    /**
     * Генерация thumbnail
     */
    public function run()
    {
        /** @var Image[] $images */
        $images = Image::findAll(['group_code' => 'photos']);

        $links = [];

        foreach ($images as $image) {
            $links[] = $image->thumb(50, 50, 70);
        }

        return json_encode($links);
    }
}