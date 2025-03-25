<?php
/**
 * Файл класса HeightenAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use chulakov\filestorage\models\Image;
use yii\base\Action;

/**
 * Class HeightenAction
 * @package backend\controllers\actions
 */
class HeightenAction extends Action
{
    /**
     * Генерация изображения с действием heighten
     */
    public function run(): string
    {
        $images = Image::findAll(['group_code' => 'photos']);

        $links = [];

        foreach ($images as $image) {
            $links[] = $image->heighten(50, 90);
        }

        return json_encode($links);
    }
}
