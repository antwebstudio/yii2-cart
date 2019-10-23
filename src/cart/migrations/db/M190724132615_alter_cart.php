<?php

namespace ant\cart\migrations\db;

use yii\db\Migration;

/**
 * Class M190724132615_alter_cart
 */
class M190724132615_alter_cart extends Migration
{
    protected $tableName = '{{%cart}}';
     
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'options', $this->text()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'options');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M190724132615_alter_cart cannot be reverted.\n";

        return false;
    }
    */
}
