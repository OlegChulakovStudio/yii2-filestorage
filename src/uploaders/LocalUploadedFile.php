<?php
/**
 * Файл класса LocalUploadedFile
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

/**
 * Класс для работы с загрузкой локальных файлов
 *
 * @package chulakov\filestorage\uploaders
 */
class LocalUploadedFile extends UploadedFile
{
    /**
     * Конструктор класса для работы с загрузкой локальных файлов
     *
     * @param string $filePath
     * @param array $config
     */
    public function __construct($filePath, array $config = [])
    {
        $this->tempName = $filePath;
        parent::__construct($config);
    }

    /**
     * Инициализация базовых параметров файла
     */
    public function init()
    {
        parent::init();
        $this->error = UPLOAD_ERR_OK;
        $this->setName(basename($this->tempName));
        $this->setType(mime_content_type($this->tempName));
        $this->setSize(filesize($this->tempName));
    }

    /**
     * @inheritdoc
     */
    public function saveAs($file, $deleteFile = false)
    {
        if ($this->beforeSave($file, $deleteFile)) {
            return copy($this->getFile(), $file);
        }
        return false;
    }

    /**
     * Удаление файла
     *
     * @param string $filePath
     * @param \Exception|null $exception
     * @return bool
     */
    public function deleteFile($filePath, $exception = null)
    {
        return $this->beforeDelete($filePath, $exception);
    }
}
