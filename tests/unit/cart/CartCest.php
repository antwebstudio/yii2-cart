<?php
//namespace tests\codeception\common\cart;
//use Yii;
use yii\helpers\Html;
//use tests\codeception\common\UnitTester;
use ant\behaviors\AttachBehaviorBehavior;
use ant\user\models\User;
use ant\cart\components\CartableInterface;
use ant\cart\models\Cart;
use ant\cart\models\CartItem;

/**
 * @group cart
 */
class CartCest
{
    public function _before(UnitTester $I)
    {
		\Yii::configure(\Yii::$app, [
            'components' => [
				'cart' => [
					'class' => 'ant\cart\components\CartManager',
				],
            ],
        ]);
    }

    public function _after(UnitTester $I)
    {
    }
	
    public function _fixtures()
    {
        return [
            'user' => [
                'class' => \tests\fixtures\UserFixture::className(),
            ],
        ];
    }
	
	public function testAddItem(UnitTester $I)
    {
		\Yii::configure(\Yii::$app, [
			'components' => [
				'cart' => [
					'class' => 'ant\cart\components\CartManager',
					'types' => [
						'default' => [
							'item' => function($cartItem) {
								return new CartCestTestCartableItem;
							},
						],
					],
				],
				'discount' => null,
			],
		]);
		
		$quantity = 2;
		
		$item = new CartCestTestCartableItem;
		
		$cart = new Cart(['type' => 'default']);
		if (!$cart->save()) throw new \Exception(Html::errorSummary($cart));		
		
		$cartItem = $cart->addItem($item, $quantity);
		$I->assertTrue($cartItem instanceof CartItem);
		
		$cart = Cart::findOne($cart->id);
		
		$I->assertTrue(($cart instanceof Cart));
		$I->assertEquals(1, $cart->getCartItems()->count());
		$I->assertEquals($quantity, $cart->cartItems[0]->quantity);
		$I->assertEquals(10, $cart->cartItems[0]->unitPrice);
		$I->assertEquals($item->name, $cart->cartItems[0]->item->name);
    }
	
	public function testAddItemWithCustomData(UnitTester $I) {
		if (isset(\Yii::$app->discount)) {
			\Yii::configure(\Yii::$app->discount, [
				'overrideMethods' => [
					'getDiscountForForm' => function($formModel) {
						return \ant\discount\helpers\Discount::percent(0);
					},
					'getDiscountForCartItem' => function($cartItem) {
						return \ant\discount\helpers\Discount::percent(0);
					}
				],
			]);
		}

		\Yii::configure(\Yii::$app->cart, [
			'types' => [
				'product' => [
					'item' => function($cartItem) {
						return new CartCestTestCartableItemWithData;
					},
				],
			],
		]);
		
		$cart = \Yii::$app->cart->createCart('product');
		$cartable = new CartCestTestCartableItemWithData;
		$cart->addItem($cartable);
		
		$I->assertEquals([CartItem::DATA_CARTABLE => $cartable->getCartItemCustomData()], $cart->cartItems[0]->data);
	}
	
	public function testAddItemWithDiscount(UnitTester $I)
    {
		\Yii::configure(\Yii::$app, [
			'components' => [
				'cart' => [
					'class' => 'ant\cart\components\CartManager',
					'types' => [
						'default' => [
							'item' => function($cartItem) {
								return new CartCestTestCartableItem;
							},
						],
					],
				],
				'discount' => [
					'class' => 'ant\discount\components\DiscountManager',
					'overrideMethods' => [
						'getDiscountForCartItem' => function($cartItem) {
							// To test discount which depends on cart item's item
							return $cartItem->item->getDiscount();
						}
					],
				],
			],
		]);
		
		$quantity = 2;
		
		$item = new CartCestTestCartableItem;
		
		$cart = new Cart(['type' => 'default']);
		if (!$cart->save()) throw new \Exception(Html::errorSummary($cart));		
		
		$cartItem = $cart->addItem($item, $quantity);
		$I->assertTrue($cartItem instanceof CartItem);
		
		$cart = Cart::findOne($cart->id);
		
		$I->assertTrue(($cart instanceof Cart));
		$I->assertEquals(1, $cart->getCartItems()->count());
		$I->assertEquals($quantity, $cart->cartItems[0]->quantity);
		$I->assertEquals(10, $cart->cartItems[0]->unitPrice);
		$I->assertEquals(20, $cart->cartItems[0]->discount_value);
		$I->assertEquals(1, $cart->cartItems[0]->discount_type);
		$I->assertEquals($item->name, $cart->cartItems[0]->item->name);
    }
	
