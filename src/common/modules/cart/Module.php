<?php

namespace common\modules\cart;

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
				'class' => 'common\behaviors\ConfigurableModuleBehavior',
				'formModels' => [
					'order' => [
						'class' => 'common\modules\order\models\OrderForm',
						
					],
					'addToCart' => [
						'class' => 'common\modules\ecommerce\models\AddToCartForm',
						'scenario' => \common\modules\ecommerce\models\AddToCartForm::SCENARIO_ADD_TO_CART,
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
