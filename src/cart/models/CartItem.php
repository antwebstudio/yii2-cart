<?php

namespace ant\cart\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\helpers\Currency;
use common\modules\payment\models\PayableItem;
use ant\cart\models\Cart;
use common\modules\product\models\Product;
use common\modules\ecommerce\models\ProductVariant;
use common\modules\discount\helpers\Discount;
use ant\cart\components\CartableInterface as Cartable;

/**
 * This is the model class for table "em_cart_item".
 *
 * @property integer $id
 * @property integer $cart_id
 * @property integer $item_id
 * @property string $name
 * @property string $url
 * @property integer $quantity
 * @property string $unit_price
 * @property double $discount_value
 * @property integer $discount_type
 * @property string $created_at
 *
 * @property Cart $cart
 */
class CartItem extends \yii\db\ActiveRecord implements PayableItem
{
	use \common\modules\payment\traits\BillableTrait;
	use \common\traits\StatusTrait;
	
	public $attachments;
	public $attachments2;
	
	const SCENARIO_ADD_TO_CART = 'add_to_cart';
	const SCENARIO_ADD_TO_QUOTATION = 'add_to_quotation';
	const SCENARIO_CHECKOUT = 'checkout';
	const SCENARIO_REQUEST_QUOTATION = 'request_quotation';
	const SCENARIO_ADMIN_UPDATE = 'admin_update';
	
	const DATA_CARTABLE = 'cartable';
	const DATA_FORM = 'form';
	const DATA_VARIANT = 'variant';

	const CODE_QUOTATION = 10;
	
	const TYPE_QUOTE = 1;
	const TYPE_ORDER = 0;
	
	public function behaviors() {
		return [
			[
				'class' => \common\behaviors\SerializeBehavior::className(),
				'attributes' => ['data'],
				'serializeMethod' => \common\behaviors\SerializeBehavior::METHOD_JSON,
			],
			[
				'class' => \common\behaviors\AttachBehaviorBehavior::className(),
				'config' => '@common/config/behaviors.php',
			],
			[
				'class' => \common\behaviors\DuplicatableBehavior::className(),	
			],
		];
	}
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cart_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
			[[], 'required', 'on' => self::SCENARIO_REQUEST_QUOTATION],
			[[], 'required', 'on' => self::SCENARIO_ADD_TO_QUOTATION],
            [['cart_id', 'item_id', 'quantity', 'discount_type', 'status'], 'integer'],
			[['quantity'], 'required'],
			[['unit_price'], 'required', 'except' => [self::SCENARIO_ADD_TO_QUOTATION, self::SCENARIO_REQUEST_QUOTATION]],
            [['unit_price', 'discount_value'], 'number'],
			[['created_at', 'currency', 'is_locked', 'remark', 'attachments', 'attachments2'], 'safe', 'on' => [self::SCENARIO_REQUEST_QUOTATION, self::SCENARIO_ADD_TO_CART, self::SCENARIO_CHECKOUT]],
			[['created_at', 'currency', 'is_locked', 'remark', 'attachments', 'attachments2'], 'safe'],
			[['seller_remark'], 'safe', 'on' => self::SCENARIO_ADMIN_UPDATE],
            [['name'], 'string', 'max' => 200],
            [['url', 'shipping_tracking_code'], 'string', 'max' => 255],
            [['cart_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cart::className(), 'targetAttribute' => ['cart_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cart_id' => 'Cart ID',
            'item_id' => 'Item ID',
            'name' => 'Name',
            'remark' => 'Remark',
            'url' => 'Url',
            'quantity' => 'Quantity',
            'unit_price' => 'Unit Price',
            'discount_value' => 'Discount',
            'discount_type' => 'Discount Type',
            'created_at' => 'Created At',
        ];
    }
	
	protected function getParamsForPrice() {
		$params = isset($this->data[CartItem::DATA_CARTABLE]) ? $this->data['cartable'] : [];
		$params = ArrayHelper::merge($params, (array) $this->data);
		$params['form']['quantity'] = $this->quantity;
		$params['form']['currency'] = $this->currency;
		return $params;
	}

	protected function refreshUnitPrice() {
		if ($this->item instanceof Cartable) {
			
			$this->unit_price = $this->item->getPrice($this->getParamsForPrice());
			
			if (!isset($this->unit_price)) {
				if ($this->scenario == self::SCENARIO_ADD_TO_QUOTATION && $this->isCanAddToQuotation) {
					$this->unit_price = 0;
				} else {
					throw new \Exception('Failed to refresh unit price. '.print_r($this->errors,1));
				}
			}
		} else {
			if (isset($this->item)) {
				throw new \Exception('Failed to refresh unit price. Item instance of CartItem "'.get_class($this->item).'" is not cartable.');
			} else {
				throw new \Exception('Failed to refresh unit price. Item instance of CartItem is null.');
			}
		}
		return true;
	}
	
