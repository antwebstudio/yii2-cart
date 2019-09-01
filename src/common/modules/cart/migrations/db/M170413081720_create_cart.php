<?php

namespace common\modules\cart\migrations\db;

use yii\db\Migration;

class M170413081720_create_cart extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cart}}', [
            'id' => $this->primaryKey()->unsigned(),
            'event_id' => $this->integer(11)->unsigned(),
            'created_ip' => $this->string(40)->defaultValue(NULL),
            'created_by' => $this->integer(11)->unsigned(),
            'created_at' => $this->timestamp()->defaultValue(NULL),
        ], $tableOptions);

        //$this->addForeignKey('fk_cart_event_id', '{{%cart}}', 'event_id', '{{%event}}', 'id', 'cascade', null);
        $this->addForeignKey('fk_cart_created_by', '{{%cart}}', 'created_by', '{{%user}}', 'id', 'cascade', null);
    }

    public function down()
    {
        $this->dropForeignKey('fk_cart_created_by', '{{%cart}}');
        //$this->dropForeignKey('fk_cart_event_id', '{{%cart}}');
        $this->dropTable('{{%cart}}');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
