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
    public $thumbParamsClass = ThumbParams::class;
    /**
     * Класс параметрической модели обработки изображений
     *
     * @var string
     */
    public $imageParamsClass = ImageParams::class;
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
        $thumbParams = $this->buildThumbParams($w, $h, $q, $p);
        $thumbPath = $this->makePath($path, $thumbParams);
        if (!file_exists($thumbPath)) {
            $this->createThumb($path, $thumbPath, $thumbParams);
        }
        return $this->convertToUrl($thumbPath);
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
     * Масштабирование по ширине без обрезки краев
     *
     * @param integer $width
     * @param integer $quality
     * @return mixed
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function widen($width, $quality = 80)
    {
        list($path, $saveTo, $params) = $this->getImageData(__FUNCTION__, $width, 0, $quality);
        if (!is_file($saveTo)) {
            $this->imageComponent->make($path)->widen($saveTo, $params);
        }
        return $this->convertToUrl($saveTo);
    }

    /**
     * Масштабирование по высоте без обрезки краев
     *
     * @param integer $height
     * @param integer $quality
     * @return mixed
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function heighten($height, $quality = 80)
    {
        list($path, $saveTo, $params) = $this->getImageData(__FUNCTION__, 0, $height, $quality);
        if (!is_file($saveTo)) {
            $this->imageComponent->make($path)->heighten($saveTo, $params);
        }
        return $this->convertToUrl($saveTo);
    }

    /**
     * Вписывание изображения в область путем пропорционального масштабирования без обрезки
     *
     * @param integer $width
     * @param integer $height
     * @param integer $quality
     * @return bool
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function contain($width = 0, $height = 0, $quality = 80)
    {
        list($path, $saveTo, $params) = $this->getImageData(__FUNCTION__, $width, $height, $quality);
        if (!is_file($saveTo)) {
            $this->imageComponent->make($path)->contain($saveTo, $params);
        }
        return $this->convertToUrl($saveTo);
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
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function cover($width = 0, $height = 0, $quality = 0, $position = null)
    {
        list($path, $saveTo, $params) = $this->getImageData(__FUNCTION__, $width, $height, $quality, $position);
        if (!is_file($saveTo)) {
            $this->imageComponent->make($path)->contain($saveTo, $params);
        }
        return $this->convertToUrl($saveTo);
    }

    /**
     * Получить путь к изображению по его параметрам
     *
     * @param string $type
     * @param int $width
     * @param int $height
     * @param int $quality
     * @param null $position
     * @return array
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    protected function getImageData($type, $width = 0, $height = 0, $quality = 80, $position = null)
    {
        $path = $this->getFilePath();
        $params = $this->buildImageParams($width, $height, $quality, $position);
        $params->addOptions('type', $type);
        $saveTo = $this->makePath($path, $params);
        return [$path, $saveTo, $params];
    }

    /**
     * Удалить все thumbnails текущего изображения
     *
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function removeAllThumbs()
    {
        $files = $this->fileStorage->searchAllFiles(
            $this->getFilePath(), $this->buildThumbParams()
        );
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Удалить все дубли текущего изображения
     *
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function removeAllImages()
    {
        $files = $this->fileStorage->searchAllFiles(
            $this->getFilePath(), $this->buildImageParams()
        );
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Удаление файлов
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
        $this->removeAllImages();
    }
}