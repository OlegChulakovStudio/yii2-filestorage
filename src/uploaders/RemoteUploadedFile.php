<?php
/**
 * Файл класса RemoteUploadedFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use Exception;
use yii\base\Model;
use yii\base\BaseObject;
use chulakov\filestorage\observer\ObserverTrait;
use chulakov\filestorage\observer\ObserverInterface;
use chulakov\filestorage\exceptions\NotUploadFileException;

/**
 * Class RemoteUploadedFile
 * @package chulakov\filestorage\uploaders
 */
class RemoteUploadedFile extends BaseObject implements UploadInterface, ObserverInterface
{
    /**
     * Подключение реализации функционала Observer
     */
    use ObserverTrait;

    /**
     * Ссылка на файл (UploadedFiles::tempName)
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
    public $size;
    /**
     * Mime тип файла
     *
     * @var string
     */
    public $type;
    /**
     * Имя файла
     *
     * @var string
     */
    public $name;
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
     * Http заголовки ссылки
     *
     * @var array
     */
    protected $headers;

    /**
     * Конструктор файла по ссылке
     *
     * @param string $link
     * @param array $config
     */
    public function __construct($link, $config = [])
    {
        $this->link = $link;
        parent::__construct($config);
    }

    /**
     * Инициализация базовых параметров файла
     */
    public function init()
    {
        parent::init();
        $this->setName($this->getFileNameFromLink());
        $this->setType($this->getMimeTypeFromLink());
        $this->setSize($this->getFileSizeFormLink());
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
     * @return boolean
     * @throws NotUploadFileException
     */
    public function saveAs($file, $deleteFile = false)
    {
        if ($this->beforeSave($file, $deleteFile)) {
            return file_put_contents($file, $this->getContent());
        }
        return false;
    }

    /**
     * Удаление файла
     *
     * @param string $filePath
     * @param Exception|null $exception
     * @return bool
     */
    public function deleteFile($filePath, $exception = null)
    {
        return $this->beforeDelete($filePath, $exception);
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
        return strtolower(pathinfo(basename($this->getName()), PATHINFO_EXTENSION));
    }

    /**
     * Установить расширение файла
     *
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->setName($this->getBaseName() . '.' . $extension);
    }

    /**
     * Получение полного имени файла
     *
     * @return string
     */
    public function getName()
    {
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
        if (!empty($this->content)) {
            return $this->size = strlen($this->content);
        }
        if ($length = $this->getFileSizeFormLink()) {
            return $this->size = $length;
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
     * Установить системное имя
     *
     * @param string $sysName
     */
    public function setSysName($sysName)
    {
        $this->sysName = $sysName;
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
        return basename($this->link);
    }

    /**
     * Получить содержимое нужного заголовка
     *
     * @param string $name
     * @return string|null
     */
    protected function getHeaderContent($name)
    {
        if (empty($this->headers) && $headers = get_headers($this->link)) {
            foreach ($headers as $header) {
                $items = explode(':', $header);
                if (count($items) == 2) {
                    list($name, $value) = explode(':', $header);
                    $this->headers[$name] = trim($value);
                }
            }
        }
        if (!empty($this->headers[$name])) {
            return $this->headers[$name];
        }
        return null;
    }
}