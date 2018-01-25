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
 * Поведение, позволяющее модифицировать исходный файл изображения
 *
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
     * @param integer $w
     * @param integer $h
     * @param integer $q
     * @param string $p
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function thumb($w = 195, $h = 144, $q = 80, $p = null)
    {
        return $this->makeImage(
            __FUNCTION__,
            $this->buildThumbParams($w, $h, $q, $p)
        );
    }

    /**
     * Масштабирование по ширине без обрезки краев
     *
     * @param integer $w
     * @param integer $q
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function widen($w, $q = 80)
    {
        return $this->makeImage(
            __FUNCTION__,
            $this->buildImageParams($w, 0, $q)
        );
    }

    /**
     * Масштабирование по высоте без обрезки краев
     *
     * @param integer $h
     * @param integer $q
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function heighten($h, $q = 80)
    {
        return $this->makeImage(
            __FUNCTION__,
            $this->buildImageParams(0, $h, $q)
        );
    }

    /**
     * Вписывание изображения в область путем пропорционального масштабирования без обрезки
     *
     * @param integer $w
     * @param integer $h
     * @param integer $q
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function contain($w, $h, $q = 80)
    {
        return $this->makeImage(
            __FUNCTION__,
            $this->buildImageParams($w, $h, $q)
        );
    }

    /**
     * Заполнение обаласти частью изображения с обрезкой исходного,
     * отталкиваясь от точки позиционировани
     *
     * @param integer $w
     * @param integer $h
     * @param integer $q
     * @param string|null $p
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    public function cover($w, $h, $q = 80, $p = null)
    {
        return $this->makeImage(
            __FUNCTION__,
            $this->buildImageParams($w, $h, $q, $p)
        );
    }

    /**
     * Удалить все thumbnails текущего изображения
     *
     * @throws \yii\base\ErrorException
     */
    public function removeAllThumbs()
    {
        return $this->removeAllFiles($this->buildThumbParams(0, 0));
    }

    /**
     * Удалить все дубли текущего изображения
     *
     * @throws \yii\base\ErrorException
     */
    public function removeAllImages()
    {
        return $this->removeAllFiles($this->buildImageParams(0, 0));
    }

    /**
     * Удалить файлы по указанному параметру
     *
     * @param PathParams $params
     * @return bool
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
     *
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
     * @param integer $w
     * @param integer $h
     * @param integer $q
     * @param string|null $p
     * @return ThumbParams
     */
    protected function buildThumbParams($w, $h, $q = 0, $p = null)
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
    protected function buildImageParams($w, $h, $q = 0, $p = null)
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
    protected function buildParams($class, $w, $h, $q = 80, $p = null)
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
     * @param string $method
     * @param ImageParams $params
     * @return string
     * @throws NoAccessException
     * @throws NotFoundFileException
     */
    protected function makeImage($method, ImageParams $params)
    {
        /** @var BaseFile $model */
        $model = $this->owner;
        if (!$model->isImage()) {
            return '';
        }
        $path = $this->getFilePath();
        $params->addOption('type', $method);
        $savePath = $this->fileStorage->getAbsolutePath(
            $this->makePath($path, $params)
        );
        if (!is_file($savePath)) {
            $image = $this->imageComponent->make($path);
            if (!method_exists($image, $method)) {
                return '';
            }
            $image->{$method}($savePath, $params);
        }
        return $this->convertToUrl($savePath);
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
     * Получить полный путь системного файла
     *
     * @return string
     */
    protected function getFullSysPath()
    {
        return $this->fileStorage->getFullSysPath($this->owner);
    }
}