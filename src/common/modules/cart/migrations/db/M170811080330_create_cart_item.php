<?php

namespace common\modules\cart\migrations\db;

use yii\db\Migration;

class M170811080330_create_cart_item extends Migration
{
    public function safeUp()
    {
		$tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cart_item}}', [
            'id' => $this->primaryKey()->unsigned(),
			'cart_id' => $this->integer(11)->unsigned(),
			'item_id' => $this->integer(11)->unsigned()->defaultValue(NULL),
			'unique_hash_id' => $this->string(32)->notNull(),
			'name' => $this->string(200),
			'url' => $this->string(255)->defaultValue(NULL),
			'quantity' => $this->integer(5)->unsigned()->notNull(),
			'currency' => $this->string(3)->defaultValue(NULL),
			'unit_price' => $this->money()->notNull(),
			'discount' => $this->double()->defaultValue(0),
			'discount_type' => $this->smallInteger(1)->defaultValue(0),
            'data' => $this->text()->defaultValue(NULL),
			'remark' => $this->text()->defaultValue(NULL),
			'is_locked' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultValue(NULL),
        ], $tableOptions);
		
        $this->addForeignKey('fk_cart_item_cart_id', '{{%cart_item}}', 'cart_id', '{{%cart}}', 'id', 'cascade', 'cascade');
    }

    public function safeDown()
    {
        $this->dropTable('{{%cart_item}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M170811080330_create_cart_item cannot be reverted.\n";

        return false;
    }
    */
}
