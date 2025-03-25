<?php
/**
 * Файл класса RemoteUploadTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\exceptions\NotUploadFileException;
use chulakov\filestorage\uploaders\RemoteUploadedFile;
use Yii;

/**
 * Class RemoteUploadedFileTest
 *
 * @package chulakov\filestorage\tests
 */
class RemoteUploadedFileTest extends TestCase
{
    /**
     * Статическая ссылка на изображение
     */
    protected static string $link = 'https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_150x54dp.png';

    /**
     * Получение instance файла
     */
    public function testGetInstance(): void
    {
        $image = new RemoteUploadedFile(self::$link);
        $this->assertInstanceOf(RemoteUploadedFile::class, $image);
    }

    /**
     * Обработка загруженного файла
     * @throws NotUploadFileException
     */
    public function testUploadFile(): void
    {
        $path = Yii::getAlias('@tests/runtime') . '/images/image.png';
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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }
}
