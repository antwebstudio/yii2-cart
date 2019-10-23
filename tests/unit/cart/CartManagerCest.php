<?php
namespace cart;

use \UnitTester;
use ant\cart\models\Cart;

class CartManagerCest
{
    public function _before(UnitTester $I)
    {
		\Yii::configure(\Yii::$app, [
            'components' => [
				'cart' => [
					'class' => 'ant\cart\components\CartManager',
					'types' => [
						'event' => [
						],
					],
				],
            ],
        ]);
    }

    public function _after(UnitTester $I)
    {
    }
	
	public function testCreateCart(UnitTester $I) {
		$cartType = 'event';
		
		$cart = \Yii::$app->cart->createCart($cartType);
		if (!$cart->save()) throw new \Exception(print_r($cart->errors, 1));
		
		$cart = Cart::findOne($cart->id);
		
		$I->assertTrue(isset($cart));
		$I->assertFalse($cart->isExpired);
		$I->assertTrue(isset($cart->token->expire_at));
	}
	
	public function testCreateCartNeverExpire(UnitTester $I) {
		$cartType = 'event';
		
		\Yii::$app->cart->lifetime = null;
		
		$cart = \Yii::$app->cart->createCart($cartType);
		if (!$cart->save()) throw new \Exception(print_r($cart->errors, 1));
		
		$cart = Cart::findOne($cart->id);
		
		$I->assertTrue(isset($cart));
		$I->assertFalse($cart->isExpired);
		$I->assertFalse(isset($cart->token->expire_at));
	}

    // tests
	public function testGetLastCart(UnitTester $I) {
		$cartType = 'event';
		
		$cart = \Yii::$app->cart->createCart($cartType);
		$cart->save();
		$cartId = $cart->id;
		
		$cart = Cart::findOne($cart->id);
		$I->assertFalse($cart->isExpired);
		
		$cart = \Yii::$app->cart->getLastCart($cartType);

		$I->assertTrue(isset($cart));
		$I->assertFalse($cart->isExpired);
		$I->assertTrue(isset($cart->token->expire_at));
		$I->assertEquals($cartId, $cart->id);
	}

    // Last cart is mark expired, so when call getLastCart again, it should get the new cart instead of the expired cart.
	public function testGetLastCartShouldExcludeExpiredCart(UnitTester $I) {
		$cartType = 'event';
		
		$cart = \Yii::$app->cart->getLastCart($cartType);
		$cartId = $cart->id;
		
		$cart = Cart::findOne($cart->id);
		$cart->markAsExpired();
		
		$cart = \Yii::$app->cart->getLastCart($cartType);

		$I->assertTrue(isset($cart));
		$I->assertFalse($cart->isExpired);
		$I->assertNotEquals($cartId, $cart->id);
	}
}
