<?php
use ant\cart\models\Cart;
use ant\cart\models\CartForm;
use ant\cart\models\CartItem;

/**
 * @group cart
 */
class CartFormCest
{public function _before(UnitTester $I)
    {
		\Yii::configure(\Yii::$app, [
			'components' => [
				'cart' => [
					'class' => 'ant\cart\components\CartManager',
					'types' => [
						'default' => [
						],
					],
				],
			],
		]);
    }

    public function _after(UnitTester $I)
    {
    }
	
	public function _fixtures() {
		return [
			'user' => 'tests\fixtures\UserFixture',
		];
	}
	
	public function test(UnitTester $I) {
        $cartForm = new CartForm(['cart' => \Yii::$app->cart->createCart()]);
		if (!$cartForm->save()) throw new \Exception(print_r($cartForm, 1));
		
		$I->assertTrue(strlen($cartForm->cart->tokenQueryParams['tokenkey']) > 10);
	}
}