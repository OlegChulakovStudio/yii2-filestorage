<?php
/**
 * Файл bootstrap.php
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

// ensure we get report on all possible php errors
error_reporting(-1);
define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);
define('YII_ENV', 'test');
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('@chulakov/filestorage', dirname(__DIR__) . '/src');
Yii::setAlias('@chulakov/filestorage/tests', dirname(__DIR__) . '/tests');
Yii::setAlias('@tests/data', dirname(__DIR__) . '/tests/data');
Yii::setAlias('@tests/runtime', dirname(__DIR__) . '/tests/runtime');

require_once __DIR__ . '/loadfiles.php'; // загрука фейковых данных для тестирования
require_once __DIR__ . '/TestCase.php';