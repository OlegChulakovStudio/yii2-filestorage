<?php
/**
 * Файл класса UpdateController
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use chulakov\filestorage\models\Image;
use chulakov\filestorage\uploaders\UploadInterface;

/**
 * Класс контроллер для обновления файла
 */
class UpdateController extends Controller
{
    /**
     * Обновление изображения
     *
     * @param integer $id
     * @return string
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdate($id)
    {
        /** @var BasicModel $model */
        $model = BasicModel::find()->andWhere(['id' => $id])->one();

        if (!$model) {
            throw new NotFoundHttpException('Модель не найдена!');
        }

        $form = new FileValidatorForm($model);

        if (\Yii::$app->request->isPost) {
            $form->load(\Yii::$app->request->post(), ''); // Загрузка параметров
            if ($form->validate() && $form->attachFile instanceof UploadInterface) { // Проверка на наличие загружаемого файла
                /** @var Image $image */
                if ($image = $model->image) {
                    if ($form->upload()) { // Загрузка файла
                        $image->delete(); // Если файл уже был загружен, то удалим его
                    }
                }
            }
            return json_encode(['success' => true]); // Выдача сообщения о успешной загрузке
        }

        throw new BadRequestHttpException("Ошибка при загрузке файла.");
    }
}