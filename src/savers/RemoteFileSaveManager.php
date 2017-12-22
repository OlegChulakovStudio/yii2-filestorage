<?php
/**
 * Файл класса RemoteFileSaveManager
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\savers;

use yii\base\BaseObject;

/**
 * Class RemoteFileSaveManager
 * @package chulakov\filestorage\savers
 */
class RemoteFileSaveManager extends BaseObject implements SaveInterface
{
    /**
     * Путь сохранения
     *
     * @var string
     */
    protected $savedPath;
    /**
     * Сохранен или нет
     *
     * @var bool
     */
    protected $saved = false;
    /**
     * Содержимое контента
     *
     * @var string
     */
    protected $content;

    /**
     * Сохранение файла
     *
     * @param string $path
     * @param string $content
     * @param bool $deleteTempFile
     * @return mixed
     */
    public function save($path, $content, $deleteTempFile = false)
    {
        $this->savedPath = $path;
        file_put_contents($path, $content);
        return true;
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
     * @return mixed
     */
    public function getExtension()
    {
        if ($this->isSaved()) {
            $items = explode('.', basename($this->savedPath));
            return array_pop($items);
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
        if ($this->isSaved()) {
            return filesize($this->savedPath);
        }
        if ($this->content && !empty($this->content)) {
            return mb_strlen($this->content);
        }
        return 0;
    }
}