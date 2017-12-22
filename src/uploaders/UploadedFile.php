<?php
/**
 * Файл класса UploadedFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use chulakov\filestorage\savers\SaveInterface;

/**
 * Class UploadedFile
 * @package chulakov\filestorage\uploaders
 */
class UploadedFile extends \yii\web\UploadedFile implements UploadInterface
{
    /**
     * Менеджер сохранения
     *
     * @var SaveInterface
     */
    public $saveManager;

    /**
     * @inheritdoc
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        $this->saveManager->save($file, $this->tempName, $deleteTempFile);
    }

    /**
     * Проверка, выполнялось ли сохранение или нет
     *
     * @return bool
     */
    protected function isSaved()
    {
        return $this->saveManager && $this->saveManager->isSaved();
    }

    /**
     * Получить расширение файла
     *
     * @return string
     */
    public function getExtension()
    {
        $ext = $this->saveManager->getExtension();
        if (empty($ext)) {
            return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
        }
        return $ext;
    }

    /**
     * Получение MIME типа файла
     *
     * @return string
     */
    public function getType()
    {
        return $this->saveManager->getType() ?: $this->type;
    }

    /**
     * Получение размера файла
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->saveManager->getSize() ?: $this->size;
    }
}