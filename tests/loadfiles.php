<?php
/**
 * Файл loadfiles.php
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

/**
 * Генерация фейковых данных для тестирование обычной загрузки
 *
 * @return array
 */
function uploadedFakeDataFile()
{
    return [
        'name' => 'image.png',
        'tmp_name' => '/path_to_file_tmp/phpsecurityfile',
        'type' => 'image/png',
        'size' => 12345,
        'error' => 0,
    ];
}

$_FILES['ImageModelTest[image]'] = uploadedFakeDataFile();
$_FILES['FileModelTest[files][]'] = uploadedFakeDataFile();

/**
 * Генерация фейковых данных для тестирования менеджера изображений
 *
 * @return array
 */
function uploadedFakeFileForImageManager()
{
    $path = \Yii::getAlias('@tests/data') . '/images/image.png';
    return [
        'name' => basename($path),
        'tmp_name' => $path,
        'type' => mime_content_type($path),
        'size' => filesize($path),
        'error' => 0,
    ];
}

$_FILES['ImageModelTest[imageManager]'] = uploadedFakeFileForImageManager();