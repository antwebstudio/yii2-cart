<?php

return [
    'id' => 'cart',
	'alias' => [
		'@frontend/modules/cart' => dirname(dirname(dirname(__DIR__))).'/frontend/modules/cart',
		'@ant/cart' => dirname(dirname(dirname(__DIR__))).'/common/modules/cart',
		'@backend/modules/cart' => dirname(dirname(dirname(__DIR__))).'/backend/modules/cart',
	],
    'class' => \frontend\modules\cart\Module::className(),
    'isCoreModule' => false,
	'depends' => [], // Cart module should not depends on any other module
];
?>