<?php
/**
 * Файл интерфейса ListenerInterface
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\managers;

use chulakov\filestorage\uploaders\ObserverInterface;

/**
 * Interface ListenerInterface
 * @package chulakov\filestorage\managers
 */
interface ListenerInterface
{
    /**
     * Присоединение к Observer
     *
     * @param ObserverInterface $observer
     * @return mixed
     */
    public function attach(ObserverInterface $observer);
}