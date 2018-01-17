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
            $this->uploader->setExtension($this->getExtension());
            if ($this->saveImage($this->updatePath($event->savedPath))) {
                $this->uploader->setSize($this->getSize());
                $this->uploader->setType($this->getType());
                $event->needSave = false;
            }
        }
    }

    /**
     * Изменение расширения для файла
     *
     * @param string $savedPath
     * @return string
     * @throws \Exception
     */
    protected function updatePath($savedPath)
    {
        $root = dirname($savedPath);
        $name = strtok(basename($savedPath), '.');
        return implode(DIRECTORY_SEPARATOR, [
            $root, $name . '.' . $this->getExtension()
        ]);
    }
}
