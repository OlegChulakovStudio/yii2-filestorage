<?php
/**
 * Файл класса ImageBehavior
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\behaviors;

use yii\rbac\Item;
use yii\di\Instance;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use chulakov\filestorage\FileStorage;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\params\ThumbParams;
use chulakov\filestorage\exceptions\NoAccessException;
use chulakov\filestorage\exceptions\NotFoundFileException;

/***
 * Class ImageBehavior
 * @package chulakov\filestorage\behaviors
 */
class ImageBehavior extends Behavior
{
    /**
     * @var BaseFile
     */
    public $owner;
    /**
     * Название создаваемой группы для хранения
     *
     * @var string
     */
    protected $groupName = 'thumbs';
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
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     * @throws \yii\base\Exception
     */
    public function thumb(ThumbParams $params = null, $absolute = false)
    {
        /** @var BaseFile $model */
        $model = $this->owner;
        if (!$model->isImage()) {
            return '';
        }
        if (!$params) {
            $params = new ThumbParams();
        }
        $path = $this->getFilePath();
        $thumbPath = $params->getSavePath($path);
        if (!file_exists($thumbPath)) {
            $this->createThumb($path, $thumbPath, $params);
        }
        return $this->getFileThumbUrl($params);
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => [$this, 'deleteFile']
        ];
    }

    /**
     * Получение полного пути до файла с превью
     *
     * @param ThumbParams $params
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
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
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     */
    protected function getFileThumbUrl($params, $absolute = false)
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
        $name = $params->width . 'x' . $params->height . '.' . $params->extension;
        return implode(DIRECTORY_SEPARATOR, [
            dirname($basePath), $this->groupName, $this->owner->getBaseName(), $name
        ]);
    }

    /**
     * Получить путь к файлу по модели
     *
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
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
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     */
    protected function getFileUrl($absolute)
    {
        return $this->storageComponent->getFileUrl($this->owner, $absolute, $this->accessRole);
    }

    /**
     * Создание thumbnail
     *
     * @param string $path
     * @param string $savePath
     * @param ThumbParams $params
     * @return bool
     * @throws \yii\base\Exception
     */
    protected function createThumb($path, $savePath, ThumbParams $params)
    {
        $image = $this->imageComponent->createImage($path, $params);
        $image->save($savePath, $params->quality);
        return true;
    }

    /**
     * Удалить все thumbnails текущего изображения
     *
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     * @throws \yii\base\ErrorException
     */
    public function removeAllThumbs()
    {
        list($name, $ext) = explode('.', basename($this->owner->sys_file));

        $path = implode('/', [
            dirname($this->getFilePath()),
            $this->groupName,
            $name
        ]);

        if (is_dir($path)) {
            FileHelper::removeDirectory($path);
        }
    }

    /**
     * Удаление файла и модели
     *
     * @throws \yii\base\InvalidParamException
     * @throws \Exception
     * @throws NoAccessException
     * @throws NotFoundFileException
     * @throws \yii\base\ErrorException
     * @throws \Throwable
     */
    public function deleteFile()
    {
        $this->removeAllThumbs();
        $this->storageComponent->removeFile($this->owner);
    }
}