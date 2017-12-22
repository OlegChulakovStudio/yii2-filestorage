<?php
/**
 * Файл класса SaveInterface
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\savers;

/**
 * Interface SaveInterface
 * @package chulakov\filestorage\savers
 */
interface SaveInterface
{
    /**
     * Сохранение файла
     *
     * @param string $path
     * @param string $content
     * @param bool $deleteTempFile
     * @return mixed
     */
    public function save($path, $content, $deleteTempFile = true);

    /**
     * Проверка, выполнено ли было сохранение или нет
     *
     * @return mixed
     */
    public function isSaved();

    /**
     * Получить расширение файла
     * @return mixed
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