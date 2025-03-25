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
     */
    public function attach(ObserverInterface $observer): void;
}
