<?php
/**
 * Файл класса PathServiceTest
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use yii\base\Exception;
use yii\helpers\FileHelper;
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\params\ThumbParams;
use chulakov\filestorage\params\ImageParams;
use chulakov\filestorage\services\PathService;
use chulakov\filestorage\exceptions\NotFoundFileException;

/**
 * Class PathServiceTest
 * @package chulakov\filestorage\tests
 */
class PathServiceTest extends TestCase
{
    /**
     * Сервис для работы с путями
     *
     * @var PathService
     */
    protected static $pathService;
    /**
     * Псевдопуть к файлу
     *
     * @var string
     */
    protected static $simplePath;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        self::$simplePath = '/path/to/file/image.png';

        if (empty(self::$pathService)) {
            self::$pathService = $this->createService();
        }
    }

    /**
     * Создание сервиса для работы с путями
     *
     * @return PathService
     */
    protected function createService()
    {
        return new PathService(\Yii::getAlias('@tests/runtime'), 'images', false);
    }

    /**
     * Тестирование парсера шаблонов
     */
    public function testParsePattern()
    {
        $pattern = '{relay}/{group}/{basename}/{type}_{width}x{height}.{ext}';
        $path = self::$pathService->parsePattern($pattern, [
            '{relay}' => 'upload',
            '{group}' => 'thumbs',
            '{basename}' => 'image',
            '{type}' => 'thumb',
            '{width}' => 640,
            '{height}' => 480,
            '{ext}' => 'png'
        ]);

        $this->assertEquals('upload/thumbs/image/thumb_640x480.png', $path);
    }

    /**
     * Чистка конфигураций
     */
    public function testClearConfig()
    {
        $config = [
            'width' => 100,
            'height' => 0,
            'ext' => '',
            'encode' => null
        ];

        $this->assertEquals(self::$pathService->filterConfig($config), [
            'width' => 100,
            'height' => 0
        ]);
    }

    /**
     * Тестирование проверки пути
     *
     * @throws NotFoundFileException
     * @throws Exception
     */
    public function testFindPath()
    {
        $path = \Yii::getAlias('@tests/data') . '/images/image.png';
        $movePath = \Yii::getAlias('@tests/runtime') . '/images/img/image.png';
        $moveDir = dirname($movePath);

        if (!is_dir(dirname($movePath))) {
            FileHelper::createDirectory(dirname($movePath));
        }

        copy($path, $movePath);

        $this->assertNotNull(self::$pathService->findPath('img/image.png', $moveDir));

        if (file_exists($movePath) && is_file($movePath)) {
            unlink($movePath);
        }
        if (is_dir($moveDir)) {
            rmdir($moveDir);
        }
    }

    /**
     * Тестирование работы с PathParams
     */
    public function testPathParams()
    {
        $pathParams = new PathParams();

        $config = [
            '{relay}' => '/path/to/file',
            '{name}' => 'image.png',
            '{basename}' => 'image',
            '{ext}' => 'png',
            '{group}' => 'cache'
        ];

        $this->assertEquals($config, self::$pathService->parseConfig(self::$simplePath, $pathParams));
    }

    /**
     * Тестирование работы с ImageParams
     */
    public function testImageParams()
    {
        $imageParams = new ImageParams(100, 100);

        $resultImage = [
            '{relay}' => '/path/to/file',
            '{name}' => 'image.png',
            '{basename}' => 'image',
            '{ext}' => 'png',
            '{group}' => 'images',
            '{width}' => 100,
            '{height}' => 100,
            '{type}' => 'images',
        ];

        $this->assertEquals($resultImage, self::$pathService->parseConfig(self::$simplePath, $imageParams));
    }

    /**
     * Тестирование работы с ThumbParams
     */
    public function testThumbParams()
    {
        $thumbParams = new ThumbParams(100, 90);

        $resultThumb = [
            '{relay}' => '/path/to/file',
            '{name}' => 'image.png',
            '{basename}' => 'image',
            '{ext}' => 'png',
            '{group}' => 'thumbs',
            '{width}' => 100,
            '{height}' => 90,
            '{type}' => 'thumbs',
        ];

        $this->assertEquals($resultThumb, self::$pathService->parseConfig(self::$simplePath, $thumbParams));
    }
}