<?php
/**
 * Файл класса ThumbsManager
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\managers;

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
            $this->saveImage($this->updatePath($event->savedPath));
        }
    }

    /**
     * Обновление пути
     *
     * @param string $savedPath
     * @return string
     * @throws \Exception
     */
    protected function updatePath($savedPath)
    {
        $params = $this->getImageParams();
        $params->addOption('type', 'thumb');

        return $this->storageComponent->makePath($savedPath, $params);
    }
}
