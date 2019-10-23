<?php

namespace ant\cart\actions;
use yii\base\Action;
use Yii;

Class DeleteAction extends Action
{
	public $cart = null;
    public $type = null;
    public $returnUrl = null;

	public function run()
    {

       if (Yii::$app->request->isGet) {

            if (Yii::$app->request->get('action') == 'deleteCartItem') {

            	if($this->cart == null ) $this->cart = Yii::$app->cart->getLastCart($this->type);
                if($this->returnUrl == null ) $this->returnUrl = Yii::$app->request->referrer;
                $cartItemId = Yii::$app->request->get('id');
                $this->cart->deleteCartItem($cartItemId);
				
				if (!Yii::$app->request->isAjax) {
					return $this->controller->redirect ($this->returnUrl);
				}
            }
        }

    }




}