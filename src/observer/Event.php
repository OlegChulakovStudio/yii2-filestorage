<?php
/**
 * Файл класса Event
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

/**
 * Событие загрузки и обработки файлов
 *
 * @package chulakov\filestorage\observer
 */
class Event extends \yii\base\Event
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
     * Причина вызова события
     *
     * @var \Exception
     */
    public $exception;

    /**
     * Конструктор события
     *
     * @param object $sender
     * @param array $config
     */
    public function __construct($sender, $config = [])
    {
        $this->sender = $sender;
        parent::__construct($config);
    }
}