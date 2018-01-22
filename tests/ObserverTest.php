<?php
/**
 * Файл класса ObserverTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\observer\Event;
use chulakov\filestorage\uploaders\UploadedFile;

/**
 * Class ObserverTest
 * @package chulakov\filestorage\tests
 */
class ObserverTest extends TestCase
{
    /**
     * Подключение трейта с генерированием uploader и event
     */
    use UploaderMockTrait;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->generateFakeUploader();
    }

    /**
     * Тумблер для проверки срабатывания события
     *
     * @var bool
     */
    public $tumbler = false;

    /**
     * Handle для регестрирования события
     */
    public function handle()
    {
        // переключить тумблер, подтверждая этим срабатывание события
        $this->tumbler = true;
    }

    /**
     * Проверка работоспомобности Observer его функции trigger
     */
    public function testTrigger()
    {
        // установка начального положения тумблера
        $this->tumbler = false;
        /** @var UploadedFile $uploader */
        $uploader = $this->createFileUploader();
        // регистрирование события
        $uploader->on(Event::SAVE_EVENT, [$this, 'handle']);
        /** @var Event $event */
        $event = $uploader->createEvent('', true, false);
        // срабатывание слушателей
        $uploader->trigger(Event::SAVE_EVENT, $event);

        $this->assertTrue($this->tumbler);
    }
}