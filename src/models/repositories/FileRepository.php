<?php
/**
 * Файл класса FileRepository
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models\repositories;

use Exception;
use chulakov\filestorage\models\File;
use chulakov\filestorage\models\Image;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\exceptions\DBModelException;
use chulakov\filestorage\exceptions\NotFoundModelException;

/**
 * Репозиторий обработки моделей по работе с файлами
 *
 * @package chulakov\filestorage\models\repositories
 */
class FileRepository
{
    /**
     * Поиск в базе данных модели файла
     *
     * @param integer $id
     * @return File|\yii\db\ActiveRecord
     * @throws NotFoundModelException
     */
    public function getFile($id)
    {
        /** @var File $model */
        if ($model = File::find()->andWhere(['id' => $id])->limit(1)->one()) {
            return $model;
        }
        throw new NotFoundModelException("Не удалось найти файл: {$id}.");
    }

    /**
     * Поиск в базе данных модели изображения
     *
     * @param integer $id
     * @return File|\yii\db\ActiveRecord
     * @throws NotFoundModelException
     */
    public function getImage($id)
    {
        /** @var Image $model */
        if ($model = Image::find()->andWhere(['id' => $id])->limit(1)->one()) {
            if ($model->isImage()) {
                return $model;
            }
        }
        throw new NotFoundModelException("Не удалось найти изображение: {$id}.");
    }

    /**
     * Сохранение модели в базе данных
     *
     * @param BaseFile $file
     * @return bool
     * @throws DBModelException
     */
    public function save(BaseFile $file)
    {
        if (!$file->save()) {
            throw new DBModelException('Не удалось сохранить модель ' . get_class($file) . ' в базу данных.');
        }
        return true;
    }

    /**
     * Удаление модели в базе данных
     *
     * @param BaseFile $file
     * @return bool
     * @throws DBModelException
     */
    public function delete(BaseFile $file)
    {
        try {
            if (!$file->delete()) {
                throw new Exception('Не удалось удалить модель ' . get_class($file) . '::' . $file->id . ' из базы данных.');
            }
        } catch (Exception $e) {
            throw new DBModelException($e->getMessage(), 0, $e);
        } catch (\Throwable $t) {
            throw new DBModelException($t->getMessage(), 0, $t);
        }
        return true;
    }
}
