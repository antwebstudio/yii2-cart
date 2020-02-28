<?php

use yii\helpers\Url;
use yii\helpers\Html;
use ant\widgets\Alert;

$this->title = $this->context->module->getPageTitle($this->context, Yii::t('cart', 'My Shopping Cart'));
?>

<?php if (count($cart->cartItems)): ?>
	<?php \yii\widgets\Pjax::begin() ?>
		<?= \ant\cart\widgets\CartList::widget([
			'cart' => $cart,
		]) ?>
	<?php \yii\widgets\Pjax::end() ?>
<?php else: ?>
	<?php /*
	<?= Alert::widget([
		'closeButton' => false,
		'options' => [
			'class' => 'alert-warning',
		],
		'body' => 'No product added',
	]) ?>
	*/?>
	<div class="empty cart text-center py-5 card">
		<i class="fa fa-4x fa-shopping-cart mb-3"></i>
		<h3><?= Yii::t('cart', 'Your shopping cart is empty.') ?></h3>
	</div>
<?php endif; ?>