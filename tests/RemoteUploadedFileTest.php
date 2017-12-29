<?php
/**
 * Файл класса RemoteUploadTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\uploaders\RemoteUploadedFile;

/**
 * Class RemoteUploadedFileTest
 * @package chulakov\filestorage\tests
 */
class RemoteUploadedFileTest extends TestCase
{
    /**
     * Модель изображения
     *
     * @var ImageModelTest
     */
    public static $imageModel;
    public static $imagePath;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->generateFakeFiles();
    }

    /**
     * Генерация фейковых данных
     */
    private function generateFakeFiles()
    {
        self::$imagePath = __DIR__ . '/data/images/test.png';

        $image = new ImageModelTest();
        $image->link = self::$imagePath;
        self::$imageModel = $image;
    }

    /**
     * Получение instance файла
     */
    public function testGetInstance()
    {
        /** @var RemoteUploadedFile $image */
        $image = RemoteUploadedFile::getInstance(self::$imageModel, 'link');
        $this->assertInstanceOf(RemoteUploadedFile::class, $image);
    }

    /**
     * Обработка загруженного файла∆
     */
    public function testUploadFile()
    {
        /** @var RemoteUploadedFile $image */
        $image = RemoteUploadedFile::getInstance(self::$imageModel, 'link');

        $image->setName('filename.png');
        $image->setSize('12345');
        $image->setType('image/png');
        $image->setExtension('png');

        $this->assertInstanceOf(RemoteUploadedFile::class, $image);
        $this->assertEquals('filename.png', $image->getName());
        $this->assertEquals('12345', $image->getSize());
        $this->assertEquals('image/png', $image->getType());
        $this->assertEquals('png', $image->getExtension());
    }
}