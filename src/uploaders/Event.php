<?php
/**
 * Файл класса Event
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

/**
 * Class Event
 * @package chulakov\filestorage\uploaders
 */
class Event extends \yii\base\Event
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
     * Event constructor.
     * @param string $savedPath
     * @param bool $deleteFile
     * @param array $config
     */
    public function __construct($savedPath, $deleteFile, array $config = [])
    {
        $this->savedPath = $savedPath;
        $this->needDelete = $deleteFile;
        parent::__construct($config);
    }
}