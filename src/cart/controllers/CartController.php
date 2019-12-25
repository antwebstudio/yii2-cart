<?php
namespace ant\cart\controllers;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use ant\product\models\Product;
use ant\order\models\Order;
use ant\payment\models\Invoice;
use ant\token\models\Token;
use ant\cart\models\Cart;
use ant\cart\models\CartItem;
use ant\cart\models\CartForm;

class CartController extends Controller
{
    //public $layout = '//one-column';

	public function actionIndex($type = null) {
		$cart = Yii::$app->cart->getLastCart($type);
		if (Yii::$app->request->isGet) {
			if (Yii::$app->request->get('action') == 'setQuantity') {
				$cartItemId = Yii::$app->request->get('id');
				$quantity = Yii::$app->request->get('quantity');
				
				if ($quantity > 0) {
					$cart->setQuantity($cartItemId, $quantity);
					return $this->redirect(['index']);
				} else if ($quantity == 0) {
					if (!$cart->deleteCartItem($cartItemId)) {
						throw new \Exception('Failed to delete cart item. ');
					}
					return $this->redirect(['index']);
				}
			} else if (Yii::$app->request->get('action') == 'deleteCartItem') {
				
				$cartItemId = Yii::$app->request->get('id');
				 if (!$cart->deleteCartItem($cartItemId)) {
					throw new \Exception('Failed to delete cart item. ');
				 }
				 return $this->redirect(['index']);
			}
		}
		
		return $this->render('index', [
			'cart' => $cart,
		]);
	}
	
	public function actionCheckout($type = null) {
		if(!$this->canCheckOut()) {
			return $this->redirect(['index']);
		}
		
		$cart = Yii::$app->cart->getLastCart($type);
		if($cart->load(Yii::$app->request->post())) {
			$cart->save();
		}

		$cart->setSelectedCartItemIds($this->getSelectedCartItemIds());
		
		return $this->render('checkout', [
			'cart' => $cart,
		]);
	}

	public function actionBuy($item, $type = null) {
		if (!isset($type)) $className = Product::class;
		$item = $className::findOne($item);

		$model = $this->module->getFormModel('addToCart', ['item' => $item]);
		
		if ($model->load(Yii::$app->request->post(), '') && $model->checkout()) {
			return $this->redirect($model->returnUrl);
        } else if ($model->checkout()) {
			return $this->redirect($model->returnUrl);
		}
	}
	
	public function actionAjaxGetCart() {
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

		$cart = Yii::$app->cart->getLastCart();
		
		$data = $cart->attributes;
		$data['cartItems'] = $cart->cartItems;
		
		return $data;
	}
	
	public function actionAjaxAddItem() {
        $model = $this->module->getFormModel('addToCart');
		
        // Add to cart
        if ($model->load(Yii::$app->request->post(), '') && $model->process()) {
			if (isset($model->returnUrl) && $model->returnUrl) {
				return $this->redirect($model->returnUrl);
			}
        }
	}
	
	public function actionAjaxRemoveItem() {
		$productId = Yii::$app->request->post('product');
		$cart = Yii::$app->cart->getLastCart();
		
		if (!$cart->removeItem(\ant\product\models\Product::findOne($productId))) {
			throw new \Exception('Failed to remove cart item. ');
		}
	}

	protected function canCheckOut() {
		$cart = Yii::$app->cart->getLastCart()->setSelectedCartItemIds($this->getSelectedCartItemIds());

		if(!$cart->isAbleToCheckout && !$cart->isAbleToQuotation) {
			Yii::$app->session->setFlash('error', 'Not allowed to checkout.');
			return false;
		}
		return true;
	}

	protected function getSelectedCartItemIds() {
		return Yii::$app->request->post('cart-item', []);
	}

	public function actionConfirm($type = null, $orderType = null) {
		if(!$this->canCheckOut()) {
			return $this->redirect(['index']);
		}
		$cart = Yii::$app->cart->getLastCart($type)->setSelectedCartItemIds($this->getSelectedCartItemIds());
		if($cart->load(Yii::$app->request->post())) {
			$cart->save();
		}
		
		$model = $this->module->getFormModel('order', [
			'type' => $orderType,
			'cart' => $cart,
		]);
		
		if (!Yii::$app->user->isGuest) {
			$model->setUserProfile(Yii::$app->user->identity->profile);
		}
		
		$transaction = Yii::$app->db->beginTransaction();

		if ($cart->id && $model->load(Yii::$app->request->post()) && $model->validate()) {
			if ($model->save()) {
				$invoice = Invoice::createFromBillableModel($model->order); // Need to create the invoice after order is saved, as we only able to get discount amount of order after order is saved.

				if ($model->order->cart->id == $cart->id) {
					// Reset cart when order is created and if the checkout cart is current cart
					Yii::$app->cart->createCart($type);
				}
			}
			$transaction->commit();

			if (Yii::$app->request->isAjax) {
				Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
				return ['success' => true];
			}
				
			return Yii::$app->response->redirect($model->getRedirectRoute());
		} else if (Yii::$app->request->isAjax) {
			Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return ['error' => \yii\widgets\ActiveForm::validate($model)];
		}
		
		return $this->render('confirm', [
			'model' => $model,
			'cart' => $cart,
		]);
	}

	public function actionCheckCanCheckOut(array $ids = []) {
		$cart = Yii::$app->cart->getLastCart()->setSelectedCartItemIds($ids);

		if(Yii::$app->request->isAjax) {
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		}

		return [
			'checkout' => $cart->isAbleToCheckout,
			'requestQuotation' => $cart->isAbleToQuotation,
		];
	}
	
    public function actionRenewToken($cart, $tokenkey)
    {
        $model = Cart::findOne(Yii::$app->encrypter->decrypt($cart));

		if(!$model) throw new HttpException(404, 'Page not found');

		$token = Token::find()
			->byCart($model)
			->byType(Token::TOKEN_TYPE_CART_EVENT_REGISTER)
			->byQueryParams([
				'cart' => $cart,
				'tokenkey' => $tokenkey
			])
			->isNotExpired()
			->one();

		if(!$token) throw new HttpException(404, 'Page not found');

		$model->renew();
    }
}