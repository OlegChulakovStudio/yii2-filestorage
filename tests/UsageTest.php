<?php
/**
 * Файл класса UsageTest
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\models\File;
use chulakov\filestorage\models\BaseFile;

/**
 * Class UsageTest
 * @package chulakov\filestorage\tests
 */
class UsageTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite:@tests/data/database/test.db',
                ],
            ]
        ]);
    }

    /**
     * Подготовка файлов
     */
    protected function generateImage()
    {
        $filePath = \Yii::getAlias('@tests/data') . '/images/image.png';
        $copyPath = dirname($filePath) . '/tmp_image.png';
        copy($filePath, $copyPath);
    }

    /**
     * Загрузка файлов
     *
     * @return BaseFile[]
     */
    protected function loadFiles()
    {
        $form = new FileFormTest();
        $form->load($_POST, '');
        $form->validate();
        return $form->upload();
    }

    /**
     * Интеграционное тестирование загрузки файла
     */
    public function testUploadTest()
    {
        $this->generateImage();
        $files = $this->loadFiles();

        $this->assertNotEmpty($files);

        if (!is_array($files)) {
            $files = [$files];
        }

        // Проверка файлов
        /** @var BaseFile $file */
        foreach ($files as $file) {
            $this->assertFileExists($file->getPath());
            if ($file->isImage()) {
                list($basename) = explode('.', basename($file->sys_file), 2);
                $path = \Yii::getAlias('@tests/runtime/images/photos/0/thumbs/') . $basename . '/thumbs_192x144.jpg';
                $this->assertFileExists($path);
            }
        }
    }

    /**
     * @inheritdoc
     *
     * @throws \yii\db\Exception
     * @throws \yii\base\ErrorException
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->clearGenerateFile();
        \Yii::$app->db->createCommand()
            ->truncateTable(File::tableName())
            ->execute();
        $this->destroyApplication();
    }
}