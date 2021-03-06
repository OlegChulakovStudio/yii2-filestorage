<?php
/**
 * Файл интерфейса ListenerInterface
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

/**
 * Interface ListenerInterface
 * @package chulakov\filestorage\observer
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