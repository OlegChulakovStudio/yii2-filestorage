<?php
/**
 * Файл класса миграции для создания пользователя и прикрепления к нему изображения
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\controllers\Migration;

/**
 * Handles the creation of table `user`.
 */
class m180326_150928_create_user_table extends Migration
{
    /**
     * Путь к изображению
     *
     * @var string
     */
    public $imageDataPath;

    /**
     * m180326_130730_create_example_table constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->imageDataPath = __DIR__ . '/data/images/achievements.png';
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $names = ['Vladimir', 'Alex', 'Oleg'];

        // Создание параметрического класса и его настройка
        $params = new UploadParams('user');
        $params->object_type = 'achievement';

        foreach ($names as $name) {
            $this->insert('{{%user}}', [
                'name' => $name,
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            $params->object_id = Yii::$app->db->getLastInsertID();
            // Загрузка изображения
            $this->upload($this->imageDataPath, $params);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
