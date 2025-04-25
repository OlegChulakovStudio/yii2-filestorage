<?php
/**
 * Файл класса FileService
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\services;

use chulakov\filestorage\exceptions\DBModelException;
use chulakov\filestorage\exceptions\NotFoundModelException;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\models\File;
use chulakov\filestorage\models\Image;
use chulakov\filestorage\models\repositories\FileRepository;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\uploaders\UploadInterface;
use Throwable;
use yii\base\UnknownClassException;

/**
 * Class FileService
 * @package chulakov\filestorage\services
 */
class FileService
{
    /**
     * Конструктор с внедрением зависимости
     */
    public function __construct(
        protected FileRepository $repository,
    ) {}

    /**
     * Создание и заполнение модели файла
     * @throws UnknownClassException
     */
    public function createFile(UploadInterface $uploadedFile, UploadParams $params): BaseFile|null
    {
        return $this->createUpload(
            BaseFile::checkIsImage($uploadedFile->getType()) ? Image::class : File::class,
            $uploadedFile,
            $params,
        );
    }

    /**
     * Сохранение модели через репозиторий
     * @throws DBModelException
     */
    public function save(BaseFile $model, bool $throwException = true): bool
    {
        return $this->repository->save($model, $throwException);
    }

    /**
     * Получить изображение по его Id
     * @throws NotFoundModelException
     */
    public function getImage(int|string $id): Image
    {
        return $this->repository->getImage($id);
    }

    /**
     * Получить файл по его Id
     * @throws NotFoundModelException
     */
    public function getFile(int|string $id): File
    {
        return $this->repository->getFile($id);
    }

    /**
     * Удаление файла
     * @throws Throwable
     */
    public function delete(BaseFile $model, bool $throwException = true): bool
    {
        return $this->repository->delete($model, $throwException);
    }

    /**
     * Создание и заполнение файла с учетом его типа
     * @throws UnknownClassException
     */
    public function createUpload(string $class, UploadInterface $file, UploadParams $params): BaseFile
    {
        if (class_exists($class) === false) {
            throw new UnknownClassException('Класс для создания файла не найден.');
        }
        /** @var BaseFile $model */
        $model = new $class();

        $model->group_code = $params->group_code;
        if (empty($params->object_id) === false) {
            $model->object_id = $params->object_id;
        }
        if (empty($params->object_type) === false) {
            $model->object_type = $params->object_type;
        }
        $model->ori_extension = $file->getExtension();
        $model->ori_name = $file->getName();
        $model->mime = $file->getType();
        $model->size = $file->getSize();

        return $model;
    }
}
