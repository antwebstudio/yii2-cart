<?php

return [
    'id' => 'cart',
	'namespace' => 'ant\cart',
    'class' => \ant\cart\Module::className(),
	/*'aliases' => [
		//'@ant/cart' => dirname(dirname(dirname(__DIR__))).'/cart',
	],*/
	'modules' => [
		'v1' => \ant\cart\api\v1\Module::class,
		'backend' => \ant\cart\backend\Module::class,
	],
    'isCoreModule' => false,
	'depends' => [
		'user',
	],
	/*'migrations' => [
		'ant\cart\migrations\db',
	],*/
];