<?php

namespace ant\cart\migrations\db;

use yii\db\Migration;

/**
 * Class M190415065802_alter_cart
 */
class M190415065802_alter_cart extends Migration
{
	protected $tableName = '{{%cart}}';
	
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'data', $this->text()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'data');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M190415065802_alter_cart cannot be reverted.\n";

        return false;
    }
    */
}
