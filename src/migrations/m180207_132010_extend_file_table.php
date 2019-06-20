<?php

use yii\db\Migration;

class m180207_132010_extend_file_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%file}}', 'object_type', $this->string(16)->after('object_id')->comment('Дополнительная идентификация типа файла'));

        if ($this->db->getDriverName() == 'pgsql') {
            $this->execute('ALTER TABLE {{%file}} ALTER COLUMN "object_id" DROP DEFAULT');
            $this->alterColumn('{{%file}}', 'object_id', 'integer USING object_id::integer');
            $this->execute('ALTER TABLE {{%file}} ALTER COLUMN "object_id" SET DEFAULT NULL');
            $this->addCommentOnColumn('{{%file}}', 'object_id', 'Уникальный идетификатор сущности для доп. группировки файлов');
        } else {
            $this->alterColumn('{{%file}}', 'object_id', $this->integer()->null()->comment('Уникальный идетификатор сущности для доп. группировки файлов'));
        }
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
