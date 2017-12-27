<?php
/**
 * Файл класса UploadObserver
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

/**
 * Interface UploadObserver
 * @package chulakov\filestorage\uploaders
 */
interface ObserverInterface
{
    /**
     * Триггер событий
     *
     * @param string $eventName
     * @param Event $event
     * @return mixed
     */
    public function trigger($eventName, Event $event);

    /**
     * Навесить handler
     *
     * @param string $event
     * @param callable $handle
     * @return mixed
     */
    public function on($event, $handle);
}