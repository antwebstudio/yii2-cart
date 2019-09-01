<?php
namespace common\modules\cart\models\query;

use common\helpers\DateTime;

class CartQuery extends \yii\db\ActiveQuery {
	public function active() {
		return $this->notExpired();
	}
	
	public function notExpired() {
		$now = new DateTime;
		return $this->joinWith('token token')->andWhere(['>', 'token.expire_at', $now->format(DateTime::FORMAT_MYSQL)]);
	}
}