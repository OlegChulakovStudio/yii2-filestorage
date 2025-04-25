<?php
/**
 * Файл класса ImageBehavior
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\behaviors;

use chulakov\filestorage\exceptions\NoAccessException;
use chulakov\filestorage\FileStorage;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\models\Image;
use chulakov\filestorage\params\ImageParams;
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\params\ThumbParams;
use Exception;
use Yii;
use yii\base\Behavior;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\di\Instance;
use yii\rbac\Item;

/***
 * Поведение, позволяющее модифицировать исходный файл изображения
 *
 * @package chulakov\filestorage\behaviors
 */
class ImageBehavior extends Behavior
{
    /**
     * @var Image
     */
    public $owner;
    /**
     * Класс параметрической модели обработки превью
     */
    public string $thumbParamsClass = 'chulakov\filestorage\params\ThumbParams';
    /**
     * Класс параметрической модели обработки изображений
     */
    public string $imageParamsClass = 'chulakov\filestorage\params\ImageParams';
    /**
     * Название компонента для работы сохранением файлов
     */
    public FileStorage|string $fileStorage = 'fileStorage';
    /**
     * Название компонента для работы с изображениями
     */
    public ImageComponent|string $imageComponent = 'imageComponent';
    /**
     * Проверка прав на доступ к файлу
     */
    public Item|string|null $accessRole = null;

    /**
     * Инициализация
     *
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->fileStorage = Instance::ensure($this->fileStorage);
        $this->imageComponent = Instance::ensure($this->imageComponent);
    }

    /**
     * @inheritdoc
     */
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_DELETE => [$this, 'deleteFile'],
        ];
    }

    /**
     * Формирование thumbnail изображения
     *
     * Если thumbnail закеширован, то сразу же будет выдан url на него
     * Если нет, то оригинальное сообщение будет обрезано под нужное разрешение,
     * после закешировано, и после этого будет выдано url на изображение
     */
    public function thumb(int $w = 195, int $h = 144, int $q = 80): bool|string
    {
        return $this->makeImage(__FUNCTION__, $this->buildThumbParams($w, $h, $q));
    }

    /**
     * Масштабирование по ширине без обрезки краев
     */
    public function widen(int $w, int $q = 80): bool|string
    {
        return $this->makeImage(__FUNCTION__, $this->buildImageParams($w, 0, $q));
    }

    /**
     * Масштабирование по высоте без обрезки краев
     */
    public function heighten(int $h, int $q = 80): bool|string
    {
        return $this->makeImage(__FUNCTION__, $this->buildImageParams(0, $h, $q));
    }

    /**
     * Вписывание изображения в область путем пропорционального масштабирования без обрезки
     */
    public function contain(int $w, int $h, int $q = 80): bool|string
    {
        return $this->makeImage(__FUNCTION__, $this->buildImageParams($w, $h, $q));
    }

    /**
     * Заполнение области частью изображения с обрезкой исходного,
     * отталкиваясь от точки позиционировании
     */
    public function cover(int $w, int $h, int $q = 80, ?string $p = null): bool|string
    {
        return $this->makeImage(__FUNCTION__, $this->buildImageParams($w, $h, $q, $p));
    }

    /**
     * Удаление файлов
     *
     * @throws ErrorException
     */
    public function deleteFile(): void
    {
        $this->removeAllThumbs();
        $this->removeAllImages();
    }

    /**
     * Удалить все thumbnails текущего изображения
     *
     * @throws ErrorException
     */
    public function removeAllThumbs(): bool
    {
        return $this->removeAllFiles($this->buildThumbParams());
    }

    /**
     * Удалить все дубли текущего изображения
     *
     * @throws ErrorException
     */
    public function removeAllImages(): bool
    {
        return $this->removeAllFiles($this->buildImageParams());
    }

    /**
     * Удалить файлы по указанному параметру
     */
    public function removeAllFiles(PathParams $params): bool
    {
        return $this->fileStorage->removeAllFiles($this->owner, $params);
    }

    /**
     * Генерация параметров для thumbnails
     */
    protected function buildThumbParams(int $w = 0, int $h = 0, int $q = 0): ImageParams|ThumbParams
    {
        return $this->buildParams($this->thumbParamsClass, $w, $h, $q);
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
    protected function buildImageParams(int $w = 0, int $h = 0, int $q = 0, ?string $p = null): mixed
    {
        return $this->buildParams($this->imageParamsClass, $w, $h, $q, $p);
    }

    /**
     * Автоматическая обработка изображения в зависимости от наличия параметров
     *
     * @param class-string $class Класс параметров который будет создан
     * @param integer $w Width
     * @param integer $h Height
     * @param integer $q Quality
     * @param string|null $p Position
     */
    protected function buildParams(string $class, int $w, int $h, int $q = 80, ?string $p = null): ImageParams
    {
        /** @var ImageParams $params */
        $params = new $class($w, $h);
        if ($q > 0) {
            $params->quality = $q;
        }
        if (isset($p)) {
            $params->coverPosition = $p;
        }
        return $params;
    }

    /**
     * Получить путь к изображению по его параметрам
     */
    protected function makeImage(string $method, ImageParams $params): bool|string
    {
        $model = $this->owner;
        if ($model->isImage() === false) {
            return $this->getNoImage($params->width, $params->height);
        }
        if ($model->isSvg()) {
            return $model->getUrl();
        }

        try {
            $path = $this->getFilePath();
            $params->addOption('type', $method);
            $savePath = $this->fileStorage->getAbsolutePath(
                $this->makePath($path, $params),
            );
            if ($this->existFile($savePath) === false) {
                $image = $this->imageComponent->make($path);
                if (method_exists($image, $method) === false) {
                    return $this->getNoImage($params->width, $params->height);
                }
                $image->{$method}($savePath, $params);
            }
            return $this->convertToUrl($savePath);
        } catch (Exception $e) {
            Yii::error($e);
            return $this->getNoImage($params->width, $params->height);
        }
    }

    /**
     * URL до превью
     */
    protected function convertToUrl(string $path): string
    {
        return $this->fileStorage->convertToUrl($path);
    }

    /**
     * Парсинг пути для сохранения
     */
    protected function makePath(string $path, ImageParams $params): string
    {
        return $this->fileStorage->makePath($path, $params);
    }

    /**
     * Получить путь к файлу по модели
     *
     * @throws NoAccessException
     */
    protected function getFilePath(): string
    {
        return $this->fileStorage->getFilePath($this->owner, $this->accessRole);
    }

    /**
     * Получение No Image файла
     */
    protected function getNoImage(int $width = 50, int $height = 50): bool|string
    {
        if ($this->owner->isImage()) {
            [$width, $height] = $this->owner->resolveSize($width, $height);
        }
        return $this->fileStorage->getNoImage($width, $height);
    }

    protected function existFile(string $savePath): bool
    {
        return $this->fileStorage->existFile($savePath);
    }
}
