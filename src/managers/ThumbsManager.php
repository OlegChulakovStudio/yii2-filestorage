<?php
/**
 * Файл класса ThumbsManager
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\managers;

use yii\helpers\FileHelper;
use chulakov\filestorage\observer\Event;

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
    public $width = 192;
    /**
     * Высота
     *
     * @var integer
     */
    public $height = 144;

    /**
     * Класс параметров
     *
     * @var string
     */
    public $imageParamsClass = 'chulakov\filestorage\params\ThumbParams';

    /**
     * Событие на сохранение thumbnail
     *
     * @param Event $event
     * @throws \Exception
     */
    public function handle(Event $event)
    {
        if ($this->validate($event->sender)) {
            $this->processing();
            $this->saveImage($this->updatePath($event->savedPath));
        }
    }

    /**
     * @param Event $event
     * @throws \Exception
     */
    public function handleDelete(Event $event)
    {
        if ($this->validate($event->sender)) {
            $path = $this->updatePath($event->savedPath);
            if (is_file($path)) {
                $path = dirname($path);
            }
            if(is_dir($path)) {
                FileHelper::removeDirectory($path);
            }
        }
    }
}
