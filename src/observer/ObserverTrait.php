<?php
/**
 * Файл трейта ObserverTrait
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

use yii\di\Instance;

/**
 * Trait ObserverTrait
 * @package chulakov\filestorage\observer
 */
trait ObserverTrait
{
    /**
     * Слушатели
     *
     * @var array
     */
    public $listeners = [];
    /**
     * Список событий
     *
     * @var array
     */
    public $events = [];

    /**
     * Инициализация слушателей
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function initListener()
    {
        foreach ($this->listeners as $listener) {
            /** @var ListenerInterface $handler */
            $handler = Instance::ensure($listener);
            $handler->attach($this);
        }
    }

    /**
     * Навесить handler
     *
     * @param string $eventName
     * @param callable $handle
     */
    public function on($eventName, $handle)
    {
        $this->events[$eventName][] = $handle;
    }

    /**
     * Триггер выполнения
     *
     * @param string $eventName
     * @param Event $event
     * @return bool
     */
    public function trigger($eventName, Event $event)
    {
        if (empty($this->events[$eventName])) {
            return false;
        }
        foreach ($this->events[$eventName] as $handle) {
            call_user_func($handle, $event);
        }
        return true;
    }
}