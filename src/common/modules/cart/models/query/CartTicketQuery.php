<?php
namespace ant\cart\models\query;

use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class CartTicketQuery extends ActiveQuery
{
    public function isLocked()
    {
        $this
            ->joinWith('cart c')
            ->joinWith('cart.token t')
            ->andWhere(['>=', 't.expire_at', time()])
        ;

        return $this;
    }
}
?>
