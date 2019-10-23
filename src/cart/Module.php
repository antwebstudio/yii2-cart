<?php

namespace ant\cart;

/**
 * cart module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
	 public function behaviors() {
		return [
			'configurable' => [
				'class' => 'ant\behaviors\ConfigurableModuleBehavior',
				'formModels' => [
					'order' => [
						'class' => 'ant\order\models\OrderForm',
						
					],
					'addToCart' => [
						'class' => 'ant\ecommerce\models\AddToCartForm',
						'scenario' => \ant\ecommerce\models\AddToCartForm::SCENARIO_ADD_TO_CART,
					],
				],
			],
		];
	 }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
