<?php
namespace ant\cart\models\query;

use ant\helpers\DateTime;

class CartQuery extends \yii\db\ActiveQuery {
	public function active() {
		return $this->notExpired();
	}
	
	public function notExpired() {
		$now = new DateTime;
		return $this->joinWith('token token')->andWhere(['or', 
			['>', 'token.expire_at', $now->format(DateTime::FORMAT_MYSQL)],
			['token.expire_at' => null],
		]);
	}
}