<?php
/**
 * Файл класса FileService
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models\services;

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

    public function __construct(FileRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
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
     * @param $class
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

    public function update()
    {

    }

    public function delete()
    {

    }
}