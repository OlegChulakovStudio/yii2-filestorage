<?php
/**
 * Файл класса UploadedFileTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\uploaders\UploadedFile;
use chulakov\filestorage\uploaders\UploadInterface;

/**
 * Class UploadedFileTest
 * @package chulakov\filestorage\tests
 */
class UploadedFileTest extends TestCase
{
    use UploaderMockTrait;

    /**
     * Тестирование изменения данных объекта
     */
    public function testMoveValue(): void
    {
        /** @var UploadInterface $uploader */
        $uploader = $this->createImageUploader();

        $this->assertInstanceOf(UploadedFile::class, $uploader);
        $this->assertEquals('filename.png', $uploader->getName());
        $this->assertEquals(12345, $uploader->getSize());
        $this->assertEquals('image/png', $uploader->getType());
        $this->assertEquals('png', $uploader->getExtension());
    }

    /**
     * Получение instance файла
     */
    public function testGetInstance(): void
    {
        /** @var UploadedFile $image */
        $image = UploadedFile::getInstance(new ImageModelTest(), 'imageUploader');
        $this->assertInstanceOf(UploadedFile::class, $image);
    }

    /**
     * Получение instance файлов
     */
    public function testGetInstances(): void
    {
        /** @var UploadedFile[] $files */
        $files = UploadedFile::getInstances(new FileModelTest(), 'files');

        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $this->assertInstanceOf(UploadedFile::class, $file);
        }
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
