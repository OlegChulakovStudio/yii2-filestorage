<?php
/**
 * Файл класса UpdateController
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use chulakov\filestorage\uploaders\UploadInterface;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Класс контроллер для обновления файла
 */
class UpdateController extends Controller
{
    /**
     * Обновление изображения
     *
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionUpdate(int $id): string
    {
        /** @var BasicModel $model */
        $model = BasicModel::find()->andWhere(['id' => $id])->one();

        if (!$model) {
            throw new NotFoundHttpException('Модель не найдена!');
        }

        $form = new FileValidatorForm($model);

        if (Yii::$app->request->isPost) {
            /** Загрузка параметров */
            $form->load(Yii::$app->request->post(), '');
            /** Проверка на наличие загружаемого файла */
            if ($form->validate() && $form->attachFile instanceof UploadInterface) {
                if ($image = $model->image) {
                    /** Загрузка файла */
                    if ($form->upload()) {
                        /** Если файл уже был загружен, то удалим его */
                        $image->delete();
                    }
                }
            }
            /** Выдача сообщения об успешной загрузке */
            return json_encode(['success' => true]);
        }

        throw new BadRequestHttpException("Ошибка при загрузке файла.");
    }
}
