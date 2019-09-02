<?php namespace cart;

use UnitTester;
use common\modules\cart\models\Cart;
use ant\cart\models\CartOption;

class CartOptionCest
{
    public function _before(UnitTester $I)
    {
		\Yii::configure(\Yii::$app, [
            'components' => [
				'cart' => [
					'class' => 'common\modules\cart\components\CartManager',
				],
            ],
        ]);
    }

    // tests
    public function test(UnitTester $I)
    {
        $price = 20;

        $cartOption = new CartOption;
		$cartOption->title = 'test cart option';
        $cartOption->price_adjust = $price;
        if (!$cartOption->save()) throw new \Exception(print_r($cartOption->errors, 1));

        $cart = new Cart;
        if (!$cart->save()) throw new \Exception(print_r($cart->errors, 1));

        $I->assertEquals(0, $cart->netTotal);

        $cart->attributes = [
            'options' => [$cartOption->id],
        ];
		
		$I->assertEquals(1, count($cart->options));
		$I->assertEquals(1, count($I->invokeMethod($cart, 'getCartOptions')));
        $I->assertEquals($price, $cart->netTotal);
        
    }
	
	/*public function testValidateItemTotal(UnitTester $I) {
		
        $price = 20;

        $cartOption = new CartOption;
        $cartOption->price_adjust = $price;
        if (!$cartOption->save()) throw new \Exception(print_r($cartOption->errors, 1));

        $cart = new Cart;
        if (!$cart->save()) throw new \Exception(print_r($cart->errors, 1));

        $I->assertTrue($I->invokeMethod($cart, 'validateItemTotal'));

        $cart->attributes = [
            'options' => [$cartOption->id],
        ];

        $I->assertFalse($I->invokeMethod($cart, 'validateItemTotal'));
	}*/
}
