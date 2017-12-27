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
     * Конфигурация компонента
     *
     * @param array $config
     * @return mixed|void
     * @throws \yii\base\InvalidConfigException
     */
    public function configure($config)
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
        $this->initListener();
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
     * Сохранение файла
     *
     * @param string $file
     * @param bool $deleteFile
     * @return mixed|void
     * @throws NotUploadFileException
     */
    public function saveAs($file, $deleteFile = false)
    {
        if ($this->beforeSave($file, $deleteFile)) {
            file_put_contents($file, $this->getContent());
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
     * Получить файл
     *
     * @return string
     */
    public function getFile()
    {
        return $this->link;
    }

    /**
     * Получить файл
     *
     * @return string
     * @throws NotUploadFileException
     */
    public function getContent()
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
     * Получение информации об оригинальном именовании файла
     *
     * @return string
     */
    public function getBaseName()
    {
        $pathInfo = pathinfo('_' . basename($this->getName()), PATHINFO_FILENAME);
        return mb_substr($pathInfo, 1, mb_strlen($pathInfo, '8bit'), '8bit');
    }

    /**
     * Получение имени файла после сохранения
     *
     * @return string
     */
    public function getSavedName()
    {
        return $this->getName();
    }

    /**
     * Получение расширения файла
     *
     * @return string
     */
    public function getExtension()
    {
        return strtolower(pathinfo(basename($this->getName()), PATHINFO_EXTENSION));
    }

    /**
     * Получение полного имени файла
     *
     * @return string
     */
    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }
        return basename($this->link);
    }

    /**
     * Устанавка полного имени файла
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Установить mime тип файла
     *
     * @param string $mime
     */
    public function setType($mime)
    {
        $this->type = $mime;
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
        return !empty($this->content)
            ? mb_strlen($this->content) : 0;
    }

    /**
     * Установить размер файла
     *
     * @param integer $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }
}