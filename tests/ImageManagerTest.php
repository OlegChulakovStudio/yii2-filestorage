<?php
/**
 * Файл класса ImageManagerTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\managers\ImageManager;
use chulakov\filestorage\uploaders\UploadedFile;

/**
 * Class ImageManagerTest
 * @package chulakov\filestorage\tests
 */
class ImageManagerTest extends TestCase
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
        $path = \Yii::getAlias('@tests/data') . '/images/image.png';
        return [
            'name' => basename($path),
            'tmp_name' => $path,
            'type' => mime_content_type($path),
            'size' => filesize($path),
            'error' => 0,
        ];
    }

    /**
     * Установка фейковых файлов
     */
    protected function generateFakeFiles()
    {
        $_FILES['ImageModelTest[image]'] = $this->generateFakeFileData();
    }

    /**
     * Инициализация ImageManager
     */
    public function testInitManager()
    {
        $imageManager = new ImageManager();
        $this->assertInstanceOf(ImageManager::className(), $imageManager);
    }

    /**
     * Получить список слушателей
     *
     * @return array
     */
    protected function getListener()
    {
        return ['listeners' =>
            [
                [
                    'class' => ImageManager::className(),
                    'width' => 640,
                    'height' => 480,
                    'encode' => 'jpg',
                    'quality' => 100, // в процентах
                    'watermarkPath' => \Yii::getAlias('@tests/data') . '/images/watermark/watermark.png',
                    'watermarkPosition' => ImageComponent::POSITION_CENTER,
                    'imageClass' => ImageComponent::className()
                ]
            ]
        ];
    }

    /**
     * Эмулирование работы ImageManager
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function testImageManage()
    {
        // путь к сохраняемому файлу
        $path = \Yii::getAlias('@tests/runtime') . '/images/image.jpg';
        /** @var UploadedFile $uploader */
        $uploader = UploadedFile::getInstance(new ImageModelTest(), 'image');

        // конфигурирование слушателей
        $uploader->configure($this->getListener());

        // установить системное имя
        $uploader->setSysName('image');

        // сохранение файла
        $uploader->saveAs($path, false);

        if (!file_exists($path)) {
            $this->throwException(new \Exception('Файл не был загружен.'));
        }

        $this->assertInstanceOf(UploadedFile::className(), $uploader);
        $this->assertEquals(filesize($path), $uploader->getSize());
        $this->assertEquals(basename($path), $uploader->getName());
        $this->assertEquals(mime_content_type($path), $uploader->getType());

        // удаление сгенерированного файла
        unlink($path);
    }
}