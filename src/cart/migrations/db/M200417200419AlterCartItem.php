<?php

namespace ant\cart\migrations\db;

use ant\db\Migration;

/**
 * Class M200417200419AlterCartItem
 */
class M200417200419AlterCartItem extends Migration
{
	protected $tableName = '{{%cart_item}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'parent_id', $this->foreignId());
		$this->addForeignKeyTo($this->tableName, 'parent_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'parent_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M200417200419AlterCartItem cannot be reverted.\n";

        return false;
    }
    */
}
