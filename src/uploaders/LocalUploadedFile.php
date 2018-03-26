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
     * @var string Путь к файлу
     */
    public $tempName;

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

        $this->getFileInfo();
    }

    /**
     * @inheritdoc
     */
    public function saveAs($file, $deleteFile = false)
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            return false;
        }
        return $deleteFile
            ? move_uploaded_file($this->tempName, $file)
            : copy($this->tempName, $file);
    }

    /**
     * Получение информации о файле
     */
    protected function getFileInfo()
    {
        $this->name = basename($this->tempName);
        $this->size = filesize($this->tempName);
        $this->type = mime_content_type($this->tempName);
        $this->error = UPLOAD_ERR_OK;
    }
}