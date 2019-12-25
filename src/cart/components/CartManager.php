<?php
namespace ant\cart\components;
use Yii;
use ant\cart\models\Cart;

class CartManager extends \yii\base\Component {
	public $types;
	public $defaultType = 'default';
	public $lifetime = 10 * 60;
	
	public function createCart($type = null) {
		if (!isset($type)) $type = $this->defaultType;
		
		if (isset($this->types[$type]) || $type == $this->defaultType) {
			if (!isset($this->types[$type])) throw new \Exception('Cart of type "'.$type.'" is not configured. ');
			
			$config = $this->types[$type];
			if (!isset($config['class'])) $config['class'] = Cart::className();
			$cart = Yii::createObject($config);
			$cart->type = $type;
			if (!$cart->save()) throw new \Exception('Failed to save cart. '.\yii\helpers\Html::errorSummary($cart));
			
			if (isset($this->lifetime)) {
				$cart->token->duration = $this->lifetime;
			} else {
				$cart->token->expire_at = null;
			}
			if (!$cart->token->save()) throw new \Exception(print_r($cart->token->errors, 1));
			
			$this->storeToSession($type, $cart);
			return $cart;
		}
		throw new \Exception('Failed to create cart instance, probably the cart type: '.$type.' is not configured. ');
	}
	
	public function getCartById($cartId) {
		if ($cartId && $cart = Cart::findOne($cartId)) {
			//Yii::configure($cart, $this->types[$cart->type]);
			return $cart;
		}
	}

	public function getLastCart($type = null) {
		if (!isset($type)) $type = $this->defaultType;
		
		$cartId = Yii::$app->session->get('_cart_'.$type);
		
		if ($cartId && $cart = Cart::findOne($cartId)) {
			if (isset($cart) && !$cart->isExpired) {
				//Yii::configure($cart, $this->types[$type]);
				return $cart;
			} else {
				return $this->createCart($type);
			}
		} else {
			return $this->createCart($type);
		}
	}

	public function getCartAbsorbedServiceCharges($cart) {
		$type = $cart->type;
		
		if (isset($this->types[$type]['cartAbsorbedServiceCharges']) && is_callable($this->types[$type]['cartAbsorbedServiceCharges'])) {
			return call_user_func_array($this->types[$type]['cartAbsorbedServiceCharges'], [$cart]);
		}
		return 0;
	}
	
	public function getCartServiceCharges($cart) {
		$type = $cart->type;
		
		if (isset($this->types[$type]['cartServiceCharges']) && is_callable($this->types[$type]['cartServiceCharges'])) {
			return call_user_func_array($this->types[$type]['cartServiceCharges'], [$cart]);
		}
		return 0;
	}

	public function getItemByCartItem($cartItem) {
		if (isset($cartItem->cart)) {
			
			$cart = $cartItem->cart;
		} else if (!isset($cartItem->cart) && $cartItem->cart_id) {
			$cart = $cartItem->getCart()->one();
		}

		if (isset($cart)) {
			$type = $cart->type;
			
			if (isset($this->types[$type]['item']) && is_callable($this->types[$type]['item'])) {
				return call_user_func_array($this->types[$type]['item'], [$cartItem]);
			}
		}

		return \ant\models\ModelClass::getModel($cartItem->item_class_id, $cartItem->item_id);
	}
	
	public function getItemClass($type) {
		return $this->types[$type]['itemClass'];
	}
	
	/*
	 * @return type $type
	 */
	/*public function createCartItem($type = null, $attributes = []) {
		if (!isset($type)) $type = $this->defaultType;
		
		$itemClass = $this->types[$type]['itemClass'];
		$attributes['class'] = $itemClass;
		
		$item = Yii::createObject($attributes);
		
		return $item;
	}*/
	
	protected function storeToSession($type, $cart) {
		Yii::$app->session->set('_cart_'.$type, $cart->id);
	}
}