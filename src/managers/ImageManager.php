<?php
/**
 * Файл класса ImageManager
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\managers;

use chulakov\filestorage\observer\Event;

/**
 * Class SaveManager
 * @package chulakov\filestorage\managers
 */
class ImageManager extends AbstractImageManager
{
    /**
     * Событие на сохранение
     *
     * @param Event $event
     * @throws \Exception
     */
    public function handle(Event $event)
    {
        // Проверка корректного типа отправителя
        if ($this->validate($event->sender)) {
            $this->updateFileInfo();
            if ($this->saveImage($this->updatePath($event->savedPath))) {
                $this->uploader->setSize($this->getSize());
                $event->needSave = false;
            }
        }
    }

    /**
     * Обновить информацию о файле
     *
     * @throws \Exception
     */
    protected function updateFileInfo()
    {
        $item = explode('.', $this->uploader->getName());
        $this->uploader->setName(array_shift($item) . '.' . $this->getExtension());
        $this->uploader->setExtension($this->getExtension());
        $this->uploader->setType($this->getType());
    }
}
