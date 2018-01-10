<?php
/**
 * Файл класса FileService
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\services;

use yii\base\UnknownClassException;
use chulakov\filestorage\models\File;
use chulakov\filestorage\models\Image;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\models\repositories\FileRepository;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\uploaders\UploadInterface;

/**
 * Class FileService
 * @package chulakov\filestorage\services
 */
class FileService
{
    /**
     * Загрузчик
     *
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
     * Получить изображение по его Id
     *
     * @param $id
     * @return File|\yii\db\ActiveRecord
     * @throws \chulakov\filestorage\exceptions\NotFoundModelException
     */
    public function getImage($id)
    {
        return $this->repository->getImage($id);
    }

    /**
     * Получить файл по его Id
     *
     * @param $id
     * @return File|\yii\db\ActiveRecord
     * @throws \chulakov\filestorage\exceptions\NotFoundModelException
     */
    public function getFile($id)
    {
        return $this->repository->getFile($id);
    }

    /**
     * Удаление файла
     *
     * @param BaseFile $model
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($model)
    {
        return $this->repository->delete($model);
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
        $model->ori_name = $file->getName();
        $model->mime = $file->getType();
        $model->size = $file->getSize();

        return $model;
    }
}