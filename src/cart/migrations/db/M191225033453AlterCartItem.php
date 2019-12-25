<?php

namespace ant\cart\migrations\db;

use ant\db\Migration;

/**
 * Class M191225033453AlterCartItem
 */
class M191225033453AlterCartItem extends Migration
{
	protected $tableName = '{{%cart_item}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'item_class_id', $this->integer()->unsigned()->null()->defaultValue(null));
		$this->addForeignKeyTo('{{%model_class}}', 'item_class_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropForeignKeyTo('{{%model_class}', 'item_class_id');
		$this->dropColumn($this->tableName, 'item_class_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M191225033453AlterCartItem cannot be reverted.\n";

        return false;
    }
    */
}
