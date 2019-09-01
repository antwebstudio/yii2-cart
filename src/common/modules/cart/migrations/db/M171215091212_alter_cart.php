<?php

namespace common\modules\cart\migrations\db;

use yii\db\Migration;

class M171215091212_alter_cart extends Migration
{
	protected $tableName = '{{%cart}}';
	
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'updated_at', $this->timestamp()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'updated_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171215091212_alter_cart cannot be reverted.\n";

        return false;
    }
    */
}
