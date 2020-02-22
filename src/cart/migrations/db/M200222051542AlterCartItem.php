<?php

namespace ant\cart\migrations\db;

use yii\db\Migration;

/**
 * Class M200222051542AlterCartItem
 */
class M200222051542AlterCartItem extends Migration
{
	protected $tableName = '{{%cart_item}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->renameColumn($this->tableName, 'url', 'item_url');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->renameColumn($this->tableName, 'item_url', 'url');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M200222051542AlterCartItem cannot be reverted.\n";

        return false;
    }
    */
}
