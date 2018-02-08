<?php

use yii\db\Migration;

/**
 * Handles the creation of table `file`.
 */
class m170622_111130_create_file_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%file}}', [
            'id' => $this->primaryKey(),
            'group_code' => $this->string(16)->notNull()->comment('Для группировки однотипных файлов. Например, news, banners'),
            'object_id' => $this->string(11)->null()->comment('Уникальный идетификатор сущности для доп. группировки файлов. Например, у новости с object_id=5 может быть 10 изображений'),
            'ori_name' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'ori_extension' => $this->string(16)->notNull()->comment('Расширение файла'),
            'sys_file' => $this->string(255)->notNull()->unique()->comment('Системное имя файла'),
            'mime' => $this->string(255)->notNull()->comment('MIME-тип файла'),
            'size' => $this->integer()->notNull()->unsigned()->defaultValue(0)->comment('Размер файла в байтах'),
            'created_at' => $this->integer()->notNull()->comment('Дата сохранения файла'),
            'updated_at' => $this->integer()->notNull()->comment('Дата обновления информации о файле'),
        ], $tableOptions);

        $this->createIndex('idx_file_group_code', '{{%file}}', 'group_code');
        $this->createIndex('idx_file_object_id', '{{%file}}', 'object_id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%file}}');
    }
}
