<?php
/**
 * Файл трейта ObserverTrait
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

use yii\di\Instance;
use yii\base\Event as BaseEvent;
use yii\helpers\StringHelper;

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
     * Обработчики, прикрепенные по непредсказуемому шаблону
     *
     * @var array
     */
    protected $eventWildcards = [];

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
     * Привязка обработчика
     *
     * @param string $name
     * @param callable $handler
     * @param bool $append
     */
    public function on($name, $handler, $append = true)
    {
        if (strpos($name, '*') !== false) {
            if ($append || empty($this->eventWildcards[$name])) {
                $this->eventWildcards[$name][] = $handler;
            } else {
                array_unshift($this->eventWildcards[$name], $handler);
            }
            return;
        }

        if ($append || empty($this->events[$name])) {
            $this->events[$name][] = $handler;
        } else {
            array_unshift($this->events[$name], $handler);
        }
    }

    /**
     * Открепление обработчика
     *
     * @param string $name
     * @param callable $handler
     * @return bool
     */
    public function off($name, $handler = null)
    {
        if (empty($this->events[$name]) && empty($this->eventWildcards[$name])) {
            return false;
        }

        if ($handler === null) {
            unset($this->events[$name], $this->eventWildcards[$name]);
            return true;
        }

        if ($this->removeEvent($this->events, $name, $handler)) {
            return true;
        }

        if ($this->removeEvent($this->eventWildcards, $name, $handler)) {
            return true;
        }

        return false;
    }

    /**
     * Триггер выполнения
     *
     * @param string $name
     * @param BaseEvent $event
     */
    public function trigger($name, BaseEvent $event)
    {
        $eventHandlers = [];
        foreach ($this->eventWildcards as $wildcard => $handlers) {
            if (StringHelper::matchWildcard($wildcard, $name)) {
                $eventHandlers = array_merge($eventHandlers, $handlers);
            }
        }
        if (!empty($this->events[$name])) {
            $eventHandlers = array_merge($eventHandlers, $this->events[$name]);
        }

        if (!empty($eventHandlers)) {
            foreach ($eventHandlers as $handler) {
                call_user_func($handler, $event);
            }
        }

        Event::trigger($this, $name, $event);
    }

    /**
     * Псевдособытие сохранения
     *
     * @param $savedPath
     * @param bool $deleteFile
     * @return bool
     */
    public function beforeSave($savedPath, $deleteFile = true)
    {
        $event = $this->createEvent(
            $savedPath, true, $deleteFile
        );
        $this->trigger(Event::SAVE_EVENT, $event);
        return $event->needSave;
    }

    /**
     * Событие удаления файлов
     *
     * @param string $filePath
     * @param \Exception|null $exception
     * @return bool
     */
    public function beforeDelete($filePath, $exception = null)
    {
        $event = $this->createEvent(
            $filePath, false, true, $exception
        );
        $this->trigger(Event::DELETE_EVENT, $event);
        return $event->needDelete;
    }

    /**
     * Создание события
     *
     * @param string $savePath
     * @param boolean $needSave
     * @param boolean $needDelete
     * @param \Exception|null $exception
     * @return Event
     */
    public function createEvent($savePath, $needSave, $needDelete, $exception = null)
    {
        $event = new Event($this);
        $event->savedPath = $savePath;
        $event->needSave = $needSave;
        $event->needDelete = $needDelete;
        $event->exception = $exception;
        return $event;
    }

    /**
     * Удаление обработчика с события
     *
     * @param array $events
     * @param string $name
     * @param mixed $handler
     * @return bool
     */
    protected function removeEvent(&$events, $name, $handler)
    {
        if (isset($events[$name])) {
            foreach ($events[$name] as $i => $event) {
                if ($event === $handler) {
                    unset($events[$name][$i]);
                    $events[$name] = array_values($events[$name]);
                    if (empty($events[$name])) {
                        unset($events[$name]);
                    }
                    return true;
                }
            }
        }
        return false;
    }
}
