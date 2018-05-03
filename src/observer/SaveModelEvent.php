<?php
/**
 * Файл класса SaveModelEvent
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

use chulakov\filestorage\models\BaseFile;

class SaveModelEvent extends \yii\base\Event
{
    /**
     * Событие обработки модели перед ее сохранением в базу данных
     */
    const BEFORE_MODEL_SAVE = 'beforeModelSave';
    /**
     * Событие обработки модели после ее сохранения в базу данных
     */
    const AFTER_MODEL_SAVE = 'afterModelSave';

    /**
     * @var BaseFile
     */
    public $model;
}
