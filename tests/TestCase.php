<?php
/**
 * Файл класса TestCase
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\FileStorage;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\storage\LocalStorage;
use chulakov\filestorage\storage\StorageInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Yii;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class TestCase
 * @package chulakov\filestorage\tests
 */
class TestCase extends BaseTestCase
{
    /**
     * Инициализация Yii приложения
     */
    protected function mockApplication(array $config = [], string $appClass = '\yii\console\Application'): void
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'container' => [
                'definitions' => [
                    StorageInterface::class => [
                        'class' => LocalStorage::class,
                        '__construct()' => [
                            'storagePath' => '@tests/runtime',
                            'storageBaseUrl' => false,
                            'storageDir' => 'images',
                        ]
                    ],
                ],
            ],
            'components' => [
                'fileStorage' => FileStorage::class,
                'imageComponent' => [
                    'class' => ImageComponent::class,
                    'driver' => ImageComponent::DRIVER_GD,
                ],
            ],
            'vendorPath' => $this->getVendorPath(),
        ], $config));
    }

    /**
     * Получить путь к папке vendor
     */
    protected function getVendorPath(): string
    {
        $vendor = dirname(__DIR__, 2) . '/vendor';
        if (is_dir($vendor) === false) {
            $vendor = dirname(__DIR__, 4);
        }
        return $vendor;
    }

    /**
     * Удаление сгенерированных директорий
     *
     * @throws ErrorException
     */
    protected function clearGenerateFile(): void
    {
        $dirs = glob(Yii::getAlias('@tests/runtime/images/*'), GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                FileHelper::removeDirectory($dir);
            }
        }
    }

    /**
     * Разрушить приложение
     */
    protected function destroyApplication(): void
    {
        Yii::$app = null;
    }
}
