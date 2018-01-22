<?php
/**
 * Файл класса TestCase
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use yii\helpers\ArrayHelper;
use chulakov\filestorage\FileStorage;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Class TestCase
 * @package chulakov\filestorage\tests
 */
class TestCase extends BaseTestCase
{
    /**
     * Инициализация Yii приложения
     *
     * @param array $config
     * @param string $appClass
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'components' => [
                'fileStorage' => [
                    'class' => FileStorage::className(),
                    'storageBaseUrl' => false,
                    'storagePath' => '@tests/runtime',
                    'storageDir' => 'images',
                ]
            ],
            'vendorPath' => $this->getVendorPath(),
        ], $config));
    }

    /**
     * Получить путь к папке vendor
     *
     * @return string
     */
    protected function getVendorPath()
    {
        $vendor = dirname(dirname(__DIR__)) . '/vendor';
        if (!is_dir($vendor)) {
            $vendor = dirname(dirname(dirname(dirname(__DIR__))));
        }
        return $vendor;
    }
}