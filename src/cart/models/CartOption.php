<?php

namespace ant\cart\models;

use Yii;

/**
 * This is the model class for table "cart_option".
 *
 * @property int $id
 * @property string $price_adjust
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 */
class CartOption extends \yii\db\ActiveRecord implements \ant\payment\models\BillableItem
{
	use \ant\payment\traits\BillableTrait;
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cart_option}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['price_adjust'], 'number'],
            [['status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }
	
	public function getUnitPrice() {
		return $this->price_adjust;
	}
	
	public function getDiscountedUnitPrice() {
		return \ant\helpers\Currency::rounding($this->getUnitPrice());
	}
	
	public function getQuantity() {
		return 1;
	}
	
	public function getDescription() {
		
	}
	
    public function getId() {
		
	}

    public function getTitle() {
		return $this->title;
	}
	
	public function setDiscount($discount, $discountType = 0) {
		
	}
	
	public function getIncludedInSubtotal() {
		return false;
	}

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'price_adjust' => 'Price Adjust',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
