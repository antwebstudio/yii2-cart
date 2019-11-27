<?php

namespace ant\cart\models;

use Yii;
use \yii\helpers\Html;
use yii\helpers\ArrayHelper;
use ant\helpers\Currency;
use ant\helpers\DateTime;
use ant\interfaces\Expirable;
use ant\payment\models\Billable;
use ant\token\models\Token;
use ant\user\models\User;
use ant\order\models\Order;
use ant\cart\components\CartableInterface;
use ant\discount\helpers\Discount;
use ant\cart\models\CartOption;

/**
 * This is the model class for table "em_cart".
 *
 * @property integer $id
 * @property string $type
 * @property string $created_ip
 * @property integer $created_by
 * @property string $created_at
 *
 * @property CartItem[] $cartItems
 */
class Cart extends \yii\db\ActiveRecord implements Billable, Expirable
{
	const TYPE_DEFAULT = 'default';
	
	const STATUS_ACTIVE = 0;
	const STATUS_INACTIVE = 1;
	
    const EXPIRE_DURATION = 10 * 60;
	
	/* 
	$itemData = [
		'attributeName' => function ($item) { return $item->attributeName; },
		'attributeName2',
	]
	*/
	public $item;
	//public $itemClass;
	public $itemData = [];
	public $itemUniqueHashId;
	public $itemDescription;
	public $cartServiceCharges;
	public $cartAbsorbedServiceCharges;
	
	public $tokenQueryParams = [];
	
	protected $_selectedCartItemIds = [];
	protected $_cartOptions;
	
    public function behaviors()
    {
        return
        [
			'configurable' => [
				'class' => 'ant\behaviors\ConfigurableModelBehavior',
			],
            [
				'class' => \yii\behaviors\BlameableBehavior::className(),
				'updatedByAttribute' => null,
			],
            [
				'class' => \ant\behaviors\TimestampBehavior::className(),
			],
            [
				'class' => \ant\behaviors\IpBehavior::className(),
				'updatedIpAttribute' => null,
			],
			[
				'class' => \ant\behaviors\DuplicatableBehavior::className(),
				'relations' => [
					'cartItems' => [],
				],
			],
			[
                'class' => 'ant\behaviors\DateTimeAttributeBehavior',
                'attributes' => [
					'created_at', 'updated_at',
				],
            ],
            [
                'class' => \ant\behaviors\SerializeBehavior::className(),
                'attributes' => ['data', 'options'],
                'serializeMethod' => \ant\behaviors\SerializeBehavior::METHOD_JSON,
            ],
        ];
    }
	
	public function init() {
		if (!isset($this->type)) $this->type = self::TYPE_DEFAULT;

        foreach ($this->events() as $event => $handler) {
            $this->on($event, is_string($handler) ? [$this, $handler] : $handler);
        }
		return parent::init();
	}
	
	public function events() {
		return [
			//self::EVENT_AFTER_FIND => 'afterFind',
			self::EVENT_AFTER_INSERT => 'afterInsert',
		];
	}
	
	public function deleteCartItem($cartItem) {
		$cartItem = CartItem::findOne($cartItem);
		
		if (isset($cartItem)) {
			if (!$cartItem->delete()) throw new \Exception('Failed to delete cart item. ');
			if(!isset($this->cartItems)) {
				$this->remark = null;
				$this->save();
			}
			return true;
		}
		return false;
	}
	
	public function removeItem($item, $quantity = null) {
		if ($this->isNewRecord) {
			throw new \Exception('Cart need to be saved first before add item. ');
		}
		
		$cartItem = $this->getCartItems()->where(['item_id' => $item->id])->one();
		if (isset($cartItem)) {
			if (isset($quantity)) {
				return $cartItem->updateCounters(['quantity' => 0 - $quantity]);
			} else {
				return $cartItem->delete();
			}
		} else {
			throw new \Exception('Item is not exist in cart. ');
		}
	}
	
	public function setQuantity($cartItem, $quantity, $refreshCartItem = true, $refreshCart = true) {
		$cartItem = CartItem::findOne($cartItem);
		if ($cartItem->is_locked) throw new \Exception('Cart item locked cannot be updated. ');
		$cartItem->quantity = $quantity;
		
		if ($refreshCartItem) $cartItem->refreshPrice();
		
		if (!$cartItem->save()) throw new \Exception(\yii\helpers\Html::errorSummary($cartItem));
		
		if ($refreshCart) {
			$this->touch('updated_at');
		}
	}
	
