<?php
namespace ant\cart\controllers;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use ant\event\models\Event;
use ant\cart\models\Cart;
use ant\cart\models\CartItem;
use ant\cart\models\CartForm;
use ant\token\models\Token;
use ant\order\models\Order;
use ant\order\models\OrderForm;

class CartItemController extends Controller
{
    public $layout = '//one-column';

	public function actionAdd() {
		$type = Yii::$app->request->post('type');
		$item_id = Yii::$app->request->post('item_id');
		$quantity = Yii::$app->request->post('quantity');
		if (!isset($quantity)) $quantity = 1;
		
		$cart = Yii::$app->cart->getLastCart($type);
		
		//$cartItem = Yii::$app->cart->createCartItem($type);
		$className = $cart->itemClass;
		$cartItem = $className::findOne($item_id);
		
		if ($cartItem->load(Yii::$app->request->post()) && $cart->addItem($cartItem, $quantity)) {
			return $this->redirect(Yii::$app->request->referrer);
		}
		throw new \Exception('Failed to add to cart. ');
	}
	
	public function actionAttachment($id, $type = 'cart') {
		$model = CartItem::findOne($id);
		
		if (!isset($model)) throw new \Exception("Cart item is not exist. ");
		
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			if($type == 'cart') {
				return $this->redirect(['/cart/cart']);
			}
        }
		
		return $this->render($this->action->id, [
			'model' => $model,
		]);
	}
}
?>
