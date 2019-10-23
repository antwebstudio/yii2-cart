<?php
namespace common\modules\cart\models;

use yii\db\ActiveRecord;

use common\modules\payment\models\PayableItem;

use common\modules\event\models\TicketType;
use common\modules\cart\models\Cart;
use common\modules\cart\models\query\CartTicketQuery;

class CartTicket extends ActiveRecord implements PayableItem
{
	public function init() {
		if (YII_DEBUG && YII_LOCALHOST) throw new \Exception('DEPRECATED'); // 2019-10-23
		return parent::init();
	}
	
    public static function tableName()
    {
        return '{{%cart_ticket}}';
    }

    public static function find()
    {
        return new CartTicketQuery(get_called_class());
    }

    public function attributeLabels()
    {
        return
        [
            'id' => 'ID',
            'cart_id' => 'Event Ticket Cart ID',
            'event_ticket_id' => 'Event Ticket ID',
            'quantity' => 'quantity',
        ];
    }

    public function rules()
    {
        return
        [
            [['quantity'], 'required'],
            [['cart_id', 'event_ticket_id', 'quantity'], 'integer'],
        ];
    }

    public function getCart()
    {
        return $this->hasOne(Cart::className(), ['id' => 'cart_id']);
    }
	
	public function getTicketType() {
		return $this->getTicket();
	}

    public function getTicket()
    {
        return $this->hasOne(TicketType::className(), ['id' => 'event_ticket_id']);
    }

    public function getId()
    {
        return $this->getId();
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getAmount()
    {
        return $this->getTicket()->one()->price;
    }
	
	public function getDescription() {
		return '';
	}

    public function getTitle()
    {
        return $this->getTicket()->one()->name;
    }
    public function getSubTotal(){
        return $this->amount * $this->quantity;
    }
	
	public function getUnitPrice() {
		return $this->ticketType->price;
	}
	
	public function getDiscount() {
		return 0;
	}
	
	public function setDiscount($discount, $discountType = 0) {
		
	}
	
	public function getDiscountedUnitPrice() {
		return $this->getUnitPrice();
	}
	
	public function getNetTotal() {
		return $this->getDiscountedUnitPrice() * $this->quantity;
	}
	
	public function getDiscountedAmount() {
		if (YII_DEBUG) throw new \Exception('Method deprecated. ');
		return $this->getDiscountedUnitPrice() * $this->quantity;
	}
	
	public function getName() {
		return $this->ticketType->name;
	}
	
	public function deductAvailableQuantity($quantity) {
		
	}
}

?>
