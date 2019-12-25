<?php

namespace ant\cart\backend\controllers;

use yii\web\Controller;
use yii\data\ActiveDataProvider;
use ant\cart\models\Cart;

/**
 * Default controller for the `cart` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
			'dataProvider' => new ActiveDataProvider([
				'query' => Cart::find()->orderBy('updated_at DESC'),
			]),
		]);
    }
	
	public function actionView($id) {
		$model = Cart::findOne($id);
		
		return $this->render($this->action->id, [
			'model' => $model,
		]);
	}
}
