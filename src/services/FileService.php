<?php
/**
 * Файл класса FileService
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\services;

use chulakov\filestorage\models\Image;
use chulakov\filestorage\models\File;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\models\repositories\FileRepository;
use chulakov\filestorage\uploaders\UploadInterface;
use chulakov\filestorage\UploadParams;
use yii\base\UnknownClassException;

class FileService
{
    /**
     * @var FileRepository
     */
    protected $repository;

    /**
     * Конструктор с внедрением зависимости
     *
     * @param FileRepository $repository
     */
    public function __construct(FileRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Создание и заполнение модели файла
     *
     * @param UploadInterface $uploadedFile
     * @param UploadParams $params
     * @return File|null
     * @throws UnknownClassException
     */
    public function createFile(UploadInterface $uploadedFile, UploadParams $params)
    {
        return $this->createUpload(File::class, $uploadedFile, $params);
    }

    /**
     * Создание и заполнение модели изображения
     *
     * @param UploadInterface $uploadedFile
     * @param UploadParams $params
     * @return Image|null
     * @throws UnknownClassException
     */
    public function createImage(UploadInterface $uploadedFile, UploadParams $params)
    {
        return $this->createUpload(Image::class, $uploadedFile, $params);
    }

    /**
     * Сохранение модели через репохиторий
     *
     * @param BaseFile $model
     * @return bool
     * @throws \Exception
     */
    public function save($model)
    {
        return $this->repository->save($model);
    }

    /**
     * Создание и заполнение файла с учетом его типа
     *
     * @param string $class
     * @param UploadInterface $file
     * @param UploadParams $params
     * @return mixed
     * @throws UnknownClassException
     */
    protected function createUpload($class, UploadInterface $file, UploadParams $params)
    {
        if (!class_exists($class)) {
            throw new UnknownClassException('Класс для создания файла не найден.');
        }
        /** @var BaseFile $model */
        $model = new $class();

        $model->group_code = $params->group_code;
        $model->object_id = $params->object_id;
        $model->ori_extension = $file->getExtension();
        $model->ori_name = $file->getBaseName();
        $model->mime = $file->getType();
        $model->size = $file->getSize();

        return $model;
    }
}