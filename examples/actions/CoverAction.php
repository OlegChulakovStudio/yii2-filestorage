<?php
/**
 * Файл класса CoverAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use chulakov\filestorage\image\Position;
use chulakov\filestorage\models\Image;
use yii\base\Action;

/**
 * Class CoverAction
 * @package backend\controllers\actions
 */
class CoverAction extends Action
{
    /**
     * Генерация изображения с действием cover
     */
    public function run(): string
    {
        $images = Image::findAll(['group_code' => 'photos']);

        $links = [];

        foreach ($images as $image) {
            $links[] = $image->cover(50, 50, 70, Position::CENTER);
        }

        return json_encode($links);
    }
}
