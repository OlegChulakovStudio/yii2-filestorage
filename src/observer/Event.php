<?php
/**
 * Файл класса Event
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

use Exception;
use yii\base\Event as BaseEvent;

/**
 * Событие загрузки и обработки файлов
 *
 * @package chulakov\filestorage\observer
 */
class Event extends BaseEvent
{
    /**
     * Событие сохранения
     */
    public const SAVE_EVENT = 'eventUploadSave';
    /**
     * Событие удаления
     */
    public const DELETE_EVENT = 'eventUploadDelete';

    /**
     * Нужно ли сохранить
     */
    public bool $needSave = true;
    /**
     * Нужно ли удалить
     */
    public bool $needDelete = false;
    /**
     * Путь
     */
    public ?string $savedPath = null;
    /**
     * Причина вызова события
     */
    public ?Exception $exception = null;

    /**
     * Конструктор события
     */
    public function __construct(object $sender, array $config = [])
    {
        $this->sender = $sender;

        parent::__construct($config);
    }
}
