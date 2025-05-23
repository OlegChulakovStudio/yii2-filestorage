<?php
/**
 * Файл класса FilesUploadBehavior
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\behaviors;

use chulakov\filestorage\uploaders\UploadInterface;

/**
 * Поведение для массовой загрузки файлов
 *
 * @package chulakov\filestorage\behaviors
 */
class FilesUploadBehavior extends FileUploadBehavior
{
    /**
     * @var bool
     */
    protected bool $isMultiple = true;

    /**
     * Проверка, инициализированы ли данные в массиве модели
     *
     * @param mixed $model
     * @return bool
     */
    protected function isInstances($model): bool
    {
        if (is_array($model)) {
            foreach ($model as $item) {
                if (parent::isInstances($item)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Инициализация массива данных для модели
     * @return UploadInterface[]
     */
    protected function getInstances()
    {
        $repository = $this->repository;
        $files = $repository::getInstances($this->owner, $this->attribute);
        if (empty($files)) {
            $files = $repository::getInstancesByName($this->attribute);
        }
        return $files;
    }

    /**
     * Конфигурация загруженных файлов
     *
     * @param UploadInterface[] $files
     */
    protected function configureInstances($files): void
    {
        if (empty($this->repositoryOptions) === false) {
            foreach ($files as $file) {
                $file->configure($this->repositoryOptions);
            }
        }
    }
}
