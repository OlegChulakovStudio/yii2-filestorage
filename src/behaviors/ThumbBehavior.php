<?php
/**
 * Файл класса ImageBehavior
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\behaviors;

use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\params\ThumbParams;
use sem\helpers\FileHelper;
use yii\base\Behavior;

class ThumbBehavior extends Behavior
{
    /**
     * Название компонента для работы сохранением файлов
     * @var string
     */
    protected $storageComponent = 'fileStorage';
    /**
     * Название компонента для работы с изображениями
     *
     * @var string
     */
    protected $imageComponent = 'imageComponent';

    /**
     * Формирование thumbnail изображения
     *
     * Если thumbnail закеширован, то сразу же будет выдан url на него
     * Если нет, то оригинальное сообщение будет обрезано под нужное разрешение,
     * после закешировано, и после этого будет выдано url на изображение
     *
     * @param ThumbParams $thumbParams
     * @return string
     *
     * @throws \Exception
     */
    public function thumb(ThumbParams $thumbParams)
    {
        /** @var BaseFile $model */
        $model = $this->owner;

        if (!$model->isImage())
            return false;

        $ext = !empty($thumbParams->extension) ? $thumbParams->extension : $model->ori_extension;
        $thumbName = $this->generateFileCacheName(
            $thumbParams->width,
            $thumbParams->height,
            $ext
        );

        $thumbParams->savedPath = $this->getFileCachePath($model, $thumbName);
        $url = $this->getFileCacheUrl($model, $thumbName);

        if (file_exists($this->getFileCachePath($model, $thumbName))) {
            return $url;
        }

        $this->createThumb($model, $thumbParams);

        return $url;
    }

    /**
     * @param $width
     * @param $height
     * @param $ext
     * @return string
     */
    protected function generateFileCacheName($width, $height, $ext)
    {
        return $width . 'x' . $height . '.' . $ext;
    }

    /**
     * Вырезать путь к файлу
     *
     * in:
     *      /path_to_file/thumb/filename.png
     * out:
     *      /path_to_file/thumb/
     *
     * @param string $path
     * @param string $separator
     * @return string
     */
    protected function cutPath($path, $separator = '/')
    {
        return mb_substr($path, 0, mb_strrpos($path, $separator));
    }

    /**
     * Получить ссылку на кешированное изображение
     *
     * @param BaseFile $model
     * @param string $filename
     * @return string
     */
    protected function getFileCacheUrl($model, $filename)
    {
        $originalFilePath = $this->cutPath(\Yii::$app->{$this->storageComponent}->getFileUrl($model));
        $thumbPath = $originalFilePath . '/thumbs/' . $this->getFileName($model->sys_file) . '/';
        return $thumbPath . $filename;
    }

    /**
     * Получить название файла без расширения
     *
     * @param string $path
     * @return mixed
     */
    protected function getFileName($path)
    {
        $items = explode('.', basename($path));
        return array_shift($items);
    }

    /**
     * Получить путь к кешу
     *
     * @param BaseFile $model
     * @param $filename
     * @return string
     * @throws \yii\base\Exception
     */
    protected function getFileCachePath($model, $filename)
    {
        $prefix = '/thumbs/' . $this->getFileName($model->sys_file) . '/';
        $thumbPath = $this->cutPath($this->getFilePath($model)) . $prefix;

        if (!file_exists($thumbPath)) {
            FileHelper::createDirectory($thumbPath);
        }

        return $thumbPath . $filename;
    }

    /**
     * Получить путь к файлу по модели
     *
     * @param BaseFile $model
     * @return mixed
     */
    protected function getFilePath($model)
    {
        return \Yii::$app->{$this->storageComponent}->getFilePath($model);
    }

    /**
     * Создание thumbnail
     *
     * @param BaseFile $model модель файла
     * @param ThumbParams $thumbParams
     * @return bool
     */
    protected function createThumb($model, ThumbParams $thumbParams)
    {
        $path = $this->getFilePath($model);
        return $this->createImage($path, $thumbParams);
    }

    /**
     * Создание изображения
     *
     * @param string $path
     * @param ThumbParams $thumbParams
     * @return bool
     */
    protected function createImage($path, ThumbParams $thumbParams)
    {
        $imageManager = \Yii::$app->{$this->imageComponent};

        $imageManager->make($path);

        $imageManager->resize($thumbParams->width, $thumbParams->height);
        $imageManager->convert($thumbParams->extension);
        $imageManager->watermark($thumbParams->watermarkPath, $thumbParams->watermarkPosition);

        $imageManager->getImage()->save($thumbParams->savedPath, $thumbParams->quality);
        return true;
    }
}