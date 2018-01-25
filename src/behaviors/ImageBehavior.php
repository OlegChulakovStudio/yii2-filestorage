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
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\params\ThumbParams;
use chulakov\filestorage\params\ImageParams;
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
     * Класс параметрической модели обработки превью
     *
     * @var string
     */
    public $thumbParamsClass = 'chulakov\filestorage\params\ThumbParams';
    /**
     * Класс параметрической модели обработки изображений
     *
     * @var string
     */
    public $imageParamsClass = 'chulakov\filestorage\params\ImageParams';
    /**
     * Название компонента для работы сохранением файлов
     *
     * @var string|FileStorage
     */
    public $fileStorage = 'fileStorage';
    /**
     * Название компонента для работы с изображениями
     *
     * @var string|ImageComponent
     */
    public $imageComponent = 'imageComponent';
    /**
     * Проверка прав на доступ к файлу
     *
     * @var string|Item|null
     */
    public $accessRole = null;

    /**
     * Инициализация
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->fileStorage = Instance::ensure($this->fileStorage);
        $this->imageComponent = Instance::ensure($this->imageComponent);
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
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function thumb($w = 0, $h = 0, $q = 0, $p = null)
    {
        return $this->getImageData(
            __FUNCTION__,
            $this->buildThumbParams($w, $h, $q, $p)
        );
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
        return $this->getImageData(
            __FUNCTION__,
            $this->buildImageParams($width, 0, $quality)
        );
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
        return $this->getImageData(
            __FUNCTION__,
            $this->buildImageParams(0, $height, $quality)
        );
    }

    /**
     * Вписывание изображения в область путем пропорционального масштабирования без обрезки
     *
     * @param integer $width
     * @param integer $height
     * @param integer $quality
     * @return bool
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function contain($width = 0, $height = 0, $quality = 80)
    {
        return $this->getImageData(
            __FUNCTION__,
            $this->buildImageParams($width, $height, $quality)
        );
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
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function cover($width = 0, $height = 0, $quality = 0, $position = null)
    {
        return $this->getImageData(
            __FUNCTION__,
            $this->buildImageParams($width, $height, $quality, $position)
        );
    }

    /**
     * Удалить все thumbnails текущего изображения
     *
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\ErrorException
     */
    public function removeAllThumbs()
    {
        return $this->removeAllFiles($this->buildThumbParams());
    }

    /**
     * Удалить все дубли текущего изображения
     *
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\ErrorException
     */
    public function removeAllImages()
    {
        return $this->removeAllFiles($this->buildImageParams());
    }

    /**
     * Удалить файлы по указанному параметру
     *
     * @param PathParams $params
     * @return bool
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\ErrorException
     */
    public function removeAllFiles(PathParams $params)
    {
        $origPath = $this->getFullSysPath();
        $files = $this->fileStorage->searchAllFiles($origPath, $params);
        foreach ($files as $file) {
            if (file_exists($file)) {
                $dirName = dirname($file);
                if (is_dir($dirName)) {
                    FileHelper::removeDirectory($dirName);
                }
            }
        }
        return true;
    }

    /**
     * Удаление файлов
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\ErrorException
     */
    public function deleteFile()
    {
        $this->removeAllThumbs();
        $this->removeAllImages();
    }

    /**
     * Генерация параметров для thumbnails
     *
     * @param integer $w Width
     * @param integer $h Height
     * @param integer $q Quality
     * @param string $p Position
     * @return ThumbParams
     */
    protected function buildThumbParams($w = 0, $h = 0, $q = 0, $p = null)
    {
        return $this->buildParams($this->thumbParamsClass, $w, $h, $q, $p);
    }

    /**
     * Генерация параметров для изображения
     *
     * @param int $w Width
     * @param int $h Height
     * @param int $q Quality
     * @param string|null $p Position
     * @return mixed
     */
    protected function buildImageParams($w = 0, $h = 0, $q = 0, $p = null)
    {
        return $this->buildParams($this->imageParamsClass, $w, $h, $q, $p);
    }

    /**
     * Автоматическая обработка изображения в зависимости от наличия параметров
     *
     * @param string $class
     * @param integer $w Width
     * @param integer $h Height
     * @param integer $q Quality
     * @param string|null $p Position
     * @return PathParams|ImageParams|ThumbParams
     */
    protected function buildParams($class, $w, $h, $q, $p)
    {
        $params = new $class($w, $h);
        if ($q > 0) {
            $params->quality = $q;
        }
        if (!empty($p)) {
            $params->coverPosition = $p;
        }
        return $params;
    }

    /**
     * Получить путь к изображению по его параметрам
     *
     * @param callable $method
     * @param ImageParams $params
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    protected function getImageData($method, ImageParams $params)
    {
        /** @var BaseFile $model */
        $model = $this->owner;
        if (!$model->isImage()) {
            return '';
        }
        $params->addOption('type', $method);
        $path = $this->getFilePath();
        $pathForUrl = $this->makePath($path, $params);
        $savePath = $this->fileStorage->getAbsolutePath(
            $pathForUrl
        );
        if (!is_file($savePath)) {
            $image = $this->imageComponent->make($path);
            if (!method_exists($image, $method)) {
                return '';
            }
            $image->{$method}($savePath, $params);
        }
        return $this->convertToUrl($pathForUrl);
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
        return $this->imageComponent->createImage($path, $params)
            ->save($savePath, $params->quality);
    }

    /**
     * Получение полного пути до файла с превью
     *
     * @param ThumbParams $params
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    protected function getFileThumbPath($params)
    {
        return $this->fileStorage->makePath($this->getFilePath(), $params);
    }

    /**
     * URL до превью
     *
     * @param string $path
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    protected function convertToUrl($path)
    {
        return $this->fileStorage->convertToUrl($path);
    }

    /**
     * Парсинг пути для сохранения
     *
     * @param string $path
     * @param ImageParams $params
     * @return string
     */
    protected function makePath($path, $params)
    {
        return $this->fileStorage->makePath($path, $params);
    }

    /**
     * Получить путь к файлу по модели
     *
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    protected function getFilePath()
    {
        return $this->fileStorage->getFilePath($this->owner, $this->accessRole);
    }

    /**
     * Получение URL до файла
     *
     * @param bool $absolute
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    protected function getFileUrl($absolute = false)
    {
        return $this->fileStorage->getFileUrl($this->owner, $absolute, $this->accessRole);
    }

    /**
     * Получить полный путь системного файла
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    protected function getFullSysPath()
    {
        return $this->fileStorage->getFullSysPath($this->owner);
    }
}