	public function testGetCartByToken(UnitTester $I) {
		\Yii::configure(\Yii::$app->cart, [
			'types' => [
				'event' => [
				],
			],
		]);
		
		$cart = \Yii::$app->cart->createCart('event');
		$cart->save();
		$cartId = $cart->id;
		
		$tokenQuery = $cart->tokenQueryParams;
		
		$decryptedCartId = $I->invokeMethod($cart, 'decryptId', [$tokenQuery['cart']]);
		$cart = \ant\cart\models\Cart::findOne($decryptedCartId);

		$I->assertTrue(isset($cart));
		$I->assertEquals($cartId, $cart->id);
	}
	
	public function testSetSelectedCartItemIds(UnitTester $I) {
		$cart = new Cart();
		if (!$cart->save()) throw new \Exception(Html::errorSummary($cart));
		
		$cartItem1 = new CartItem(['cart_id' => $cart->id, 'unique_hash_id' => uniqid()]);
		$cartItem1->attributes = [
			'unit_price' => 2,
			'quantity' => 1,
		];
		if (!$cartItem1->save()) throw new \Exception(Html::errorSummary($cartItem1));
		
		$cartItem2 = new CartItem(['cart_id' => $cart->id, 'unique_hash_id' => uniqid()]);
		$cartItem2->attributes = [
			'unit_price' => 3,
			'quantity' => 2,
		];
		if (!$cartItem2->save()) throw new \Exception(Html::errorSummary($cartItem2));
		
		$cart->setSelectedCartItemIds([$cartItem1->id]);
		
		$I->assertEquals(1, count($cart->selectedCartItems));
		$I->assertEquals(2, $cart->subtotal);
		
		$cart->setSelectedCartItemIds([$cartItem2->id]);
		
		$I->assertEquals(6, $cart->subtotal);
		$I->assertEquals(1, count($cart->selectedCartItems));
		
		$cart->setSelectedCartItemIds([]);
		
		$I->assertEquals(2, count($cart->selectedCartItems));
		$I->assertEquals(8, $cart->subtotal);
		
	}
	
	public function testSplitCart(UnitTester $I) {
		\Yii::configure(\Yii::$app, [
			'components' => [
				'cart' => [
					'class' => 'ant\cart\components\CartManager',
					'types' => [
						'default' => [
							'item' => function($cartItem) {
							},
						],
					],
				],
			],
		]);
		
		$cart = new Cart(['type' => 'default']);
		if (!$cart->save()) throw new \Exception(Html::errorSummary($cart));
		
		$cartItem1 = new CartItem(['cart_id' => $cart->id, 'unique_hash_id' => uniqid()]);
		$cartItem1->attributes = [
			'unit_price' => 2,
			'quantity' => 1,
		];
		if (!$cartItem1->save()) throw new \Exception(Html::errorSummary($cartItem1));
		
		$cartItem2 = new CartItem(['cart_id' => $cart->id, 'unique_hash_id' => uniqid()]);
		$cartItem2->attributes = [
			'unit_price' => 3,
			'quantity' => 2,
		];
		if (!$cartItem2->save()) throw new \Exception(Html::errorSummary($cartItem2));
		
		$I->assertEquals(2, count($cart->cartItems));
		
		$newCart = $cart->splitCart([$cartItem1->id]);
		
		$I->assertEquals(null, $cart->expireAt);
		$I->assertEquals(null, $newCart->expireAt);
		$I->assertEquals(1, count($cart->cartItems));
		$I->assertEquals(1, count($newCart->cartItems));
		$I->assertEquals($cartItem2->id, $cart->cartItems[0]->id);
		$I->assertEquals($cartItem1->id, $newCart->cartItems[0]->id);
	}
	
