<?php
/**
 * Файл класса ThumbAction.php
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use chulakov\filestorage\models\Image;
use yii\base\Action;

class ThumbAction extends Action
{
    /**
     * Генерация thumbnail
     */
    public function run(): string
    {
        $images = Image::findAll(['group_code' => 'photos']);

        $links = [];

        foreach ($images as $image) {
            $links[] = $image->thumb(50, 50, 70);
        }

        return json_encode($links);
    }
}
