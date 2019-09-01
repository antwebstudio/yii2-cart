<?php

namespace common\modules\cart\migrations\db;

use yii\db\Migration;

/**
 * Class M190317035813_alter_cart_item
 */
class M190317035813_alter_cart_item extends Migration
{
    protected $tableName = '{{%cart_item}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		  $this->addColumn($this->tableName, 'type', $this->smallInteger()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
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
        echo "M190317035813_alter_cart_item cannot be reverted.\n";

        return false;
    }
    */
}
