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
            $this->updateFileInfo($event->savedPath);
            if ($this->saveImage($this->updatePath($event->savedPath))) {
                $this->uploader->setSize($this->getSize());
                $this->uploader->setType($this->getType());
                $event->needSave = false;
            }
        }
    }

    /**
     * Обновить информацию о файле
     *
     * @param string $filePath
     * @throws \Exception
     */
    protected function updateFileInfo($filePath)
    {
        $updateName = $this->storageComponent->updatePath($filePath, $this->getImageParams(), false);

        list($name) = explode('.', $this->uploader->getName());
        list($sysName) = explode('.', $updateName);

        $this->uploader->setExtension($this->getExtension());
        $this->uploader->setName($name . '.' . $this->getExtension());
        $this->uploader->setSysName($sysName);
    }
}
