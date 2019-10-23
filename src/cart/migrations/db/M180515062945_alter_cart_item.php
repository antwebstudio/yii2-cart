<?php

namespace ant\cart\migrations\db;

use yii\db\Migration;

class M180515062945_alter_cart_item extends Migration
{
	protected $tableName = '{{%cart_item}}';
	
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'status', $this->smallInteger()->defaultValue(0));
		$this->addColumn($this->tableName, 'shipping_tracking_code', $this->string()->defaultValue(null));
    }

    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'status');
		$this->dropColumn($this->tableName, 'shipping_tracking_code');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180515062945_alter_cart_item cannot be reverted.\n";

        return false;
    }
    */
}
