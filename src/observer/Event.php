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

    /**
     * Event constructor.
     * @param string $savedPath
     * @param bool $deleteFile
     * @param array $config
     */
    public function __construct($savedPath, $deleteFile, array $config = [])
    {
        $this->savedPath = $savedPath;
        $this->needDelete = $deleteFile;
    }
}