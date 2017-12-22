<?php
/**
 * Файл класса RemoteUploadedFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use chulakov\filestorage\exceptions\NotUploadFileException;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\savers\SaveInterface;
use yii\base\Model;

/**
 * Class RemoteUploadedFile
 * @package chulakov\filestorage\uploaders
 * @property ImageComponent $imageManager
 */
class RemoteUploadedFile implements UploadInterface
{
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
     * @var SaveInterface
     */
    public $saveManager;

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
     * @param bool $deleteTempFile
     * @return mixed|void
     * @throws NotUploadFileException
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        $this->getFileContent();
        $this->saveManager->save($file, $this->content, false);
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
        if ($ext = $this->saveManager->getExtension()) {
            return $ext;
        }
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
        if ($mimeType = $this->saveManager->getType()) {
            return $mimeType;
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
        return $this->saveManager->getSize();
    }
}