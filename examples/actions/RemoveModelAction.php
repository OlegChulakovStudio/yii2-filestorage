<?php
/**
 * Файл класса RemoveModelAction
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\controllers\actions;

use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\models\File;
use Exception;
use Throwable;
use yii\base\Action;
use yii\db\StaleObjectException;

/**
 * Class RemoveModelAction
 * @package backend\controllers\actions
 */
class RemoveModelAction extends Action
{
    /**
     * Удаление файла
     *
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function run(): void
    {
        /** @var BaseFile $model */
        $model = File::findOne(['id' => 1]);
        /**
         * При удалении модели срабатывает событие afterDelete,
         * после чего удаляется все сгенерированные файлы
         * связанные с данным файлом.
         */
        $model?->delete();
    }
}
