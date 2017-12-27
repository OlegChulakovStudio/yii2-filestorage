<?php
/**
 * Файл класса ImageBehavior
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\behaviors;

use yii\base\Behavior;
use yii\di\Instance;
use yii\helpers\FileHelper;
use chulakov\filestorage\FileStorage;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\params\ThumbParams;
use yii\rbac\Item;

/***
 * Class ThumbBehavior
 * @package chulakov\filestorage\behaviors
 */
class ThumbBehavior extends Behavior
{
    /**
     * @var BaseFile
     */
    public $owner;
    /**
     * Название компонента для работы сохранением файлов
     *
     * @var string|FileStorage
     */
    protected $storageComponent = 'fileStorage';
    /**
     * Название компонента для работы с изображениями
     *
     * @var string|ImageComponent
     */
    protected $imageComponent = 'imageComponent';
    /**
     * Проверка прав на доступ к файлу
     *
     * @var string|Item|null
     */
    protected $accessRole = null;

    /**
     * Инициализация
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->storageComponent = Instance::ensure($this->storageComponent);
        $this->imageComponent = Instance::ensure($this->imageComponent);
    }

    /**
     * Формирование thumbnail изображения
     *
     * Если thumbnail закеширован, то сразу же будет выдан url на него
     * Если нет, то оригинальное сообщение будет обрезано под нужное разрешение,
     * после закешировано, и после этого будет выдано url на изображение
     *
     * @param ThumbParams $params
     * @param bool $absolute
     * @return string
     *
     * @throws \chulakov\filestorage\exceptions\NoAccessException
     * @throws \chulakov\filestorage\exceptions\NotFoundFileException
     * @throws \yii\base\Exception
     */
    public function thumb(ThumbParams $params, $absolute = false)
    {
        /** @var BaseFile $model */
        $model = $this->owner;
        if (!$model->isImage()) {
            return '';
        }
        $path = $this->getFileThumbPath($params);
        if (!file_exists($path)) {
            $this->createThumb($path, $params);
        }
        return $this->getFileThumbUrl($params, $absolute);
    }

    /**
     * Получение полного пути до файла с превью
     *
     * @param ThumbParams $params
     * @return string
     * @throws \chulakov\filestorage\exceptions\NoAccessException
     * @throws \chulakov\filestorage\exceptions\NotFoundFileException
     * @throws \yii\base\Exception
     */
    protected function getFileThumbPath($params)
    {
        $path = $this->generateThumbPath($this->getFilePath(), $params);
        if (!is_dir(dirname($path))) {
            FileHelper::createDirectory(dirname($path));
        }
        return $path;
    }

    /**
     * Получить URL ссылку на превью
     *
     * @param ThumbParams $params
     * @param bool $absolute
     * @return string
     * @throws \chulakov\filestorage\exceptions\NoAccessException
     */
    protected function getFileThumbUrl($params, $absolute)
    {
        return $this->generateThumbPath($this->getFileUrl($absolute), $params);
    }

    /**
     * Получить путь к кешу
     *
     * @param string $basePath
     * @param ThumbParams $params
     * @return string
     */
    protected function generateThumbPath($basePath, $params)
    {
        $name = $params->width . 'x' . $params->height . '.' . $this->owner->getExtension();
        return implode(DIRECTORY_SEPARATOR, [
            dirname($basePath), 'thumbs', $this->owner->getBaseName(), $name
        ]);
    }

    /**
     * Получить путь к файлу по модели
     *
     * @return mixed
     * @throws \chulakov\filestorage\exceptions\NoAccessException
     * @throws \chulakov\filestorage\exceptions\NotFoundFileException
     */
    protected function getFilePath()
    {
        return $this->storageComponent->getFilePath($this->owner, $this->accessRole);
    }

    /**
     * Получение URL до файла
     *
     * @param bool $absolute
     * @return string
     * @throws \chulakov\filestorage\exceptions\NoAccessException
     */
    protected function getFileUrl($absolute)
    {
        return $this->storageComponent->getFileUrl($this->owner, $absolute, $this->accessRole);
    }

    /**
     * Создание thumbnail
     *
     * @param string $path
     * @param ThumbParams $params
     * @return bool
     */
    protected function createThumb($path, ThumbParams $params)
    {
        $this->imageComponent->make($path);

        $this->imageComponent->resize($params->width, $params->height);
        $this->imageComponent->convert($params->extension);
        $this->imageComponent->watermark($params->watermarkPath, $params->watermarkPosition);

        $this->imageComponent->getImage()->save($params->savedPath, $params->quality);

        return true;
    }
}