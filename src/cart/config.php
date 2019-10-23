<?php

return [
    'id' => 'cart',
	'namespace' => 'ant\cart',
    'class' => \ant\cart\Module::className(),
	'aliases' => [
		'@ant/cart' => dirname(dirname(dirname(__DIR__))).'/cart',
	],
    'isCoreModule' => false,
	'migrations' => [
		'ant\cart\migrations\db',
	],
];