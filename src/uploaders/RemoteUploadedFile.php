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
     * @param string $names
     * @return array
     */
    public static function getInstancesByName($names)
    {
        if (!is_array($names)) {
            return [new static($names)];
        }
        $result = [];
        foreach ($names as $item) {
            $result[] = new static($item);
        }
        return $result;
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
        $this->savedPath = $file;
        $this->getFileContent();
        return $this->putFileContent();
    }

    /**
     * Получение бинарных данных файла по ссылке
     *
     * @return string
     * @throws NotUploadFileException
     */
    protected function getFileContent()
    {
        if (empty($this->content)) {
            $this->content = file_get_contents($this->link);
            if ($this->content === false) {
                throw new NotUploadFileException('Ошибка чтения контента по ссылке: ' . $this->link);
            }
        }
        return $this->content;
    }

    /**
     * Запись бинарных данных в файл
     *
     * @return bool
     */
    protected function putFileContent()
    {
        return file_put_contents($this->savedPath, $this->content);
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
        $items = explode('.', $this->getBaseName());
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
        return 'text/plain';
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
        try {
            if ($content = $this->getFileContent()) {
                return mb_strlen($this->content);
            }
            throw new NotUploadFileException('Ошибка чтения контента по ссылке: ' . $this->link);
        } catch (NotUploadFileException $e) {
            return 0;
        }
    }
}
