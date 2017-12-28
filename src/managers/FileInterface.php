<?php
/**
 * Файл интерфейса FileInterface
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\managers;

/**
 * Interface FileInterface
 * @package chulakov\filestorage\managers
 */
interface FileInterface
{
    /**
     * Получить расширение файла
     *
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