<?php

namespace common\modules\cart\migrations\db;

use yii\db\Migration;

class M170427061832_create_cart_token_map extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cart_token_map}}', [
            'cart_id' => $this->integer()->unsigned(),
            'token_id' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->addForeignKey('fk_cart_token_map_cart_id', '{{%cart_token_map}}', 'cart_id', '{{%cart}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_cart_token_map_token_id', '{{%cart_token_map}}', 'token_id', '{{%token}}', 'id', 'cascade', 'cascade');
    }

    public function down()
    {
        $this->dropForeignKey('fk_cart_token_map_token_id', '{{%cart_token_map}}');
        $this->dropForeignKey('fk_cart_token_map_cart_id', '{{%cart_token_map}}');
        $this->dropTable('{{%cart_token_map}}');
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
