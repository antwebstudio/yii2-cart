<?php

return [
    'id' => 'cart',
	'namespace' => 'common\modules\cart',
    'class' => \common\modules\cart\Module::className(),
	'aliases' => [
		'@ant/cart' => dirname(dirname(dirname(__DIR__))).'/cart',
	],
    'isCoreModule' => false,
	'migrations' => [
		'ant\cart\migrations\db',
	],
];