<?php
namespace ant\cart\models;

use Yii;
use ant\cart\models\Cart;

class CartForm extends \ant\web\FormModel
{
    public $item;
    public $returnUrl = ['/cart'];
    public $checkoutUrl = ['/cart/cart/checkout'];
    public $confirmUrl = ['/ecommerce/cart/confirm'];

	public function models() {
        return [
        ];
    }

    public function getCart() {
        return Yii::$app->cart->getLastCart();
    }

    public function checkout() {
        $this->returnUrl = $this->confirmUrl;
        return $this->process();
    }

    public function process() {
        $this->cart->addItem($this->item);
        return true;
    }
}
