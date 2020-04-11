<?php

namespace ant\cart\migrations\db;

use ant\db\Migration;

/**
 * Class M200410142203AlterCartItem
 */
class M200410142203AlterCartItem extends Migration
{
	protected $tableName = '{{%cart_item}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'additional_discount', $this->double()->defaultValue(0));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'additional_discount');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M200410142203AlterCartItem cannot be reverted.\n";

        return false;
    }
    */
}
