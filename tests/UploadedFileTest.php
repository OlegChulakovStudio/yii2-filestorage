<?php
/**
 * Файл класса UploadedFileTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\uploaders\UploadedFile;

/**
 * Class UploadedFileTest
 * @package chulakov\filestorage\tests
 */
class UploadedFileTest extends TestCase
{
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
     * Генераци я фейковых данных
     *
     * @return array
     */
    protected function generateFakeFileData()
    {
        return [
            'name' => 'image.png',
            'tmp_name' => '/path_to_file_tmp/phpsecurityfile',
            'type' => 'image/png',
            'size' => 12345,
            'error' => 0,
        ];
    }

    /**
     * Установка фейковых файлов
     */
    protected function generateFakeFiles()
    {
        $_FILES['ImageModelTest[image]'] = $this->generateFakeFileData();
        $_FILES['FileModelTest[files][]'] = $this->generateFakeFileData();
    }

    /**
     * Получение instance файла
     */
    public function testGetInstance()
    {
        /** @var UploadedFile $image */
        $image = UploadedFile::getInstance(new ImageModelTest(), 'image');
        $this->assertInstanceOf(UploadedFile::className(), $image);
    }

    /**
     *Получение instance файлов
     */
    public function testGetInstances()
    {
        /** @var UploadedFile[] $files */
        $files = UploadedFile::getInstances(new FileModelTest(), 'files');

        foreach ($files as $file) {
            $this->assertInstanceOf(UploadedFile::className(), $file);
        }
    }

    /**
     * Обработка файла
     */
    public function testUpload()
    {
        /** @var UploadedFile $image */
        $image = UploadedFile::getInstance(new ImageModelTest(), 'image');

        $this->assertInstanceOf(UploadedFile::className(), $image);
        $this->assertEquals('image.png', $image->getName());
        $this->assertEquals('image/png', $image->getType());
        $this->assertEquals(12345, $image->getSize());
        $this->assertEquals('png', $image->getExtension());
    }
}