	public function testGetIsAbleToCheckout(UnitTester $I) {
		$cart = new Cart(['type' => 'default']);
        if (!$cart->save()) throw new \Exception(Html::errorSummary($cart));
		
		$I->assertTrue($cart->isAbleToCheckout);
		
		$cart->markAsExpired();
		
		$I->assertFalse($cart->isAbleToCheckout);
	}
	
	public function testGetIsAbleToQuotation(UnitTester $I) {
		$cart = new Cart(['type' => 'default']);
        if (!$cart->save()) throw new \Exception(Html::errorSummary($cart));
		
		$I->assertTrue($cart->isAbleToQuotation);
		
		$cart->markAsExpired();
		
		$I->assertFalse($cart->isAbleToQuotation);
	}
	
	public function testCartExpire(UnitTester $I) {
		$cart = new Cart(['type' => 'default']);
		if (!$cart->save()) throw new \Exception(print_r($cart->errors, 1));
		
		$cart->token->expire_at = null;
		if (!$cart->token->save()) throw new \Exception(print_r($cart->token->errors, 1));
		
        $I->assertEquals(null, $cart->token->expire_at);
        $I->assertFalse($cart->token->isExpired);
	}
	
	public function testSetQuantity(UnitTester $I) {
		\Yii::configure(\Yii::$app, [
			'components' => [
				'cart' => [
					'class' => 'ant\cart\components\CartManager',
					'types' => [
						'default' => [
							'item' => function($cartItem) {
								return new CartCestTestCartableItem;
							},
						],
					],
				],
			],
		]);
		
		$quantity = 5;
		
		$cart = new Cart(['type' => 'default']);
		if (!$cart->save()) throw new \Exception(print_r($cart->errors, 1));
		
		$cartItem1 = new CartItem(['cart_id' => $cart->id, 'unique_hash_id' => uniqid()]);
		$cartItem1->attributes = [
			'unit_price' => 2,
			'quantity' => 1,
		];
		if (!$cartItem1->save()) throw new \Exception(Html::errorSummary($cartItem1));
		
		$cart->setQuantity($cartItem1->id, $quantity);
		
		$cartItem = CartItem::findOne($cartItem1->id);
		
		$I->assertEquals($quantity, $cartItem->quantity);
	}
	
	public function testDuplicateWithCartItems(UnitTester $I) {
		$cart = new Cart(['type' => 'default']);
		if (!$cart->save()) throw new \Exception(print_r($cart->errors, 1));
		
		$duplicated = $cart->duplicateWithCartItems();
		
		$I->assertTrue(isset($duplicated));
		$I->assertNotEquals($cart->id, $duplicated->id);
	}
}

class CartCestTestCartableItemWithData extends \yii\base\Model implements CartableInterface {
	public function getCartItemCustomData() {
		return ['anyCustomData' => 'anyValue'];
	}
	
	public function setCartItemCustomData($data) {
		
	}
	
	public function getDiscount() {
		
	}
	
	public function getName() {
		return 'test item';
	}
	
	public function getPrice() {
		return 10;
	}
	
	public function getUniqueHashId() {
		return $this->getId();
	}
	
	public function getId() {
		return 1;
	}
}

class CartCestTestCartableItem extends \yii\base\Model implements \ant\cart\components\CartableInterface {
	public function getDiscount() {
		return new \ant\discount\helpers\Discount(20, \ant\discount\helpers\Discount::TYPE_PERCENT);
	}
	
	public function getName() {
		return 'test item';
	}
	
	public function getPrice() {
		return 10;
	}
	
	public function getUniqueHashId() {
		return $this->getId();
	}
	
	public function getId() {
		return 1;
	}
	
	public function setCartItemCustomData($data) {
		
	}
	
	public function getCartItemCustomData() {
		return ['attribute' => 'test'];
	}
}
