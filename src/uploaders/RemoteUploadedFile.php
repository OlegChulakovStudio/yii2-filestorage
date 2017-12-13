<?php
/**
 * Файл класса RemoteUploadedFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use chulakov\filestorage\exceptions\NotUploadFileException;
use yii\base\Model;

class RemoteUploadedFile implements UploadInterface
{
    /**
     * @var string link on file
     */
    protected $link;
    /**
     * @var string
     */
    protected $savedPath;
    /**
     * @var string
     */
    protected $content;

    /**
     * RemoteUploadedFile constructor.
     * @param $link
     */
    public function __construct($link)
    {
        $this->link = $link;
    }

    /**
     * Инициализация одной модели
     *
     * @param Model $model
     * @param string $attribute
     * @return mixed
     */
    public static function getInstance($model, $attribute)
    {
        return self::getInstanceByName($model->{$attribute});
    }

    /**
     * Инициализация массива моделей
     *
     * @param Model $model
     * @param string $attribute
     * @return mixed
     */
    public static function getInstances($model, $attribute)
    {
        return self::getInstanceByName($model->{$attribute});
    }

    /**
     * Инициализация одной модели по имени атрибута
     *
     * @param string $link
     * @return mixed|static
     */
    public static function getInstanceByName($link)
    {
        return new static($link);
    }

    /**
     * Инициализация массива моделей по имени атрибута
     *
     * @param string $name
     * @return mixed
     */
    public static function getInstancesByName($name)
    {
        // TODO: Implement getInstancesByName() method.
    }

    /**
     * Сохранение файла по указанному пути
     *
     * @param string $file
     * @param bool $deleteTempFile
     * @return bool|mixed
     * @throws NotUploadFileException
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        $this->content = $this->getFileContent($this->link);
        $this->savedPath = $file;
        return $this->putFileContent($file);
    }

    /**
     * Получение бинарных данных файла по ссылке
     *
     * @param $link
     * @return bool|string
     * @throws NotUploadFileException
     */
    protected function getFileContent($link)
    {
        $content = file_get_contents($link);
        if (!$content) {
            throw new NotUploadFileException('Ошибка чтения контента. Ссылка: ' . $link);
        }
        return $content;
    }

    /**
     * Запись бинарных данных в файл
     *
     * @param $path
     * @return bool
     */
    protected function putFileContent($path)
    {
        return file_put_contents($path, $this->content);
    }

    /**
     * Получение информации об оригинальном именовании файла
     *
     * @return string
     */
    public function getBaseName()
    {
        return basename($this->link);
    }

    /**
     * Получение расширения файла
     *
     * @return string
     */
    public function getExtension()
    {
        $items = explode('.', basename($this->link));
        return array_pop($items);
    }

    /**
     * Получение MIME типа файла
     *
     * @return string
     */
    public function getType()
    {
        if ($this->savedPath) {
            return mime_content_type($this->savedPath);
        }
        if (function_exists('finfo_buffer')) {
            return finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $this->content);
        }

        $filePath = \Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . uniqid() . '.' . $this->getType();
        try {
            $this->putFileContent($filePath);
        } catch (NotUploadFileException $e) {
        }
        $type = mime_content_type($filePath);
        unlink($filePath);
        return $type;
    }

    /**
     * Получение размера файла
     *
     * @return integer
     */
    public function getSize()
    {
        if ($this->savedPath) {
            return filesize($this->savedPath);
        }
        if ($this->content) {
            return mb_strlen($this->content);
        }
        return 0;
    }
}