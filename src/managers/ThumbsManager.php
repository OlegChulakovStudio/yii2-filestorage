<?php
/**
 * Файл класса ThumbsManager
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\managers;

use yii\di\Instance;
use yii\base\BaseObject;
use yii\helpers\FileHelper;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\params\ThumbParams;
use chulakov\filestorage\uploaders\UploadInterface;
use chulakov\filestorage\observer\Event;
use chulakov\filestorage\observer\ListenerInterface;
use chulakov\filestorage\observer\ObserverInterface;

/**
 * Class ThumbsManager
 * @package chulakov\filestorage\managers
 */
class ThumbsManager extends BaseObject implements ListenerInterface
{
    /**
     * @var ThumbParams
     */
    public $thumbParams;
    /**
     * Название компонента для работы с изображениями
     *
     * @var string
     */
    public $imageClass;
    /**
     * @var ImageComponent
     */
    public $imageComponent;
    /**
     * @var UploadInterface
     */
    public $uploader;

    /**
     * Событие на сохранение thumbnail
     *
     * @param Event $event
     * @throws \Exception
     */
    public function generateThumb(Event $event)
    {
        if (!$this->validate($event->sender)) {
            return;
        }
        // проверка на изображение
        if (!$this->isImage()) {
            return;
        }

        if ($this->thumbParams) {
            $this->getImageManager()->createImage($this->uploader->getFile(), $this->thumbParams);
            $this->saveThumb($event->savedPath, $this->thumbParams->quality);
        }
    }

    /**
     * Присоединение к Observer
     *
     * @param ObserverInterface $observer
     * @return mixed
     */
    public function attach(ObserverInterface $observer)
    {
        $observer->on(Event::SAVE_EVENT, [$this, 'generateThumb']);
    }

    public function isImage()
    {
        return strpos($this->uploader->getType(), 'image') !== false;
    }

    /**
     * Валидация файла для обработки
     *
     * @param object $uploader
     * @return bool
     * @throws \Exception
     */
    protected function validate($uploader)
    {
        // Проверка корректного типа отправителя
        if (!($uploader instanceof UploadInterface)) {
            return false;
        }
        // Проверка файла по типу изменяемого
        $this->uploader = $uploader;
        if (!$this->isImage()) {
            return false;
        }
        return true;
    }

    /**
     * Сохранение Thumbnail
     *
     * @param $path
     * @param $quality
     * @throws \Exception
     */
    protected function saveThumb($path, $quality)
    {
        $savedPath = $this->thumbParams->getSavePath($path);
        $savedDir = dirname($savedPath);
        if (!is_dir($savedDir)) {
            FileHelper::createDirectory($savedDir);
        }
        $this->getImageManager()->save($savedPath, $quality);
    }

    /**
     * Геттер для работы с imageComponent
     * Получить менеджер работы с изображениями
     *
     * @return ImageComponent
     * @throws \Exception
     */
    protected function getImageManager()
    {
        if (empty($this->imageComponent)) {
            $this->imageComponent = $this->imageClass;
            if (is_array($this->imageComponent) && empty($this->imageComponent['class'])) {
                $this->imageComponent['class'] = $this->imageComponent;
            }
            $this->imageComponent = Instance::ensure($this->imageComponent);
        }
        return $this->imageComponent;
    }
}