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
use yii\base\Model;

/**
 * Class RemoteUploadedFile
 * @package chulakov\filestorage\uploaders
 * @property ImageComponent $imageManager
 */
class RemoteImageUploadedFile extends ImageUploadedFile implements UploadInterface
{
    /**
     * @var string
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
     * @param string $path
     */
    public function __construct($path)
    {
        $this->tempName = $path;
        parent::__construct();
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
     * Сохранение файла
     *
     * @param string $file
     * @param bool $deleteTempFile
     * @return bool|mixed|void
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        parent::saveAs($file, false);
    }

    /**
     * Получение информации об оригинальном именовании файла
     *
     * @return string
     */
    public function getBaseName()
    {
        return basename($this->tempName);
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
        if ($this->savedPath && file_exists($this->savedPath)) {
            return mime_content_type($this->savedPath);
        }
        return $this->imageManager->getImage()->mime();
    }

    /**
     * Получение размера файла
     *
     * @return integer
     */
    public function getSize()
    {
        if ($this->savedPath && file_exists($this->savedPath)) {
            return filesize($this->savedPath);
        }
        try {
            return $this->imageManager->getImage()->filesize();
        } catch (\Exception $e) {
            return 0;
        }
    }
}