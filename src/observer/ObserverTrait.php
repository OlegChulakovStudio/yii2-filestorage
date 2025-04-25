<?php
/**
 * Файл трейта ObserverTrait
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\observer;

use Exception;
use yii\base\Event as BaseEvent;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\StringHelper;

/**
 * Trait ObserverTrait
 * @package chulakov\filestorage\observer
 */
trait ObserverTrait
{
    /**
     * Слушатели
     */
    public array $listeners = [];
    /**
     * Список событий
     */
    public array $events = [];
    /**
     * Обработчики, прикрепленные по непредсказуемому шаблону
     */
    protected array $eventWildcards = [];

    /**
     * Инициализация слушателей
     *
     * @throws InvalidConfigException
     */
    public function initListener(): void
    {
        foreach ($this->listeners as $listener) {
            /** @var ListenerInterface $handler */
            $handler = Instance::ensure($listener);
            $handler->attach($this);
        }
    }

    /**
     * Привязка обработчика
     */
    public function on(string $name, callable $handler, bool $append = true): void
    {
        if (str_contains($name, '*')) {
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
     */
    public function off(string $event, ?callable $handler = null): bool
    {
        if (empty($this->events[$event]) && empty($this->eventWildcards[$event])) {
            return false;
        }

        if ($handler === null) {
            unset($this->events[$event], $this->eventWildcards[$event]);
            return true;
        }

        if ($this->removeEvent($this->events, $event, $handler)) {
            return true;
        }

        if ($this->removeEvent($this->eventWildcards, $event, $handler)) {
            return true;
        }

        return false;
    }

    /**
     * Триггер выполнения
     */
    public function trigger(string $name, BaseEvent $event): void
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
     */
    public function beforeSave(string $savedPath, bool $deleteFile = true): bool
    {
        $event = $this->createEvent($savedPath, true, $deleteFile);
        $this->trigger(Event::SAVE_EVENT, $event);
        return $event->needSave;
    }

    /**
     * Событие удаления файлов
     */
    public function beforeDelete(string $filePath, ?Exception $exception = null): bool
    {
        $event = $this->createEvent($filePath, false, true, $exception);
        $this->trigger(Event::DELETE_EVENT, $event);
        return $event->needDelete;
    }

    /**
     * Создание события
     */
    public function createEvent(string $savePath, bool $needSave, bool $needDelete, ?Exception $exception = null): Event
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
     */
    protected function removeEvent(array &$events, string $name, callable $handler): bool
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
