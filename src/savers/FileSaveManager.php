<?php
/**
 * Файл класса FileSaveManager
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\savers;

use yii\base\BaseObject;

/**
 * Class FileSaveManager
 * @package chulakov\filestorage\savers
 */
class FileSaveManager extends BaseObject implements SaveInterface
{
    /**
     * Сохранено или нет
     *
     * @var bool
     */
    protected $saved = false;
    /**
     * Путь к сохраненному файлу
     *
     * @var string
     */
    protected $savedPath;
    /**
     * Путь к оригинальному файлу
     *
     * @var string
     */
    protected $filePath;

    /**
     * Сохранение файла
     *
     * @param string $savePath
     * @param string $filePath
     * @param bool $deleteTempFile
     * @return mixed
     */
    public function save($savePath, $filePath, $deleteTempFile = true)
    {
        $this->savedPath = $savePath;
        $this->filePath = $filePath;

        if ($deleteTempFile) {
            return move_uploaded_file($this->filePath, $this->savedPath);
        } elseif (is_uploaded_file($this->filePath)) {
            return copy($this->filePath, $this->savedPath);
        }
        return false;
    }

    /**
     * Проверка, выполнено ли было сохранение или нет
     *
     * @return mixed
     */
    public function isSaved()
    {
        return $this->saved;
    }

    /**
     * Получить расширение файла
     *
     * @return mixed
     */
    public function getExtension()
    {
        $ext = pathinfo($this->savedPath, PATHINFO_EXTENSION) ?: pathinfo($this->filePath, PATHINFO_EXTENSION) ?: false;
        return $ext ? strtolower($ext) : false;
    }

    /**
     * Получить расширение файла
     *
     * @param string $path
     * @return null|string
     */
    protected function getMimeType($path)
    {
        return !empty($path) ? mime_content_type($path) : false;
    }

    /**
     * Получение MIME типа файла
     *
     * @return string
     */
    public function getType()
    {
        return $this->getMimeType($this->savedPath) ?: $this->getMimeType($this->filePath) ?: false;
    }

    /**
     * Получение размера файла
     *
     * @return integer
     */
    public function getSize()
    {
        return filesize($this->savedPath) ?: filesize($this->filePath) ?: 0;
    }
}