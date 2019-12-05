<?php
namespace ant\cart\api\v1\controllers;

use ant\cart\models\Cart;

class CartController extends \yii\web\Controller {
    public function actionRenew($cart) {
        $cart = Cart::findByEncryptedId($cart);
        $cart->renew();

        return $cart;
    }
}