	public static function find() {
		return new \ant\cart\models\query\CartQuery(get_called_class());
	}
	
	public static function statusOptions() {
		return [
			self::STATUS_ACTIVE => [
				'label' => 'Active',
				'cssClass' => 'label-success',
			],
			self::STATUS_INACTIVE => [
				'label' => 'Inactive',
				'cssClass' => 'label-default',
			],
		];
	}
	public function getSelectedCartItems() {
		$selected = [];
		foreach ($this->cartItems as $item) {
			if ($this->isCartItemSelected($item->id)) {
				$selected[] = $item;
			}
		}
		return $selected;
	}
	
	public function getSelectedCartItemIds() {
		return $this->_selectedCartItemIds;
	}
	
	public function setSelectedCartItemIds($ids) {
		$this->_selectedCartItemIds = $ids;
		return $this;
	}
	
	public function getIsAbleToCheckout() {
		if ($this->isExpired) return false;
		
		foreach ($this->cartItems as $item) {
			if ($this->isCartItemSelected($item->id) && !$item->getIsAbleToCheckout()) {
				return false;
			}
		}
		return true;
	}

	public function getIsAbleToQuotation() {
		if ($this->isExpired) return false;
		
		foreach ($this->cartItems as $item) {
			if ($this->isCartItemSelected($item->id) && !$item->getIsAbleToQuotation()) {
				return false;
			}
		}
		return true;
	}

	/*protected function hasQuotationItem() {
		foreach ($this->cartItems as $item) {
			if($item->status == $item::CODE_QUOTATION) {
				return false;
			}
		}
		return true;
	}

	protected function hasNotQuotationItem() {
		foreach ($this->cartItems as $item) {
			if($item->status != $item::CODE_QUOTATION) {
				return false;
			}
		}
		return true;
	}*/

	public function getIsActive() {
		$minute = 30;
		$updated = new DateTime($this->updated_at, new \DateTimeZone('Asia/Kuala_Lumpur'));
		if (time() - $updated->getTimestamp() <= $minute * 60) {
			return true;
		}
		return false;
	}
	
	public function getStatusText() {
		return $this->getStatusOption('label', 'Unknown');
	}
	
	public function getRelativeTime($attribute) {
		if (isset($this->{$attribute})) {
			return \Yii::$app->formatter->asRelativeTime(($this->{$attribute}).' Asia/Kuala_Lumpur');
		}
	}
	
	public function getStatusOption($option, $default = null) {
		$status = $this->status;
		$status = $this->isActive ? self::STATUS_ACTIVE : self::STATUS_INACTIVE;
		
		$options = self::statusOptions();
		if (isset($option)) {
			if (isset($option)) {
				$value = isset($options[$status][$option]) ? $options[$status][$option] : null;
			} else {
				$value = isset($options[$status]) ? $options[$status] : null;
			}
			
			if (!isset($value)) {
				return $default;
			}
			return $value;
		} else {
			return $options;
		}
	}
	
	public function addQuoteItem(CartableInterface $item, $quantity = 1, $isLock = false, $attributes = []) {
		return $this->_addItem(CartItem::TYPE_QUOTE, $item, $quantity, $isLock, $attributes);
	}
	
	public function addItem(CartableInterface $item, $quantity = 1, $isLock = false, $attributes = []) {
		return $this->_addItem(CartItem::TYPE_ORDER, $item, $quantity, $isLock, $attributes);
	}
	
	protected function getCartItemScenarioByType($type) {
		if ($type == CartItem::TYPE_ORDER) {
			return CartItem::SCENARIO_ADD_TO_CART;
		} else if ($type == CartItem::TYPE_QUOTE) {
			return CartItem::SCENARIO_ADD_TO_QUOTATION;
		} else {
			throw new \Exception('CartItem type is not supported. ');
		}
	}
	
