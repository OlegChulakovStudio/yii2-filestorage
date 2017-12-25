<?php
/**
 * Файл класса RemoteImageSaveManager
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\savers;

/**
 * Class RemoteImageSaveManager
 * @package chulakov\filestorage\savers
 */
class RemoteImageSaveManager extends RemoteFileSaveManager
{
    use ImageTrait;

    /**
     * Сохранение файла на физический диск
     *
     */
    protected function saveFile()
    {
        $image = $this->imageManager->getImage();
        $image->save($this->savedPath, $this->quality);
    }

    /**
     * Сохранение файла
     *
     * @param string $path
     * @param string $content
     * @param bool $deleteFile
     * @return mixed
     */
    public function save($path, $content, $deleteFile = false)
    {
        $this->savedPath = $path;
        $this->content = $content;

        $this->loadImage($this->content); // установка изображения
        $this->transformation(); // обработка изображения
        $this->saveFile(); // сохранение результирующего файла

        return $this->saved = true;
    }

    /**
     * Получить расширение файла
     * @return mixed
     */
    public function getExtension()
    {
        if ($this->isSaved()) { // если сохранено
            $items = explode('.', basename($this->savedPath));
            return array_pop($items);
        }
        if (!empty($this->encode)) {  // если указано перекодирование
            return $this->encode;
        }
        return false;
    }

    /**
     * Получение MIME типа файла
     *
     * @return string
     */
    public function getType()
    {
        return finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $this->content) ?: 'text/plain';
    }

    /**
     * Получение размера файла
     *
     * @return integer
     */
    public function getSize()
    {
        if ($this->isSaved()) { //  если файл сохранени
            return filesize($this->savedPath);
        }
        if ($this->imageComponent && $this->imageComponent->hasImage()) { // если компонент уже работает с данным объектом
            return $this->imageComponent->getImage()->filesize();
        }
        if ($this->content && !empty($this->content)) { // если есть содержимое изображения
            return mb_strlen($this->content);
        }
        return 0;
    }
}