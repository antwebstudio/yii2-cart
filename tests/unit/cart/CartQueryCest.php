<?php
namespace cart;

use \UnitTester;
use common\modules\cart\models\Cart;

class CartQueryCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testActive(UnitTester $I)
    {
		$cart = new Cart(['type' => 'default']);
        if (!$cart->save()) throw new \Exception(Html::errorSummary($cart));
		
		$cart = Cart::find()->alias('cart')->andWhere(['cart.id' => $cart->id])->active()->one();
		
		$I->assertTrue(isset($cart));
		
		$cart->markAsExpired();
		
		$cart = Cart::find()->alias('cart')->andWhere(['cart.id' => $cart->id])->active()->one();
		
		$I->assertFalse(isset($cart));
		
    }
}
