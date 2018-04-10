<?php
/**
 * Файл класса MailManager
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\managers;

use Yii;
use yii\base\BaseObject;
use chulakov\filestorage\observer\Event;
use chulakov\filestorage\uploaders\UploadInterface;
use chulakov\filestorage\observer\ListenerInterface;
use chulakov\filestorage\observer\ObserverInterface;

/**
 * Класс(пример) майл-менеджера для оповещения пользователя о новом файле
 *
 * @package backend\managers
 */
class MailManager extends BaseObject implements ListenerInterface
{
    /**
     * Обработка файла
     *
     * @param Event $event
     */
    public function handle(Event $event)
    {
        if ($event->sender instanceof UploadInterface) {
            Yii::$app->mailer->compose()
                ->setFrom('example@domain.com')
                ->setTo('example@domain.com')
                ->setSubject('Отправка файла')
                ->setTextBody('Файл пришел! Путь к файлу: ' . $event->savedPath)
                ->send();
        }
    }

    /**
     * Присоединение к Observer
     *
     * @param ObserverInterface $observer
     */
    public function attach(ObserverInterface $observer)
    {
        $observer->on(Event::SAVE_EVENT, [$this, 'handle']);
    }
}