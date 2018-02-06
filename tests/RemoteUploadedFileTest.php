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
     * Статическая ссылка на изображение
     *
     * @var string
     */
    protected static $link = 'https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_150x54dp.png';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * Получение instance файла
     */
    public function testGetInstance()
    {
        /** @var RemoteUploadedFile $image */
        $image = new RemoteUploadedFile(self::$link);
        $this->assertInstanceOf(RemoteUploadedFile::class, $image);
    }

    /**
     * Обработка загруженного файла
     * @throws \chulakov\filestorage\exceptions\NotUploadFileException
     */
    public function testUploadFile()
    {
        $path = \Yii::getAlias('@tests/runtime') . '/images/image.png';
        /** @var RemoteUploadedFile $image */
        $image = new RemoteUploadedFile(self::$link);
        $image->setSysName('image');
        $image->saveAs($path);

        $this->assertInstanceOf(RemoteUploadedFile::class, $image);
        $this->assertFileExists($path);
        $this->assertEquals('googlelogo_color_150x54dp.png', $image->getName());
        $this->assertEquals('3170', $image->getSize());
        $this->assertEquals('image/png', trim($image->getType()));
        $this->assertEquals('png', $image->getExtension());

        // Удаление изображения
        unlink($path);
    }
}