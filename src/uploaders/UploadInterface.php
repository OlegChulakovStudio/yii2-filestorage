<?php
/**
 * Файл класса FileInterface
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use yii\base\Model;

interface UploadInterface
{
    /**
     * Инициализация одной модели
     *
     * @param Model $model
     * @param string $attribute
     * @return mixed
     */
    public static function getInstance($model, $attribute);

    /**
     * Инициализация массива моделей
     *
     * @param Model $model
     * @param string $attribute
     * @return mixed
     */
    public static function getInstances($model, $attribute);

    /**
     * Инициализация одной модели по имени атрибута
     *
     * @param string $name
     * @return mixed
     */
    public static function getInstanceByName($name);

    /**
     * Инициализация массива моделей по имени атрибута
     *
     * @param string $name
     * @return mixed
     */
    public static function getInstancesByName($name);

    /**
     * Сохранение файла по указанному пути
     *
     * @param string $file
     * @param bool $deleteTempFile
     * @return mixed
     */
    public function saveAs($file, $deleteTempFile = true);

    /**
     * Получение информации об оригинальном именовании файла
     *
     * @return string
     */
    public function getBaseName();

    /**
     * Получение расширения файла
     *
     * @return string
     */
    public function getExtension();

    /**
     * Получение MIME типа файла
     *
     * @return string
     */
    public function getType();

    /**
     * Получение размера файла
     *
     * @return integer
     */
    public function getSize();
}
