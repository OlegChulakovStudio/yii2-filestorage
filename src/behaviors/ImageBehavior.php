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
     * @param integer $w Width
     * @param integer $h Height
     * @param integer $q Quality
     * @param string $p Position
     * @return string
     *
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     * @throws \yii\base\Exception
     */
    public function thumb($w = 0, $h = 0, $q = 0, $p = null)
    {
        /** @var BaseFile $model */
        $model = $this->owner;
        if (!$model->isImage()) {
            return '';
        }
        $path = $this->getFilePath();
        $thumbParams = $this->getThumbParams($w, $h, $q, $p);
        $thumbPath = $thumbParams->getSavePath($path);
        if (!file_exists($thumbPath)) {
            $this->createThumb($path, $thumbPath, $thumbParams);
        }
        return $this->getFileThumbUrl($thumbParams);
    }

    /**
     * Автоматическая обработка изображения в зависимости от наличия параметров
     *
     * @param integer $w Width
     * @param integer $h Height
     * @param integer $q Quality
     * @param string $p Position
     * @return ThumbParams
     *
     * @throws \yii\base\InvalidParamException
     * @throws \chulakov\filestorage\exceptions\NoAccessException
     * @throws NotFoundFileException
     */
    protected function getThumbParams($w = 0, $h = 0, $q = 0, $p = null)
    {
        $thumbParams = new ThumbParams();

        list($width, $height) = getimagesize($this->getFilePath());

        if (!empty($w) && empty($h)) {
            $thumbParams->width = $w;
            $thumbParams->height = round($height / ($width / $w));
        }
        if (!empty($h) && empty($w)) {
            $thumbParams->height = $h;
            $thumbParams->width = round($width / ($height / $h));
        }
        if (!empty($q)) {
            $thumbParams->quality = $q;
        }
        if (!empty($p)) {
            $thumbParams->coverPosition = $p;
        }
        return $thumbParams;
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
     */
    protected function getFileThumbPath($params)
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
    protected function getFileThumbUrl($params, $absolute = false)
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
     * Масштабирование по ширине без обрезки краев
     *
     * @param integer $width
     * @param integer $quality
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function widen($width, $quality = 80)
    {
        list($path, $params) = $this->getImageData($width, 0, $quality);
        return $this->imageComponent->make($this->getFilePath())->widen($path, $params);
    }

    /**
     * Масштабирование по высоте без обрезки краев
     *
     * @param integer $height
     * @param integer $quality
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function heighten($height, $quality = 80)
    {
        list($path, $params) = $this->getImageData(0, $height, $quality);
        return $this->imageComponent->make($this->getFilePath())->heighten($path, $params);
    }

    /**
     * Вписывание изображения в область путем пропорционального масштабирования без обрезки
     *
     * @param integer $width
     * @param integer $height
     * @param integer $quality
     * @return bool
     *
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function contain($width = 0, $height = 0, $quality = 80)
    {
        list($path, $params) = $this->getImageData($width, $height, $quality);
        return $this->imageComponent->make($this->getFilePath())->contain($path, $params);
    }

    /**
     * Заполнение обаласти частью изображения с обрезкой исходного,
     * отталкиваясь от точки позиционировани
     *
     * @param integer $width
     * @param integer $height
     * @param integer $quality
     * @param string|null $position
     * @return mixed
     *
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function cover($width = 0, $height = 0, $quality = 0, $position = null)
    {
        list($path, $params) = $this->getImageData($width, $height, $quality, $position);
        return $this->imageComponent->make($this->getFilePath())->contain($path, $params);
    }

    /**
     * Получить путь к изображению по его параметрам
     *
     * @param int $width
     * @param int $height
     * @param int $quality
     * @param null $position
     * @return array
     *
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    protected function getImageData($width = 0, $height = 0, $quality = 80, $position = null)
    {
        $params = $this->getThumbParams($width, $height, $quality, $position);
        return [
            $params->getSavePath($this->getFilePath()),
            $params
        ];
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
        list($name) = explode('.', basename($this->owner->sys_file));

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