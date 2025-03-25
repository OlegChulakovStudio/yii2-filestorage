<?php
/**
 * Файл класса ThumbManagerTest
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\image\Position;
use chulakov\filestorage\managers\ThumbsManager;
use chulakov\filestorage\uploaders\UploadedFile;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class ThumbManagerTest
 * @package chulakov\filestorage\tests
 */
class ThumbManagerTest extends TestCase
{
    /**
     * Тестирование генерации thumbnail загружаемого изображения
     *
     * @throws InvalidConfigException
     */
    public function testThumbGenerate(): void
    {
        // Путь к сохраняемому файлу
        $path = Yii::getAlias('@tests/runtime') . '/images/image.jpg';
        /** @var UploadedFile $uploader */
        $uploader = UploadedFile::getInstance(new ImageModelTest(), 'imageManager');
        // Конфигурировать слушателей
        $uploader->configure($this->getListener());
        // Установить системное имя
        $uploader->setSysName('image');

        $this->assertFileDoesNotExist($path);
        // Сохранить файл
        $uploader->saveAs($path, false);
        $this->assertInstanceOf(UploadedFile::class, $uploader);

        [$basename] = explode('.', basename($path), 2);

        $thumbPath = implode(DIRECTORY_SEPARATOR, [
            dirname($path),
            'thumbs',
            $basename,
            'thumbs_192x144.jpg',
        ]);

        $this->assertFileExists($thumbPath);

        if (file_exists($thumbPath) && is_file($thumbPath)) {
            unlink($thumbPath);
        }
    }

    /**
     * Получить слушатели
     */
    protected function getListener(): array
    {
        return [
            'listeners' => [
                [
                    'class' => ThumbsManager::class,
                    'encode' => 'jpg',
                    'quality' => 80,
                    'watermarkPath' => Yii::getAlias('@tests/data') . '/images/watermark/watermark.png',
                    'watermarkPosition' => Position::CENTER,
                    'imageComponent' => 'imageComponent',
                ],
            ],
        ];
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
