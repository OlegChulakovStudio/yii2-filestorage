<?php
/**
 * Файл класса RemoveModelAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use yii\base\Action;
use chulakov\filestorage\models\File;
use chulakov\filestorage\models\BaseFile;

/**
 * Class RemoveModelAction
 * @package backend\controllers\actions
 */
class RemoveModelAction extends Action
{
    /**
     * Удаление файла
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function run()
    {
        /** @var BaseFile $model */
        $model = File::findOne(['id' => 1]);
        /**
         * При удалении модели срабатывает событие afterDelete,
         * после чего удаляется все сгенерированные файлы
         * связанные с данным файлом.
         */
        if ($model) {
            $model->delete();
        }
    }
}