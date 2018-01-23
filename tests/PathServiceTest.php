<?php
/**
 * Файл класса PathServiceTest
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\exceptions\NotFoundFileException;
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\services\PathService;
use yii\base\Exception;
use yii\helpers\FileHelper;

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
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
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
     * Тестирование формирования параметров для парсинга из полного пути файла
     */
    public function testParseConfig()
    {
        $pathParams = new PathParams();
        $this->assertEquals([
            '{relay}' => '/path/to/file',
            '{name}' => 'file.txt',
            '{basename}' => 'file',
            '{ext}' => 'txt',
            '{group}' => 'cache'
        ],
            self::$pathService->parseConfig('/path/to/file/file.txt', $pathParams)
        );
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
}