	private function _addItem($type, CartableInterface $item, $quantity = 1, $isLock = false, $attributes = []) {
		if (!isset($item)) throw new \Exception('Cannot add a null item. ');
		if ($this->isNewRecord) throw new \Exception('Cart need to be saved first before add item. ');
		//$itemUniqueHashId = $this->_getItemUniqueHashId($item);
		
		if ($isLock) {
			// New locked cart item should always a new separated record.
			$cartItem = null;
		} else {
			// Locked cart item should not be updated, hence retrieve only those not locked.
			$cartItem = $this->getCartItems()->where([
				'item_id' => $item->getId(), 
				'unique_hash_id' => $item->getUniqueHashId(), 
				'is_locked' => 0
			])->one();
		}
			
		if (isset($cartItem)) {
			if ($quantity) {
				if (!$cartItem->updateCounters(['quantity' => $quantity])) throw new \Exception(\yii\helpers\Html::errorSummary($cartItem));
			}
			return $cartItem;
		} else {
			//$currency = isset($attributes['currency']) ? $attributes['currency'] : null;
			
			$cartItem = new CartItem([
				'scenario' => $this->getCartItemScenarioByType($type),
				'cart_id' => $this->id
			]);
			
			$cartItem->attributes = $attributes;
			$cartItem->name = $item->getName();
			$cartItem->unique_hash_id = $item->getUniqueHashId();
			$cartItem->item_id = $item->getId();
			$cartItem->data = ArrayHelper::merge(isset($attributes['data']) ? $attributes['data'] : [], [CartItem::DATA_CARTABLE => $item->getCartItemCustomData()]);
			$cartItem->quantity = $quantity;
			$cartItem->is_locked = $isLock ? 1 : 0;
			
			$cartItem->refreshPrice();
			
			if (!$cartItem->save()) throw new \Exception(Html::errorSummary($cartItem));
			
			return $cartItem;
		}
	}
	
	public function getItemsTotalQuantity() {
		return $this->getItemsAttributeTotal('quantity');
	}
	
	public function getItemsTotal() {
		return $this->getItemsAttributeTotal('totalPrice');
	}
	
	protected function isCartItemSelected($cartItemId) {
		return !count($this->selectedCartItemIds) || in_array($cartItemId, $this->selectedCartItemIds);
	}
	
	protected function getItemsAttributeTotal($attribute) {
        $sum = 0;
        foreach ($this->cartItems as $model) {
			if ($this->isCartItemSelected($model->id)) {
				$sum += $model->{$attribute};
			}
        }
        return $sum;
    }
	
	public function getNetTotal() {
		return $this->getCalculatedNetTotal();
	}
	
	public function getSubtotal() {
		return Currency::rounding($this->getItemsAttributeTotal('netTotal'));
	}
	
	public function getCalculatedNetTotal() {
		return Currency::rounding($this->getCartOptionsTotalPrice() + $this->getSubtotal() + $this->getServiceCharges() - $this->getDiscountAmount() + $this->getTaxCharges());
	}
	
	public function getDueAmount() {
		return $this->getCalculatedNetTotal();
	}
	
	public function getIsPaid() {
		return false;
	}
	
	public function getIsFree() {
		return false;
	}
	
	public function getCurrency() {
		return 'MYR';
	}
	
	public function getBillItems() {
		return array_merge((array) $this->cartItems, (array) $this->cartOptions);
	}
	
	public function getDiscountAmount() {
		// @TODO: implement cart discount rule
		if (isset(Yii::$app->discount)) {
			$discount = Yii::$app->discount->getDiscountForCart($this);
			if ($discount instanceof Discount) {
				return $discount->of($this->getSubtotal());
			} else {
				return $discount;
			}
		}
		return Currency::rounding(0);
	}
	
	public function getDescriptionForCartItem($cartItem) {
		return $this->getDescriptionFor($cartItem);
	}
	
	public function setAttributes($values, $safeOnly = true) {
		$this->_cartOptions = null;
		return parent::setAttributes($values, $safeOnly);
	}
	
	protected function validateItemTotal() {
		//return $this->getCalculatedNetTotal() == $this->getCartOptionsTotalPrice() + $this->getSubtotal();
	}
	
	protected function getCartOptions() {
		if (!isset($this->_cartOptions)) {
			$this->_cartOptions = CartOption::findAll(['id' => $this->options]);
		}
		return $this->_cartOptions;
	}

	public function getCartOptionsTotalPrice() {
		$options = $this->getCartOptions();
		$price = 0;
		foreach ($options as $option) {
			$price += $option->price_adjust;
		}
		return $price;
	}
	
