<?php

namespace common\modules\cart\migrations\db;

use yii\db\Migration;

class M180824080554_alter_cart extends Migration
{
	protected $tableName = '{{%cart}}';
	
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'remark', $this->text()->null()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'remark');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180824080554_alter_cart cannot be reverted.\n";

        return false;
    }
    */
}
