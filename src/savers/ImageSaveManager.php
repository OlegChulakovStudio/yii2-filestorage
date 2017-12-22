<?php
/**
 * Файл класса ImageSaveManager
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\savers;

/**
 * Class FileSaveManager
 * @package chulakov\filestorage\savers
 */
class ImageSaveManager extends FileSaveManager
{
    use ImageTrait;

    /**
     * Сохранение файла
     *
     * @param bool $deleteTempFile
     * @return bool
     */
    protected function saveFile($deleteTempFile = true)
    {
        $image = $this->imageManager->getImage();
        $image->save($this->savedPath, $this->quality);
        if ($deleteTempFile) {
            unlink($this->filePath);
        }
        return true;
    }

    /**
     * Сохранение файла
     *
     * @param string $savePath
     * @param string $filePath
     * @param bool $deleteTempFile
     * @return bool|mixed
     */
    public function save($savePath, $filePath, $deleteTempFile = true)
    {
        $this->savedPath = $savePath;
        $this->filePath = $filePath;

        $this->loadImage($this->filePath);
        $this->transformation();
        $this->saveFile($deleteTempFile);

        return $this->saved = true;
    }

    /**
     * Получить расширение файла
     * @return mixed
     */
    public function getExtension()
    {
        if (!empty($this->encode)) {
            return $this->encode;
        }
        return strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));
    }

    /**
     * Получение размера файла
     *
     * @return integer
     */
    public function getSize()
    {
        if ($this->imageManager->hasImage() && $this->isSaved()) {
            return $this->imageManager->getImage()->filesize();
        }
        return parent::getSize();
    }
}