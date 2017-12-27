<?php
/**
 * Файл класса FileRepository
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models\repositories;

use chulakov\filestorage\models\File;
use chulakov\filestorage\models\Image;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\exceptions\NotFoundModelException;

/**
 * Class FileRepository
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
     * @throws \Exception
     */
    public function save(BaseFile $file)
    {
        if (!$file->save()) {
            throw new \Exception('Модель ' . get_class($file) . ' не сохранена.');
        }
        return true;
    }
}
