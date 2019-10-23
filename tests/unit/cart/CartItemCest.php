<?php
//namespace tests\codeception\common\cart;
use yii\helpers\Html;
//use tests\codeception\common\UnitTester;
use ant\cart\models\Cart;
use ant\cart\models\CartItem;
use ant\discount\helpers\Discount;
use ant\cart\components\CartableInterface;

class CartItemCest
{
    public function _before(UnitTester $I)
    {
		\Yii::configure(\Yii::$app, [
            'components' => [
				'cart' => [
					'class' => 'ant\cart\components\CartManager',
					'types' => [
						'product' => [
							'item' => function() {
								return [];
							},
						],
					],
				],
			],
		]);
    }

    public function _after(UnitTester $I)
    {
    }
	
	public function testSetAttributes(UnitTester $I) {
		$data = [
			'name' => 'test cart item',
			'quantity' => 2,
			'currency' => 'MYR',
			'unit_price' => 2.00,
			'discount_value' => 20,
			'discount_type' => Discount::TYPE_AMOUNT,
			'remark' => 'test cart item remark',
		];
		$cartItem = new CartItem(['unique_hash_id' => uniqid()]);
		$cartItem->attributes = $data;
		
		$I->assertTrue($cartItem->validate());
		$I->assertEquals($data['name'], $cartItem->name);
		$I->assertEquals($data['quantity'], $cartItem->quantity);
		$I->assertEquals($data['currency'], $cartItem->currency);
		$I->assertEquals($data['unit_price'], $cartItem->unit_price);
		$I->assertEquals($data['discount_value'], $cartItem->discount_value);
		$I->assertEquals($data['discount_type'], $cartItem->discount_type);
		$I->assertEquals($data['remark'], $cartItem->remark);
		
		if (!$cartItem->save()) throw new \Exception(Html::errorSummary($cartItem));
		
		$cartItem = CartItem::findOne($cartItem->id);
		$I->assertEquals($data['name'], $cartItem->name);
		$I->assertEquals($data['quantity'], $cartItem->quantity);
		$I->assertEquals($data['currency'], $cartItem->currency);
		$I->assertEquals($data['unit_price'], $cartItem->unit_price);
		$I->assertEquals($data['discount_value'], $cartItem->discount_value);
		$I->assertEquals($data['discount_type'], $cartItem->discount_type);
		$I->assertEquals($data['remark'], $cartItem->remark);
		
	}

	// For request quotation, unit_price of CartItem is not required.
	public function testValidateOnScenarioRequestQuotation(UnitTester $I) {
		$cartItem = new CartItem(['scenario' => CartItem::SCENARIO_REQUEST_QUOTATION]);
		$cartItem->attributes = [
			'quantity' => 1,
		];

		$cartItem->validate();
		$I->assertTrue($cartItem->validate());
	}

    // tests
    public function testRefresh(UnitTester $I)
    {
		$cartItem = new CartItem;
		$cartItem->attributes = [
			'unit_price' => 10,
			'quantity' => 2,
		];
		$cartItem->unique_hash_id = uniqid();
		
		if (!$cartItem->save()) throw new \Exception(Html::errorSummary($cartItem));
		
		$exceptionThrown = false;
		
		try {
			$cartItem->refresh();
		} catch (\Exception $ex) {
			$exceptionThrown = true;
		}
		
		// Exception should be thrown because the item instance of cart item is null.
		$I->assertTrue($exceptionThrown);
	}
	
	// When add to cart
	// When unit price of item is null
	// For order type cart item, unit price can be zero, but cannot be null.
	// For quotation type cart item, unit price can be null.
	// Expected result: throw exception
	public function testRefreshUnitPriceWhenAddToCart(UnitTester $I) {
		\Yii::configure(\Yii::$app->cart, [
			'types' => [
				'product' => [
					'item' => function() {
						return new CartItemCestTestItemWithNullUnitPrice;
					},
				],
			],
		]);

		$cart= new Cart(['type' => 'product']);
		if (!$cart->save()) throw new \Exception(Html::errorSummary($cart));
		
		$cartItem = new CartItem(['scenario' => CartItem::SCENARIO_ADD_TO_CART, 'cart_id' => $cart->id]);
		$cartItem->attributes = [
			'unit_price' => 10,
			'quantity' => 2,
		];
		$cartItem->unique_hash_id = uniqid();
		
		if (!$cartItem->save()) throw new \Exception(Html::errorSummary($cartItem));

		$exceptionThrown = false;
		
		try {
			$I->invokeMethod($cartItem, 'refreshUnitPrice');
		} catch (\Exception $ex) {
			$exceptionThrown = true;
		}
		
		// Exception should be thrown because the unit price of the item instance of cart item is null.
		$I->assertTrue($exceptionThrown);
	}

	// When add to quotation
	// When unit price of item is null
	// Expected result: no exception is thrown
	public function testRefreshUnitPriceWhenAddToQuotation(UnitTester $I) {
		\Yii::configure(\Yii::$app->cart, [
			'types' => [
				'product' => [
					'item' => function() {
						return new CartItemCestTestItemWithNullUnitPrice;
					},
				],
			],
		]);

		$cart= new Cart(['type' => 'product']);
		if (!$cart->save()) throw new \Exception(Html::errorSummary($cart));
		
		$cartItem = new CartItem(['scenario' => CartItem::SCENARIO_ADD_TO_QUOTATION, 'cart_id' => $cart->id]);
		$cartItem->attributes = [
			'unit_price' => 10,
			'quantity' => 2,
		];
		$cartItem->unique_hash_id = uniqid();
		
		if (!$cartItem->save()) throw new \Exception(Html::errorSummary($cartItem));
		
		$exceptionThrown = false;
		
		try {
			$I->invokeMethod($cartItem, 'refreshUnitPrice');
		} catch (\Exception $ex) {
			$exceptionThrown = true;
		}
		
		$I->assertFalse($exceptionThrown);
	}
}

class CartItemCestTestItemWithNullUnitPrice implements CartableInterface {
	public function getPrice() {
		return null;
	}

	public function getDiscount() {

	}

	public function getName() {

	}

	public function getUniqueHashId() {

	}

	public function getCartItemCustomData() {

	}

	public function setCartItemCustomData($data) {

	}

	public function getId() {

	}
}