<?php
/**
 * Файл интерфейса FileInterface
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use Throwable;
use yii\base\Model;

/**
 * Interface UploadInterface
 * @package chulakov\filestorage\uploaders
 */
interface UploadInterface
{
    /**
     * Конфигурирование загрузчика
     */
    public function configure(array $config): void;

    /**
     * Инициализация одной модели
     *
     * @param Model $model
     * @param string $attribute
     * @return UploadInterface
     */
    public static function getInstance($model, $attribute);

    /**
     * Инициализация массива моделей
     *
     * @param Model $model
     * @param string $attribute
     * @return UploadInterface[]
     */
    public static function getInstances($model, $attribute);

    /**
     * Инициализация одной модели по имени атрибута
     *
     * @param string $name
     * @return UploadInterface
     */
    public static function getInstanceByName($name);

    /**
     * Инициализация массива моделей по имени атрибута
     *
     * @param string|array $name
     * @return UploadInterface[]
     */
    public static function getInstancesByName($name);

    /**
     * Сохранение файла по указанному пути
     *
     * @param string $file
     * @param bool $deleteTempFile
     */
    public function saveAs($file, $deleteTempFile = true): bool;

    /**
     * Путь до файла
     */
    public function getFile(): string;

    /**
     * Контент файла
     */
    public function getContent(): string;

    /**
     * Получение информации об оригинальном именовании файла
     *
     * @return string
     */
    public function getBaseName();

    /**
     * Получить оригинальное имя файла
     */
    public function getSysName(): string;

    /**
     * Установить системное имя
     */
    public function setSysName(string $sysName): void;

    /**
     * Получить имя файла с расширением
     */
    public function getName(): string;

    /**
     * Установка полного имени файла
     */
    public function setName(string $name): void;

    /**
     * Получение расширения файла
     *
     * @return string
     */
    public function getExtension();

    /**
     * Установить расширение файла
     */
    public function setExtension(string $extension): void;

    /**
     * Получение MIME типа файла
     */
    public function getType(): string;

    /**
     * Установить mime тип файла
     */
    public function setType(string $mime): void;

    /**
     * Получение размера файла
     */
    public function getSize(): int;

    /**
     * Установить размер файла
     */
    public function setSize(int $size): void;

    /**
     * Удаление зависимостей файла
     */
    public function deleteFile(string $filePath, ?Throwable $exception = null): bool;

    /**
     * Необходимость удаление временного файла
     */
    public function needDeleteTempFile(): bool;
}
