<?php
/**
 * Файл класса ContainAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use chulakov\filestorage\models\Image;
use yii\base\Action;

/**
 * Class ContainAction
 * @package backend\controllers\actions
 */
class ContainAction extends Action
{
    /**
     * Генерация изображения с действием contain
     */
    public function run(): string
    {
        $images = Image::findAll(['group_code' => 'photos']);

        $links = [];

        foreach ($images as $image) {
            $links[] = $image->contain(50, 50, 70);
        }

        return json_encode($links);
    }
}
