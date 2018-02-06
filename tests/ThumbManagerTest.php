<?php
/**
 * Файл класса ThumbManagerTest
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\image\Position;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\managers\ThumbsManager;
use chulakov\filestorage\uploaders\UploadedFile;
use yii\helpers\FileHelper;

/**
 * Class ThumbManagerTest
 * @package chulakov\filestorage\tests
 */
class ThumbManagerTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * Получить слушатели
     *
     * @return array
     */
    protected function getListener()
    {
        return ['listeners' =>
            [
                [
                    'class' => ThumbsManager::className(),
                    'encode' => 'jpg',
                    'quality' => 80,
                    'watermarkPath' => \Yii::getAlias('@tests/data') . '/images/watermark/watermark.png',
                    'watermarkPosition' => Position::CENTER,
                    'imageComponent' => 'imageComponent',
                ]
            ]
        ];
    }

    /**
     * Тестирование генерации thumbnail загружаемого изображения
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function testThumbGenerate()
    {
        // Путь к сохраняемому файлу
        $path = \Yii::getAlias('@tests/runtime') . '/images/image.jpg';
        /** @var UploadedFile $uploader */
        $uploader = UploadedFile::getInstance(new ImageModelTest(), 'imageManager');
        // Конфигурировать слушателей
        $uploader->configure($this->getListener());
        // Установить системное имя
        $uploader->setSysName('image');

        $this->assertFileNotExists($path);
        // Сохранить файл
        $uploader->saveAs($path, false);
        $this->assertInstanceOf(UploadedFile::className(), $uploader);

        list($basename) = explode('.', basename($path), 2);

        $thumbPath = implode(DIRECTORY_SEPARATOR, [
            dirname($path),
            'thumbs',
            $basename,
            'thumbs_192x144.jpg'
        ]);

        $this->assertFileExists($thumbPath);

        if (file_exists($thumbPath) && is_file($thumbPath)) {
            unlink($thumbPath);
        }
    }
}