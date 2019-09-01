<?php

namespace common\modules\cart\migrations\db;

use yii\db\Migration;

class M180903044013_alter_cart_item extends Migration
{
    protected $tableName = '{{%cart_item}}';
	
    public function safeUp()
    {
		  $this->addColumn($this->tableName, 'seller_remark', $this->string()->defaultValue(null));
    }

    public function safeDown()
    {
		  $this->dropColumn($this->tableName, 'seller_remark');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180903044013_alter_cart_item cannot be reverted.\n";

        return false;
    }
    */
}
