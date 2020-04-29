<?php

namespace ant\cart\migrations\db;

use ant\db\Migration;
use ant\cart\models\CartItem;
use ant\discount\helpers\Discount;

/**
 * Class M200510030830AlterCartItems
 */
class M200510030830AlterCartItem extends Migration
{
	protected $tableName = '{{%cart_item}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'discount_amount', $this->decimal(19, 4)->notNull()->defaultValue(0));
		$this->addColumn($this->tableName, 'discount_percent', $this->decimal(19, 4)->notNull()->defaultValue(0));
		
		CartItem::updateAll(['discount_type' => Discount::TYPE_PERCENT], 'discount_percent = discount_value');
		CartItem::updateAll(['discount_type' => Discount::TYPE_AMOUNT], 'discount_amount = discount_value');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'discount_amount');
		$this->dropColumn($this->tableName, 'discount_percent');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M200510030830AlterCartItems cannot be reverted.\n";

        return false;
    }
    */
}
