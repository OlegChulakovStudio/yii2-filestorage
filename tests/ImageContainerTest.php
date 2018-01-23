<?php
/**
 * Файл класса ImageContainerTest
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use yii\di\Instance;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\params\ImageParams;
use chulakov\filestorage\params\ThumbParams;
use chulakov\filestorage\image\ImageContainer;

/**
 * Class ImageContainerTest
 * @package chulakov\filestorage\tests
 */
class ImageContainerTest extends TestCase
{
    /**
     * Компонент для работы с изображениями
     *
     * @var ImageComponent
     */
    protected $imageComponent = ImageComponent::class;
    /**
     * Класс ImageContainer с изображением
     *
     * @var ImageContainer
     */
    protected static $image;
    /**
     * Путь к оригинальному изображению
     *
     * @var string
     */
    protected static $imagePath;
    /**
     * Путь к сохраненному изображению
     *
     * @var string
     */
    protected static $imageSavePath;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();

        self::$imagePath = \Yii::getAlias('@tests/data') . '/images/image.png';
        self::$imageSavePath = \Yii::getAlias('@tests/runtime') . '/images/image.png';

        $this->imageComponent = Instance::ensure($this->imageComponent);
        if (empty(self::$image)) {
            self::$image = $this->createImage();
        }
    }

    /**
     * Тест создания изображения
     */
    public function testCreateImage()
    {
        $this->assertInstanceOf(ImageContainer::class, self::$image);
    }

    /**
     * Получение свойств изображения
     */
    public function testImageProperty()
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
    public function testImageThumb()
    {
        $params = new ThumbParams(40, 40);
        $this->callFuncImgContainer('thumb', self::$imageSavePath, $params);
        list($width, $height) = getimagesize(self::$imageSavePath);

        $this->assertFileExists(self::$imageSavePath);
        $this->assertEquals(40, $width);
        $this->assertEquals(40, $height);
        $this->deleteFile(self::$imageSavePath);
    }

    /**
     * Тестирование генерации cover
     */
    public function testImageCover()
    {
        $params = new ImageParams(50, 40);
        $params->coverPosition = Position::CENTER;

        $this->callFuncImgContainer('cover', self::$imageSavePath, $params);
        list($width, $height) = getimagesize(self::$imageSavePath);

        $this->assertFileExists(self::$imageSavePath);
        $this->assertEquals(50, $width);
        $this->assertEquals(40, $height);
        $this->deleteFile(self::$imageSavePath);
    }

    /**
     * Тестирование генерации contain
     */
    public function testImageContain()
    {
        $params = new ImageParams(50, 40);
        $params->coverPosition = Position::CENTER;

        $this->callFuncImgContainer('contain', self::$imageSavePath, $params);
        list($width, $height) = getimagesize(self::$imageSavePath);

        $this->assertFileExists(self::$imageSavePath);
        $this->assertEquals(50, $width);
        $this->assertEquals(40, $height);
        $this->deleteFile(self::$imageSavePath);
    }

    /**
     * Тестирование генерации widen
     */
    public function testImageWiden()
    {
        $params = new ImageParams(50, 0);
        $params->coverPosition = Position::CENTER;

        $this->callFuncImgContainer('widen', self::$imageSavePath, $params);
        list($width) = getimagesize(self::$imageSavePath);

        $this->assertFileExists(self::$imageSavePath);
        $this->assertEquals(50, $width);
        $this->deleteFile(self::$imageSavePath);
    }

    /**
     * Тестирование генерации heighten
     */
    public function testImageHeighten()
    {
        $params = new ImageParams(0, 50);
        $params->coverPosition = Position::CENTER;

        $this->callFuncImgContainer('heighten', self::$imageSavePath, $params);
        list($w, $height) = getimagesize(self::$imageSavePath);

        $this->assertFileExists(self::$imageSavePath);
        $this->assertEquals(50, $height);
        $this->deleteFile(self::$imageSavePath);
    }

    /**
     * Создание изображения
     *
     * @param string $path
     * @return ImageContainer
     */
    protected function createImage($path = '')
    {
        return $this->imageComponent->make(
            empty($path) ? \Yii::getAlias('@tests/data') . '/images/image.png' : $path
        );
    }

    /**
     * Удаление файла
     *
     * @param string $path
     * @return bool
     */
    protected function deleteFile($path)
    {
        return file_exists($path) && is_file($path) ? unlink($path) : false;
    }

    /**
     * Вызов функциий контейнера
     *
     * @param string $func
     * @param string $path
     * @param ImageParams $imageParams
     * @return bool
     */
    protected function callFuncImgContainer($func, $path, ImageParams $imageParams)
    {
        return self::$image->{$func}(
            $path,
            $imageParams
        );
    }
}