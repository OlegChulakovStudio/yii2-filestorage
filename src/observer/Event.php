<?php
/**
 * Файл класса Event
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

/**
 * Class Event
 * @package chulakov\filestorage\observer
 */
class Event
{
    /**
     * Событие сохранения
     */
    const SAVE_EVENT = 'eventUploadSave';
    /**
     * Событие удаления
     */
    const DELETE_EVENT = 'eventUploadDelete';

    /**
     * Отправитель
     *
     * @var object
     */
    public $sender;
    /**
     * Нужно ли сохранить
     *
     * @var bool
     */
    public $needSave = true;
    /**
     * Нужно ли удалить
     *
     * @var bool
     */
    public $needDelete = false;
    /**
     * Путь
     *
     * @var string
     */
    public $savedPath;
    /**
     * @var \Exception
     */
    public $exception;

    /**
     * Конструктор события
     *
     * @param object $sender
     */
    public function __construct($sender)
    {
        $this->sender = $sender;
    }
}