	protected function getDescriptionFor($cartItem) {
		if (is_callable($this->itemDescription)) {
			return call_user_func_array($this->itemDescription, [$cartItem]);
		}
		return $this->itemDescription;
	}

	
	/*protected function _getItemUniqueHashId($item) {
		if (is_callable($this->itemUniqueHashId)) {
			return md5(call_user_func_array($this->itemUniqueHashId, [$item]));
		}
		return md5($item->getUniqueHashId());
	}
	
	protected function _getItemData($item) {
		if (is_callable($this->itemData)) {
			return call_user_func_array($this->itemData, [$item]);
		} else {
			$data = [];
			foreach ($this->itemData as $attribute => $getter) {
				if (is_callable($getter)) {
					$data[$attribute] = call_user_func_array($getter, [$item]);
				} else {
					$data[$getter] = $item->{$getter};
				}
			}
			return $data == [] ? null : $data;
		}
	}
	
	protected function _getItem($item) {
		if (is_object($item)) {
			return $item;
		} else if (is_numeric($item)) {
			throw new \Exception('Not yet implement: to get item by item id. ');
		}
	}
	*/
	
	public function afterFind() {
		if (!isset($this->type)) throw new \Exception('Type of cart is not set. ');
		//if (!isset(\Yii::$app->cart->types[$this->type])) throw new \Exception('Cart of "'.$this->type.'" is not configured properly. ');
		if (isset(\Yii::$app->cart) && isset(\Yii::$app->cart->types[$this->type])) {
			\Yii::configure($this, \Yii::$app->cart->types[$this->type]);
		}
        return parent::afterFind();
	}
	
	protected function decryptId($hash) {
		$type = Token::TOKEN_TYPE_CART_EVENT_REGISTER;
		return isset(Yii::$app->encrypter) ? Yii::$app->encrypter->decrypt($hash) : Yii::$app->getSecurity()->decryptByPassword($hash, $type);
	}
	
	protected function encryptId($id) {
		$type = Token::TOKEN_TYPE_CART_EVENT_REGISTER;
		return isset(Yii::$app->encrypter) ? Yii::$app->encrypter->encrypt($id) : Yii::$app->getSecurity()->encryptByPassword($id, $type);
	}

	public function generateToken() {
		$type = Token::TOKEN_TYPE_CART_EVENT_REGISTER;
		$queryParams = [
            'cart' => $this->encryptId($this->id),
            'tokenkey' => Token::createTokenKey()
		];
		$duration = self::EXPIRE_DURATION;
		
		$token = new Token;

        $token->type = $type;
        $token->queryParams = $queryParams;
        $token->duration = $duration;

        if($token->save()) $this->token_id = $token->id;
		if (!$this->save()) throw new \Exception(Html::errorSummary($token));

		return $token;
	}

