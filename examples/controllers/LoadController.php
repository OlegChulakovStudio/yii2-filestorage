<?php
/**
 * Файл класса LoadController
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use yii\web\Controller;
use yii\web\BadRequestHttpException;

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
    public function actionIndex()
    {
        $form = new ImageForm();

        if (\Yii::$app->request->isPost) {
            $form->load(\Yii::$app->request->post(), ''); // Загрузка параметров
            if ($form->validate() && $form->upload()) { // Валидация и загрузка файлов
                return json_encode(['success' => true]); // Выдача сообщения о успешной загрузке
            }
        }

        throw new BadRequestHttpException("Ошибка при загрузке файла.");
    }
}