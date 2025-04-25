<?php
/**
 * Файл класса LoadController
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use yii\web\BadRequestHttpException;
use yii\web\Controller;

/**
 * Класс контроллер для загрузки файла
 */
class LoadController extends Controller
{
    /**
     * Загрузка изображения
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionIndex(): string
    {
        $form = new ImageForm();

        if (Yii::$app->request->isPost) {
            /** Загрузка параметров */
            $form->load(Yii::$app->request->post(), '');
            /** Валидация и загрузка файлов */
            if ($form->validate() && $form->upload()) {
                /** Выдача сообщения об успешной загрузке */
                return json_encode(['success' => true]);
            }
        }

        throw new BadRequestHttpException("Ошибка при загрузке файла.");
    }
}
