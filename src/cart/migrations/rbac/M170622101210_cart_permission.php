<?php

namespace ant\cart\migrations\rbac;

use yii\db\Schema;
use ant\rbac\Migration;
use ant\rbac\Role;
use frontend\modules\cart\controllers\DefaultController;

class M170622101210_cart_permission extends Migration
{
	protected $permissions;
	
	public function init() {
		$this->permissions = [
			\backend\modules\cart\controllers\DefaultController::className() => [
				'index' => ['Cart index', [Role::ROLE_ADMIN]],
				'view' => ['View cart detail', [Role::ROLE_ADMIN]],
			],
			\frontend\modules\cart\controllers\CartController::className() => [
				'index' => ['Cart index', [Role::ROLE_GUEST]],
				'checkout' => ['Checkout cart', [Role::ROLE_GUEST]],
				'confirm' => ['Confirm to cart', [Role::ROLE_GUEST]],
				'renew-token' => ['Renew cart token', [Role::ROLE_GUEST]],
				'ajax-add-item' => ['Checkout cart', [Role::ROLE_GUEST]],
				'ajax-remove-item' => ['Checkout cart', [Role::ROLE_GUEST]],
				'ajax-get-cart' => ['Checkout cart', [Role::ROLE_GUEST]],
				'check-can-check-out' => ['Check can check out (ajax)', [Role::ROLE_GUEST]],
			],
			\frontend\modules\cart\controllers\CartItemController::className() => [
				'attachment' => ['Add attachment for cart item', [Role::ROLE_GUEST]],
			],

		];
		
		parent::init();
	}
	
	public function up()
    {
		$this->addAllPermissions($this->permissions);
    }

    public function down()
    {
		$this->removeAllPermissions($this->permissions);
    }
}
