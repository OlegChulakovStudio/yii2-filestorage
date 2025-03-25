<?php
/**
 * Файл класса Controller
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers;

use backend\controllers\actions\ContainAction;
use backend\controllers\actions\CoverAction;
use backend\controllers\actions\HeightenAction;
use backend\controllers\actions\RemoveImageAction;
use backend\controllers\actions\RemoveModelAction;
use backend\controllers\actions\RemoveThumbsAction;
use backend\controllers\actions\ThumbAction;
use backend\controllers\actions\UploadAction;
use backend\controllers\actions\WidenAction;
use yii\web\Controller as BaseController;

/**
 * Class SiteController
 * @package backend\controllers
 */
class Controller extends BaseController
{
    /**
     * @inheritdoc
     */
    public function actions(): array
    {
        return [
            'uploader' => UploadAction::class,
            'thumb' => ThumbAction::class,
            'cover' => CoverAction::class,
            'contain' => ContainAction::class,
            'widen' => WidenAction::class,
            'heighten' => HeightenAction::class,
            'remove-model' => RemoveModelAction::class,
            'remove-thumb' => RemoveThumbsAction::class,
            'remove-image' => RemoveImageAction::class,
        ];
    }
}
