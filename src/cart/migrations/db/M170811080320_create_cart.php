<?php

namespace ant\cart\migrations\db;

use yii\db\Migration;

class M170811080320_create_cart extends Migration
{
    public function safeUp()
    {
		$tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cart}}', [
            'id' => $this->primaryKey()->unsigned(),
			'token_id' => $this->integer(11)->unsigned()->defaultValue(NULL),
            'type' => $this->string(50)->defaultValue(NULL),
			'status' => $this->integer(3)->defaultValue(0),
            'created_ip' => $this->string(40)->defaultValue(NULL),
            'created_by' => $this->integer(11)->unsigned(),
            'created_at' => $this->timestamp()->defaultValue(NULL),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%cart}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M170811080320_create_cart cannot be reverted.\n";

        return false;
    }
    */
}
