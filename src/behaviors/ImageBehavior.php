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
use chulakov\filestorage\params\ImageMakeParams;
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
     * @param ThumbParams|null $thumbParams
     * @param ImageMakeParams|null $makeParams
     * @return string
     *
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     * @throws \yii\base\Exception
     */
    public function thumb(ThumbParams $thumbParams = null, ImageMakeParams $makeParams = null)
    {
        /** @var BaseFile $model */
        $model = $this->owner;
        if (!$model->isImage()) {
            return '';
        }
        if (!$thumbParams) {
            $thumbParams = new ThumbParams();
        }
        $path = $this->getFilePath();
        $thumbPath = $thumbParams->getSavePath($path);
        if ($makeParams) {
            $this->processing($thumbPath, $makeParams);
        }
        if (!file_exists($thumbPath)) {
            $this->createThumb($path, $thumbPath, $thumbParams);
        }
        return $this->getFileThumbUrl($thumbParams);
    }

    /**
     * Автоматическая обработка изображения в зависимости от наличия параметров
     *
     * @param string $thumbPath
     * @param ImageMakeParams $params
     * @return bool
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    protected function processing($thumbPath, ImageMakeParams $params)
    {
        $params->savePath = $thumbPath;

        if (empty($params->height)) {
            return $this->widen($params);
        } elseif (empty($params->width)) {
            return $this->heighten($params);
        } elseif (!empty($params->width) && !empty($params->height) && !empty($params->position)) {
            return $this->contain($params);
        } elseif (!empty($params->width) && !empty($params->height)) {
            return $this->cover($params);
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public
    function events()
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
     */
    protected
    function getFileThumbPath($params)
    {
        return $params->getSavePath($this->getFilePath());
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
    protected
    function getFileThumbUrl($params, $absolute = false)
    {
        return $params->getSavePath($this->getFileUrl($absolute));
    }

    /**
     * Получить путь к файлу по модели
     *
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    protected
    function getFilePath()
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
    protected
    function getFileUrl($absolute)
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
    protected
    function createThumb($path, $savePath, ThumbParams $params)
    {
        $image = $this->imageComponent->createImage($path, $params);
        $image->save($savePath, $params->quality);
        return true;
    }

    /**
     * Масштабирование по ширине без обрезки краев
     *
     * @param ImageMakeParams $params
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public
    function widen($params)
    {
        return $this->imageComponent->make($this->getFilePath())->widen($params);
    }

    /**
     * Масштабирование по высоте без обрезки краев
     *
     * @param ImageMakeParams $params
     * @return mixed
     * @throws NoAccessException
     * @throws \yii\base\InvalidParamException
     * @throws NotFoundFileException
     */
    public
    function heighten($params)
    {
        return $this->imageComponent->make($this->getFilePath())->heighten($params);
    }

    /**
     * Вписывание изображения в область путем пропорционального масштабирования без обрезки
     *
     * @param ImageMakeParams $params
     * @return bool
     * @throws NoAccessException
     * @throws \yii\base\InvalidParamException
     * @throws \chulakov\filestorage\exceptions\NotFoundFileException
     */
    public
    function contain(ImageMakeParams $params)
    {
        return $this->imageComponent->make($this->getFilePath())->contain($params);
    }

    /**
     * Заполнение обаласти частью изображения с обрезкой исходного,
     * отталкиваясь от точки позиционировани
     *
     * @param ImageMakeParams $params
     * @return mixed
     * @throws NoAccessException
     * @throws NotFoundFileException
     * @throws \yii\base\InvalidParamException
     */
    public
    function cover(ImageMakeParams $params)
    {
        return $this->imageComponent->make($this->getFilePath())->cover($params);
    }

    /**
     * Удалить все thumbnails текущего изображения
     *
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     * @throws \yii\base\ErrorException
     */
    public
    function removeAllThumbs()
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
    public
    function deleteFile()
    {
        $this->removeAllThumbs();
        $this->storageComponent->removeFile($this->owner);
    }
}