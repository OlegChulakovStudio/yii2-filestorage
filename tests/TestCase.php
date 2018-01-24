<?php
/**
 * Файл класса TestCase
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\ImageComponent;
use yii\helpers\FileHelper;
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
                ],
                'imageComponent' => [
                    'class' => ImageComponent::className(),
                    'driver' => ImageComponent::DRIVER_GD
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

    /**
     * Удаление сгенерированных директорий
     *
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\ErrorException
     */
    protected function clearGenerateFile()
    {
        $dirs = glob(\Yii::getAlias('@tests/runtime/images/*'), GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                FileHelper::removeDirectory($dir);
            }
        }
    }

    /**
     * Разрушить приложение
     */
    protected function destroyApplication()
    {
        \Yii::$app = null;
    }
}