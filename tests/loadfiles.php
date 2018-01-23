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
    $path = \Yii::getAlias('@tests/data') . '/images/image.png';
    return [
        'name' => basename($path),
        'tmp_name' => $path,
        'type' => mime_content_type($path),
        'size' => filesize($path),
        'error' => 0,
    ];
}

/**
 * Генерация фейковых данных для тестирование множественной загрузки файлов
 *
 * @return array
 */
function uploadedFakeDataFiles()
{
    $path = \Yii::getAlias('@tests/data') . '/images/image.png';
    return [
        'name' => [
            basename($path),
            basename($path),
        ],
        'tmp_name' => [
            $path,
            $path,
        ],
        'type' => [
            mime_content_type($path),
            mime_content_type($path),
        ],
        'size' => [
            filesize($path),
            filesize($path),
        ],
        'error' => [
            0,
            0,
        ],
    ];
}

// Загрузка изображений
$_FILES['ImageModelTest[imageManager]'] = uploadedFakeDataFile(); // ImageManager
$_FILES['ImageModelTest[imageUploader]'] = uploadedFakeDataFile(); // UploadedFile
// Загрузка файлов
$_FILES['FileModelTest[files][]'] = uploadedFakeDataFiles();