<?php
/**
 * Файл класса RemoteUploadedFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use yii\base\Model;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\exceptions\NotUploadFileException;

/**
 * Class RemoteUploadedFile
 * @package chulakov\filestorage\uploaders
 * @property ImageComponent $imageManager
 */
class RemoteUploadedFile implements UploadInterface, ObserverInterface
{
    /**
     * Подключение реализации функционала Observer
     */
    use ObserverTrait;

    /**
     * Ссылка на файл
     *
     * @var string
     */
    protected $link;
    /**
     * Содержимое файла
     *
     * @var string
     */
    protected $content;

    /**
     * Расширение файла
     *
     * @var string
     */
    protected $extension;
    /**
     * Размер файла
     *
     * @var integer
     */
    protected $size;
    /**
     * Mime тип файла
     *
     * @var string
     */
    protected $type;
    /**
     * Имя файла
     *
     * @var string
     */
    protected $name;

    /**
     * RemoteUploadedFile constructor.
     * @param string $link
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
     * @param string|array $names
     * @return array
     */
    public static function getInstancesByName($names)
    {
        if (!is_array($names)) {
            return [new static($names)];
        }
        $result = [];
        /** @var array $names */
        foreach ($names as $item) {
            $result[] = new static($item);
        }
        return $result;
    }

    /**
     * Получить контент файла
     *
     * @return bool|string
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
     * Сохранение файла
     *
     * @param string $file
     * @param bool $deleteFile
     * @return mixed|void
     * @throws NotUploadFileException
     */
    public function saveAs($file, $deleteFile = false)
    {
        $this->getFileContent();
        if ($this->beforeSave($file, $deleteFile)) {
            file_put_contents($file, $this->content);
        }
    }

    /**
     * Псевдособытие сохранения
     *
     * @param string $filePath
     * @param bool $deleteFile
     * @return bool
     */
    protected function beforeSave($filePath, $deleteFile = false)
    {
        $event = new Event($filePath, $deleteFile);

        $event->needSave = true;
        $event->sender = $this;

        $this->trigger(Event::SAVE_EVENT, $event);
        return $event->needSave;
    }

    /**
     * Получение информации об оригинальном именовании файла
     *
     * @return string
     */
    public function getBaseName()
    {
        if (!empty($this->name)) {
            return $this->name;
        }
        $item = explode('.', basename($this->link));
        $name = array_shift($item);
        return $name . '.' . $this->getExtension();
    }

    public function setBaseName($name)
    {
        $this->name = $name;
    }

    /**
     * Получение расширения файла
     *
     * @return string
     */
    public function getExtension()
    {
        if (!empty($this->extension)) {
            return $this->extension;
        }
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
        if (!empty($this->type)) {
            return $this->type;
        }
        if (!empty($this->content) && function_exists('finfo_buffer')) {
            if ($mimeType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $this->content)) {
                return $mimeType;
            }
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
        if (!empty($this->size)) {
            return $this->size;
        }
        echo 'asd';
        die();

        return !empty($this->content) ? mb_strlen($this->content) : 0;
    }

    /**
     * Получить файл
     *
     * @return string Path or content
     */
    public function getFile()
    {
        return $this->content;
    }

    /**
     * Обновить путь сохранения
     *
     * @param string $path
     * @return string
     */
    public function uploadPath($path)
    {
        /**
         * Получить название файла с path без его расширения
         */
        $item = explode('.', mb_substr($path, mb_strrpos($path, '/') + 1));
        $filename = array_shift($item);
        /**
         * Получить путь без названия файла
         */
        $path = mb_substr($path, 0, mb_strrpos($path, '/') + 1);
        return $path . $filename . '.' . $this->getExtension();
    }

    /**
     * Установить расширение файла
     *
     * @param string $extension
     * @return mixed
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Установить mime тип файла
     *
     * @param string $mime
     * @return mixed
     */
    public function setType($mime)
    {
        $this->type = $mime;
    }

    /**
     * Установить размер файла
     *
     * @param integer $size
     * @return mixed
     */
    public function setSize($size)
    {
        $this->size = $size;
    }
}