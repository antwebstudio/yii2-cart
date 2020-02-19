<?php
namespace ant\cart\controllers;

use Yii;

class DefaultController extends \yii\web\Controller
{
	public function actionIndex() {
		return $this->redirect(['/cart/cart']);
	}
}