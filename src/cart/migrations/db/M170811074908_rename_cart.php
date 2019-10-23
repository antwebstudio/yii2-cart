<?php

namespace ant\cart\migrations\db;

use Yii;
use yii\db\Migration;

class M170811074908_rename_cart extends Migration
{
    public function safeUp()
    {
		if (Yii::$app->db->schema->getTableSchema('event_cart') === null) {
            $this->renameTable('{{%cart}}', '{{%event_cart}}');
        }
    }

    public function safeDown()
    {
        if (Yii::$app->db->schema->getTableSchema('cart') === null) {
            $this->renameTable('{{%event_cart}}', '{{%cart}}');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M170811074908_rename_cart cannot be reverted.\n";

        return false;
    }
    */
}
