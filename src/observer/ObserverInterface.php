<?php
/**
 * Файл класса ObserverInterface
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

use yii\base\Event as BaseEvent;

/**
 * Interface ObserverInterface
 * @package chulakov\filestorage\observer
 */
interface ObserverInterface
{
    /**
     * Триггер событий
     *
     * @param string $name
     * @param BaseEvent $event
     * @return mixed
     */
    public function trigger($name, BaseEvent $event);

    /**
     * Привязка обработчика
     *
     * @param string $event
     * @param callable $handle
     * @param bool $append
     * @return mixed
     */
    public function on($event, $handle, $append = true);

    /**
     * Открепление обработчика
     *
     * @param string $event
     * @param callable $handler
     * @return mixed
     */
    public function off($event, $handler = null);
}
