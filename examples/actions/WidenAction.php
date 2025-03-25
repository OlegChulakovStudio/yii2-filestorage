<?php
/**
 * Файл класса WidenAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use chulakov\filestorage\models\Image;
use yii\base\Action;

/**
 * Class WidenAction
 * @package backend\controllers\actions
 */
class WidenAction extends Action
{
    /**
     * Генерация изображения с действием widen
     */
    public function run(): string
    {
        $images = Image::findAll(['group_code' => 'photos']);

        $links = [];

        foreach ($images as $image) {
            $links[] = $image->widen(50, 90);
        }

        return json_encode($links);
    }
}
