<?php
/**
 * Файл интерфейса FileInterface
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use yii\base\Model;

/**
 * Interface UploadInterface
 * @package chulakov\filestorage\uploaders
 */
interface UploadInterface
{
    /**
     * Конфигурирование загрузчика
     *
     * @param array $config
     * @return mixed
     */
    public function configure($config);

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
     *  Путь до файла
     *
     * @return string
     */
    public function getFile();

    /**
     * Контент файла
     *
     * @return string
     */
    public function getContent();

    /**
     * Получение информации об оригинальном именовании файла
     *
     * @return string
     */
    public function getBaseName();

    /**
     * Получение имени файла после сохранения
     *
     * @return string
     */
    public function getSavedName();

    /**
     * Установка полного имени файла
     *
     * @param string $name
     */
    public function setName($name);

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
     * Установить mime тип файла
     *
     * @param string $mime
     */
    public function setType($mime);

    /**
     * Получение размера файла
     *
     * @return integer
     */
    public function getSize();

    /**
     * Установить размер файла
     *
     * @param integer $size
     */
    public function setSize($size);
}
