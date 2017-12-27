<?php
/**
 * Файл трейта ObserverTrait
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use chulakov\filestorage\managers\ListenerInterface;
use yii\di\Instance;

/**
 * Trait ObserverTrait
 * @package chulakov\filestorage\uploaders
 */
trait ObserverTrait
{
    /**
     * Слушатели
     *
     * @var array
     */
    public $listeners;
    /**
     * Список событий
     *
     * @var array
     */
    public $events = [];

    /**
     * Конфигурирование загрузчика
     *
     * @param array $config
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function configure($config)
    {
        $this->listeners = $config;
        $this->initListener();
    }

    /**
     * Инициализация слушателей
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function initListener()
    {
        if (!empty($this->listeners['class'])) {
            $this->createListener($this->listeners);
        } else {
            foreach ($this->listeners as $listener) {
                $this->createListener($listener);
            }
        }
    }

    /**
     * Сохрание слушателя
     *
     * @param array $configure
     * @throws \yii\base\InvalidConfigException
     */
    public function createListener($configure)
    {
        /** @var ListenerInterface $handler */
        $handler = Instance::ensure($configure);
        $handler->attach($this);
    }

    /**
     * Навесить handler
     *
     * @param string $eventName
     * @param callable $handle
     * @return mixed
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