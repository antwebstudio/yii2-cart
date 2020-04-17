<?php

namespace ant\cart\migrations\db;

use yii\db\Migration;
use ant\cart\models\Cart;

/**
 * Class M200417124127AlterCart
 */
class M200417124127AlterCart extends Migration
{
	protected $tableName = '{{%cart}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'net_total', $this->money(12, 2)->null()->defaultValue(null));
		
		$ids = [];
		$preIds = [];
		do {
			$carts = Cart::find()->where(['net_total' => null])->limit(100)->all();
			$ids = [];
			if (isset($carts)) {
				
				foreach($carts as $cart) {
					echo 'Updating: '.$cart->id."\n";
					
					$ids[] = $cart->id;
					$cart->updateAttributes(['net_total' => $cart->netTotal]);
				}
			}
			if (count($ids) && current($ids) == current($preIds)) {
				throw new \Exception('Failed to update');
			}
			$preIds = $ids;
		} while (isset($carts) && count($carts));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'net_total');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M200417124127AlterCart cannot be reverted.\n";

        return false;
    }
    */
}
