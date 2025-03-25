<?php
/**
 * Файл класса ImageContainerTest
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\image\ImageContainer;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\params\ImageParams;
use chulakov\filestorage\params\ThumbParams;
use Yii;
use yii\di\Instance;

/**
 * Class ImageContainerTest
 * @package chulakov\filestorage\tests
 */
class ImageContainerTest extends TestCase
{
    /**
     * Класс ImageContainer с изображением
     */
    protected static ImageContainer $image;
    /**
     * Путь к оригинальному изображению
     */
    protected static string $imagePath;
    /**
     * Путь к сохраненному изображению
     */
    protected static string $imageSavePath;
    /**
     * Компонент для работы с изображениями
     */
    protected ImageComponent|string $imageComponent = ImageComponent::class;

    /**
     * Тест создания изображения
     */
    public function testCreateImage(): void
    {
        $this->assertInstanceOf(ImageContainer::class, self::$image);
    }

    /**
     * Получение свойств изображения
     */
    public function testImageProperty(): void
    {
        $image = self::$image;

        $this->assertInstanceOf(ImageContainer::class, $image);
        $this->assertEquals(128, $image->getWidth());
        $this->assertEquals(128, $image->getHeight());
        $this->assertEquals('png', $image->getExtension());
        $this->assertEquals('image/png', $image->getMimeType());
        $this->assertEquals(18748, $image->getFileSize());
    }

    /**
     * Тестирование генерации thumbnail
     */
    public function testImageThumb(): void
    {
        $params = new ThumbParams(40, 40);
        $this->callFuncImgContainer('thumb', self::$imageSavePath, $params);
        [$width, $height] = getimagesize(self::$imageSavePath);

        $this->assertFileExists(self::$imageSavePath);
        $this->assertEquals(40, $width);
        $this->assertEquals(40, $height);
        $this->deleteFile(self::$imageSavePath);
    }

    /**
     * Вызов функции контейнера
     *
     * @param string $func
     * @param string $path
     * @param ImageParams $imageParams
     * @return bool
     */
    protected function callFuncImgContainer(string $func, string $path, ImageParams $imageParams): bool
    {
        return self::$image->{$func}(
            $path,
            $imageParams
        );
    }

    /**
     * Удаление файла
     */
    protected function deleteFile(string $path): bool
    {
        return file_exists($path) && is_file($path) && unlink($path);
    }

    /**
     * Тестирование генерации cover
     */
    public function testImageCover(): void
    {
        $params = new ImageParams(50, 40);
        $params->coverPosition = Position::CENTER;

        $this->callFuncImgContainer('cover', self::$imageSavePath, $params);
        [$width, $height] = getimagesize(self::$imageSavePath);

        $this->assertFileExists(self::$imageSavePath);
        $this->assertEquals(50, $width);
        $this->assertEquals(40, $height);
        $this->deleteFile(self::$imageSavePath);
    }

    /**
     * Тестирование генерации contain
     */
    public function testImageContain(): void
    {
        $params = new ImageParams(50, 40);
        $params->coverPosition = Position::CENTER;

        $this->callFuncImgContainer('contain', self::$imageSavePath, $params);
        [$width, $height] = getimagesize(self::$imageSavePath);

        $this->assertFileExists(self::$imageSavePath);
        $this->assertEquals(50, $width);
        $this->assertEquals(40, $height);
        $this->deleteFile(self::$imageSavePath);
    }

    /**
     * Тестирование генерации widen
     */
    public function testImageWiden(): void
    {
        $params = new ImageParams(50, 0);
        $params->coverPosition = Position::CENTER;

        $this->callFuncImgContainer('widen', self::$imageSavePath, $params);
        [$width] = getimagesize(self::$imageSavePath);

        $this->assertFileExists(self::$imageSavePath);
        $this->assertEquals(50, $width);
        $this->deleteFile(self::$imageSavePath);
    }

    /**
     * Тестирование генерации heighten
     */
    public function testImageHeighten(): void
    {
        $params = new ImageParams(0, 50);
        $params->coverPosition = Position::CENTER;

        $this->callFuncImgContainer('heighten', self::$imageSavePath, $params);
        [$width, $height] = getimagesize(self::$imageSavePath);

        $this->assertFileExists(self::$imageSavePath);
        $this->assertEquals(50, $height);
        $this->deleteFile(self::$imageSavePath);
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();

        self::$imagePath = Yii::getAlias('@tests/data') . '/images/image.png';
        self::$imageSavePath = Yii::getAlias('@tests/runtime') . '/images/image.png';

        $this->imageComponent = Instance::ensure($this->imageComponent);
        if (empty(self::$image)) {
            self::$image = $this->createImage();
        }
    }

    /**
     * Создание изображения
     */
    protected function createImage(string $path = ''): ImageContainer
    {
        return $this->imageComponent->make(
            empty($path) ? Yii::getAlias('@tests/data') . '/images/image.png' : $path,
        );
    }
}
