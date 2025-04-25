<?php
/**
 * Файл класса ThumbsManager
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\managers;

use chulakov\filestorage\observer\Event;
use Exception;

/**
 * Class ThumbsManager
 * @package chulakov\filestorage\managers
 */
class ThumbsManager extends AbstractImageManager
{
    /**
     * Ширина
     *
     * @var integer
     */
    public int $width = 192;
    /**
     * Высота
     *
     * @var integer
     */
    public int $height = 144;

    /**
     * Класс параметров
     *
     * @var string
     */
    public string $imageParamsClass = 'chulakov\filestorage\params\ThumbParams';

    /**
     * Событие на сохранение thumbnail
     * @throws Exception
     */
    public function handle(Event $event): void
    {
        if ($this->validate($event->sender)) {
            $this->processing();
            $this->saveImage($this->updatePath($event->savedPath));
        }
    }

    /**
     * @throws Exception
     */
    public function handleDelete(Event $event): void
    {
        if ($this->validate($event->sender)) {
            $path = $this->updatePath($event->savedPath);
            $this->deleteImage($path);
        }
    }
}
