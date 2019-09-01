<?php

namespace ant\cart\migrations\db;

use common\components\Migration;

/**
 * Class M190724112250_create_cart_option
 */
class M190724112250_create_cart_option extends Migration
{
    protected $tableName = '{{%cart_option}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
			'title' => $this->string()->notNull(),
            'price_adjust' => $this->money()->null()->defaultValue(null),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->null()->defaultValue(null),
            'updated_at' => $this->timestamp()->null()->defaultValue(null),
        ], $this->getTableOptions());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M190724112250_create_cart_option cannot be reverted.\n";

        return false;
    }
    */
}
