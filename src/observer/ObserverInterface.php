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
     */
    public function trigger(string $name, BaseEvent $event): void;

    /**
     * Привязка обработчика
     */
    public function on(string $event, callable $handle, bool $append = true): void;

    /**
     * Открепление обработчика
     */
    public function off(string $event, ?callable $handler = null): bool;
}