    public function afterInsert($event)
	{
        $this->tokenQueryParams = $this->generateToken()->queryParams;
	}
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cart}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return $this->getCombinedRules([
            [['created_by'], 'integer'],
            [['created_at', 'remark', 'data', 'options'], 'safe'],
            [['type'], 'string', 'max' => 50],
            [['created_ip'], 'string', 'max' => 40],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'created_ip' => 'Created Ip',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
	}
	
	public function getOrder() {
		return $this->hasOne(Order::className(), ['cart_id' => 'id']);
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCartItems()
    {
		return $this->hasMany(CartItem::className(), ['cart_id' => 'id']);
	}

	public function getCartItemsIndexedById()
    {
		return $this->hasMany(CartItem::className(), ['cart_id' => 'id'])->indexBy('id');
	}
	
	public function getQuotingCartItems()
    {
		$query = $this->hasMany(CartItem::className(), ['cart_id' => 'id'])
			->andOnCondition(
				['status' => 2]
			);
		return $query;
    }

    /*public function getTotal()
    {
        return $this->getItemsTotal() + $this->getServiceCharges();
    }*/
	
	public function getCreatedBy() {
		return $this->hasOne(User::className(), ['id' => 'created_by']);
	}

	public function getToken()
	{
		return $this->hasOne(Token::className(), ['id' => 'token_id']);
		
		/*return $this->hasOne(Token::className(), ['id' => 'token_id'])
			->viaTable('{{%cart_token_map}}', ['cart_id' => 'id']);*/
	}
	
	public function getTaxCharges() {
		if (isset(Yii::$app->taxManager)) {
			return Currency::rounding(Yii::$app->taxManager->getTaxTotalForCart($this));
		}
		return 0;
	}
	
	public function getAbsorbedServiceCharges() {
		return isset(Yii::$app->cart) ? Yii::$app->cart->getCartAbsorbedServiceCharges($this) : 0;
	}
	
	public function getServiceCharges() {
		return isset(Yii::$app->cart) ? Yii::$app->cart->getCartServiceCharges($this) : 0;
	}
	
	protected function validateItems($attributeNames = null, $scenario = null) {
		foreach ($this->cartItems as $item) {
			if (isset($scenario)) $item->setScenario($scenario);
			if (!$item->validate($attributeNames)) return false;
		}
		return true;
	}

    public function renew()
    {
        $this->token->renew(self::EXPIRE_DURATION);
    }

    public function expire()
    {
        $this->token->expire();
	}

	public function getIsExpired() {
		if (isset($this->token)) {
			return $this->token->getIsExpired();
		}
	}
	
	public function duplicateWithCartItems() {
	$transaction = Yii::$app->db->beginTransaction();
		try {
			$newCart = $this->duplicate();
			$transaction->commit();
		} catch (\Exception $ex) {
			$transaction->rollBack();
			throw $ex;
		}
		return $newCart;
	}

	// refresh cart data to before checkout status.
	protected function renewCart() {
		$this->remark = null;
		$this->save();
	}

	public function markAsExpired() {
		return $this->expire();
	}

	public function splitCart($newCartItemIds) {
		if (count($newCartItemIds)) {
			$transaction = Yii::$app->db->beginTransaction();
			
			$newCart = new Cart(['type' => $this->type, 'remark' => $this->remark]);
			$this->renewCart();

			if (!$newCart->save()) throw new \Exception(Html::errorSummary($newCart));
			
			foreach ($this->getCartItems()->all() as $item) {
				if (in_array($item->id, $newCartItemIds)) {
					$item->cart_id = $newCart->id;
					if (!$item->save()) throw new \Exception(Html::errorSummary($item));
				}
			}
			
			// To refresh $this->cartItems relation
			$this->refresh();
			
			$transaction->commit();
			
			return $newCart;
		}
	}
	
	public function getRenewRoute($token) {
		return ['/cart/cart/renew-token', 'cart' => $this->encryptId($this->id), 'tokenkey' => $token];
	}

	/*public function getStatusSelectedCartItems($ids) {
		define("STATUS_CANNOT_CHECKOUT_NOR_QUOTATION", 0);
		define("STATUS_CAN_CHECKOUT", 1);
		define("STATUS_CAN_QUOTATION", 2);
		$status;
		$statusCart = $this->getIsAbleToCheckout();
		$selecteds = $this->getSelectedCartItems();
		if(!$this->validateItems(null, CartItem::SCENARIO_CHECKOUT)) {
			return STATUS_CANNOT_CHECKOUT_NOR_QUOTATION;
		}
		if(!$statusCart) {
			$statusCart = $this->getIsAbleToQuotation();
			if($statusCart) {
				$status = STATUS_CAN_QUOTATION;
			} else {
				$status = STATUS_CANNOT_CHECKOUT_NOR_QUOTATION;
			}
		} else {
			$status = STATUS_CAN_CHECKOUT;
		}

		if($status == STATUS_CANNOT_CHECKOUT_NOR_QUOTATION) {
			$status = STATUS_CAN_CHECKOUT;
			foreach ($selecteds as $key => $selected) {
				if ($ids == [] || $selected->status == $selected::CODE_QUOTATION) {
					$status = STATUS_CAN_QUOTATION; // selected got at least one quotation
					break; // got one quotation
				}
			} 
			
			if($status == STATUS_CAN_QUOTATION) {
				foreach ($selecteds as $key => $selected) {
					if ($ids == [] || $selected->status != $selected::CODE_QUOTATION) {
						$status = STATUS_CANNOT_CHECKOUT_NOR_QUOTATION; // selected can not checkout and request quotation
						break; // got one cart
					}
				}
			}
		}
		return $status;
	}*/
}
