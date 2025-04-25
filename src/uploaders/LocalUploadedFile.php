<?php
/**
 * Файл класса LocalUploadedFile
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use Throwable;

/**
 * Класс для работы с загрузкой локальных файлов
 *
 * @package chulakov\filestorage\uploaders
 */
final class LocalUploadedFile extends UploadedFile
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
    public function init(): void
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
    public function saveAs($file, $deleteTempFile = false): bool
    {
        if ($this->beforeSave($file, $deleteTempFile)) {
            return copy($this->getFile(), $file);
        }
        return false;
    }

    /**
     * Удаление файла
     */
    public function deleteFile(string $filePath, ?Throwable $exception = null): bool
    {
        return $this->beforeDelete($filePath, $exception);
    }

    /**
     * Необходимость удаление временного файла после загрузки
     */
    public function needDeleteTempFile(): bool
    {
        return false;
    }
}
