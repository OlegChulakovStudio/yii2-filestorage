<?php
/**
 * Файл класса SaveModelEvent
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

use chulakov\filestorage\models\BaseFile;
use yii\base\Event;

class SaveModelEvent extends Event
{
    /**
     * Событие обработки модели перед ее сохранением в базу данных
     */
    public const BEFORE_MODEL_SAVE = 'beforeModelSave';
    /**
     * Событие обработки модели после ее сохранения в базу данных
     */
    public const AFTER_MODEL_SAVE = 'afterModelSave';

    /**
     * Модель файла
     */
    public ?BaseFile $model = null;
}