	public function refresh() {
		$result1 = parent::refresh();
		$result2 = $this->refreshPrice();
		return $result1 && $result2;
	}
	
	public function refreshPrice() {
		// Refresh unit price
		if ($this->type == self::TYPE_ORDER) {
			$this->refreshUnitPrice();
		
			// Refresh discount
			if (isset(Yii::$app->discount)) {
				$this->setDiscount(Yii::$app->discount->getDiscountForCartItem($this));
			}
		}
	}
	
	public function getImage() {
		if (isset($this->item->image)) return $this->item->image;
	}
	
	public function getUrl() {
		if (isset($this->item->url)) return $this->item->url;
	}
	
	public function getUnitPrice() {
		return Currency::rounding($this->unit_price);
	}
	
	public function getDiscountedUnitPrice() {
		return Currency::rounding($this->getUnitPrice() - $this->getDiscountAmount());
	}
	
	public function getDiscountedAmount() {
		// Different with net total, this is used to calculated service charges, hence not included service charges
		// while netTotal will included service charges
		return Currency::rounding($this->getDiscountedUnitPrice() * $this->quantity);
	}

	public function getIsFree() {
		return $this->netTotal == 0;
	}
	
	public function getNetTotal() {
		return Currency::rounding($this->getDiscountedUnitPrice() * $this->quantity);
	}
	
	public function getAmount() {
		if (YII_DEBUG) throw new \Exception('Deprecated method');
		return Currency::rounding($this->getUnitPrice() * $this->quantity);
	}
	
	protected function getTotalPrice() {
		return $this->getUnitPrice() * $this->quantity;
	}
	
	public function getTotalDiscountPrice() {
		return $this->getDiscountPrice() * $this->quantity;
	}
	
	public function getDiscountPrice() {
		if (YII_DEBUG) throw new \Exception('Deprecated');
		return $this->getDiscountAmount();
	}
	
	public function getDiscountAmount() {
		$discount = new \common\modules\discount\helpers\Discount($this->discount_value, $this->discount_type);
		return $discount->of($this->unitPrice);
	}
	
	public function getDescription() {
		return $this->cart->getDescriptionForCartItem($this);
	}
	
	public function getQuantity() {
		return $this->quantity;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getTitle() {
		return $this->name;
	}

	public function getIsLocked() {
		return $this->is_locked || isset($this->cart->order);
	}
	
	public function setDiscount($discount, $discountType = 0) {
		if ($discount instanceof \common\modules\discount\helpers\Discount) {
			$this->discount_value = $discount->value;
			$this->discount_type = $discount->type;
		} else {
			$this->discount_value = $discount;
			$this->discount_type = $discountType;
		}
	}
	
	public function getDiscount() {
		return new \common\modules\discount\helpers\Discount($this->discount_value, $this->discount_type);
	}
	
	public function deductAvailableQuantity($quantity) {
		return $this->item->deductQuantity($quantity);
	}
	
	public function getItem() {
		if (isset(Yii::$app->cart)) {
			return Yii::$app->cart->getItemByCartItem($this);
		}
		
		return $this->hasOne(Product::className(), ['id' => 'item_id']);
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCart()
    {
        return $this->hasOne(Cart::className(), ['id' => 'cart_id']);
    }
	
	public function getIsAbleToCheckout() {
		$this->scenario = self::SCENARIO_CHECKOUT;
		return $this->validate() && $this->status != self::CODE_QUOTATION;
	}

	// Checkout for quotation
	public function getIsAbleToQuotation() {
		$this->scenario = self::SCENARIO_REQUEST_QUOTATION;
		return $this->validate() && $this->status == self::CODE_QUOTATION;
	}

	// Add to cart for quotation
	protected function getIsCanAddToQuotation() {
		$this->scenario = self::SCENARIO_ADD_TO_QUOTATION;
		return $this->validate();
	}
	
	public function statusOptions() {
		return [
			0 => [
				'label' => 'Pending',
			],
			1 => [
				'label' => 'Shipped',
			],
			2 => [
				'label' => 'Delivered',
			],
			3 => [
				'label' => 'Completed',
			],
			4 => [
				'label' => 'Rejected',
			],
			self::CODE_QUOTATION => [
				'label' => 'quotation',
			],
		];
	}
}
