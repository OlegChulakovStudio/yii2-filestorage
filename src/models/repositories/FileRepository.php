<?php
/**
 * Файл класса FileRepository
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models\repositories;

use chulakov\filestorage\exceptions\DBModelException;
use chulakov\filestorage\exceptions\NotFoundModelException;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\models\File;
use chulakov\filestorage\models\Image;
use Throwable;
use Yii;

/**
 * Репозиторий обработки моделей по работе с файлами
 *
 * @package chulakov\filestorage\models\repositories
 */
class FileRepository
{
    /**
     * Поиск в базе данных модели файла
     * @throws NotFoundModelException
     */
    public function getFile(int|string $id): File
    {
        /** @var File $model */
        if ($model = File::find()->findById($id)->one()) {
            return $model;
        }
        throw new NotFoundModelException("Не удалось найти файл: {$id}.");
    }

    /**
     * Поиск в базе данных модели изображения
     * @throws NotFoundModelException
     */
    public function getImage(int|string $id): Image
    {
        /** @var Image $model */
        if ($model = Image::find()->findById($id)->one()) {
            if ($model->isImage()) {
                return $model;
            }
        }
        throw new NotFoundModelException("Не удалось найти изображение: {$id}.");
    }

    /**
     * Сохранение модели в базе данных
     * @throws DBModelException
     */
    public function save(BaseFile $file, bool $throwException = true): bool
    {
        try {
            if ($file->save() === false) {
                throw new DBModelException('Не удалось сохранить модель ' . get_class($file) . ' в базу данных.');
            }
            return true;
        } catch (Throwable $t) {
            Yii::error($t);
            if ($throwException) {
                throw new DBModelException($t->getMessage(), $t->getCode(), $t->getPrevious());
            }
            return false;
        }
    }

    /**
     * Удаление модели в базе данных
     * @throws DBModelException
     */
    public function delete(BaseFile $file, bool $throwException = true): bool
    {
        try {
            if ($file->delete() === false) {
                throw new NotFoundModelException(
                    sprintf('Не удалось удалить модель %s::%s из базы данных.', get_class($file), $file->id),
                );
            }
            return true;
        } catch (Throwable $t) {
            Yii::error($t);
            if ($throwException) {
                throw new DBModelException($t->getMessage(), 0, $t);
            }
            return false;
        }
    }
}
