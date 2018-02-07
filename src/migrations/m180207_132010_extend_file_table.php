<?php

namespace chulakov\filestorage\migrations;

use yii\db\Migration;

class m180207_132010_extend_file_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%file}}', 'object_type', $this->string(16)->after('object_id')->comment('Дополнительная идентификация типа файла'));
        $this->alterColumn('{{%file}}', 'object_id', $this->integer()->null()->comment('Уникальный идетификатор сущности для доп. группировки файлов'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%file}}', 'object_type');
        $this->alterColumn('{{%file}}', 'object_id', $this->string(11)->null()->comment('Уникальный идетификатор сущности для доп. группировки файлов. Например, у новости с object_id=5 может быть 10 изображений'));
    }
}
