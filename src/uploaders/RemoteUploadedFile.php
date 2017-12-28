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
     * Оригинальное имя файла
     *
     * @var string
     */
    protected $sysName;
    /**
     * Расширение файла
     *
     * @var string
     */
    protected $extension;

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
     * Получение расширения файла
     *
     * @return string
     */
    public function getExtension()
    {
        if (empty($this->extension)) {
            $this->extension = strtolower(pathinfo(basename($this->getName()), PATHINFO_EXTENSION));
        }
        return $this->extension;
    }

    /**
     * Установить расширение файла
     *
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Получение полного имени файла
     *
     * @return string
     */
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = $this->getFileNameFromLink() ?: basename($this->link);
        }
        return $this->name;
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
        if ($mimeType = $this->getMimeTypeFromLink()) {
            return $mimeType;
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
        if ($length = $this->getFileSizeFormLink()) {
            return $this->size = $length;
        }
        if (!empty($this->content)) {
            return $this->size = strlen($this->content);
        }
        return 0;
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

    /**
     *  Получить системное имя файла
     *
     * @return string
     */
    public function getSysName()
    {
        if (empty($this->sysName)) {
            $this->sysName = uniqid();
        }
        return $this->sysName . '.' . $this->getExtension();
    }

    /**
     * Получить mime тип по ссылке
     *
     * @return string|null
     */
    protected function getMimeTypeFromLink()
    {
        return $this->getHeaderContent('Content-Type');
    }

    /**
     * Получить размер файла по ссылке
     *
     * @return string|null
     */
    protected function getFileSizeFormLink()
    {
        return $this->getHeaderContent('Content-Length');
    }

    /**
     * Получить имя файла по ссылке
     *
     * @return string|null
     */
    protected function getFileNameFromLink()
    {
        $header = $this->getHeaderContent('Content-Disposition');
        if (preg_match('/filename=\"([^\"]*)\";/sui', $header, $match)) {
            return trim($match[1]);
        }
        return null;
    }

    /**
     * Получить содержимое нужного заголовка
     *
     * @param string $name
     * @return string|null
     */
    protected function getHeaderContent($name)
    {
        $headers = get_headers($this->link);
        foreach ($headers as $header) {
            if (strpos($header, $name) !== false) {
                $items = explode(':', $header);
                return strtolower(trim(array_pop($items)));
            }
        }
        return null;
    }
}