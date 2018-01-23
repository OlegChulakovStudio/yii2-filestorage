<?php
/**
 * Файл класса UploadAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use yii\base\Action;
use backend\models\BasicFileForm;
use chulakov\filestorage\exceptions\NotUploadFileException;

/**
 * Class UploadAction
 * @package backend\controllers\actions
 */
class UploadAction extends Action
{
    /**
     * Базовая загрузка файла
     *
     * @throws \yii\base\InvalidParamException
     * @throws NotUploadFileException
     */
    public function run()
    {
        $form = new BasicFileForm();

        $request = \Yii::$app->request;

        if ($request->isPost) {
            $form->load(\Yii::$app->request->post(), '');
            if ($form->validate() && $form->upload()) {
                return json_encode(['success' => true]);
            }
        }

        throw new NotUploadFileException('Файл не был загружен.');
    }
}