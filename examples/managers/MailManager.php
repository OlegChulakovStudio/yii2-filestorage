<?php
/**
 * Файл класса MailManager
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\managers;

use chulakov\filestorage\observer\Event;
use chulakov\filestorage\observer\ListenerInterface;
use chulakov\filestorage\observer\ObserverInterface;
use chulakov\filestorage\uploaders\UploadInterface;
use Yii;
use yii\base\BaseObject;

/**
 * Класс(пример) майл-менеджера для оповещения пользователя о новом файле
 *
 * @package backend\managers
 */
class MailManager extends BaseObject implements ListenerInterface
{
    /**
     * Обработка файла
     */
    public function handle(Event $event): void
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
     */
    public function attach(ObserverInterface $observer): void
    {
        $observer->on(Event::SAVE_EVENT, [$this, 'handle']);
    }
}
