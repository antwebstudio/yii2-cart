<?php

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = $this->context->module->getPageTitle($this->context, 'My Shopping Cart');
?>

<?php if (count($cart->cartItems)): ?>

	<?= \ant\cart\widgets\CartList::widget([
		'editable' => false,
		'cart' => $cart,
		'buttons' => [
			'prev' => [
				'label' => '<i class="fa fa-angle-left"></i> Back',
				'url' => ['/cart/cart'],
			],
			'next' => [
				'label' => 'Confirm <i class="fa fa-angle-right"></i>',
				'url' => ['/cart/cart/confirm'],
			],
		],
	]) ?>

<?php else: ?>
	<?= \yii\bootstrap\Alert::widget([
		'closeButton' => false,
		'options' => [
			'class' => 'alert-warning',
		],
		'body' => 'No product added',
	]); ?>
<?php endif; ?>