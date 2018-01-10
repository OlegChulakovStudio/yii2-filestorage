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
    const SAVE_EVENT = 1;
    /**
     * Событие удаления
     */
    const DELETE_EVENT = 2;
    /**
     * Содержимое изображения
     *
     * @var string
     */
    public $content;
    /**
     * Нужно ли сохранить
     *
     * @var bool
     */
    public $needSave;
    /**
     * Нужно ли удалить
     *
     * @var bool
     */
    public $needDelete;
    /**
     * Путь к оригинальному файлу
     *
     * @var string
     */
    public $filePath;
    /**
     * Путь
     *
     * @var string
     */
    public $savedPath;
    /**
     * Отправитель
     *
     * @var object
     */
    public $sender;
}