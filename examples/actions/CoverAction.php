<?php
/**
 * Файл класса CoverAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use yii\base\Action;
use chulakov\filestorage\models\Image;
use chulakov\filestorage\image\Position;

/**
 * Class CoverAction
 * @package backend\controllers\actions
 */
class CoverAction extends Action
{
    /**
     * Генерация изображения с действием cover
     */
    public function run()
    {
        /** @var Image[] $images */
        $images = Image::findAll(['group_code' => 'photos']);

        $links = [];

        foreach ($images as $image) {
            $links[] = $image->cover(50, 50, 70, Position::CENTER);
        }

        return json_encode($links);
    }
}