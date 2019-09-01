<?php

namespace common\modules\cart\migrations\db;

use yii\db\Migration;

class M180321072944_alter_cart_item extends Migration
{
	protected $tableName = '{{%cart_item}}';
	
    public function safeUp()
    {

		$this->renameColumn($this->tableName, 'discount', 'discount_value');
    }

    public function safeDown()
    {
		$this->renameColumn($this->tableName, 'discount_value', 'discount');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180321072944_alter_cart_item cannot be reverted.\n";

        return false;
    }
    */
}
