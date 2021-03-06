<?php

use yii\helpers\Url;
use yii\helpers\Html;
use ant\discount\widgets\DiscountCoupon;

$this->title = $this->context->module->getPageTitle($this->context, 'My Shopping Cart');
?>

<?php if (count($cart->cartItems)): ?>

	<?= \ant\cart\widgets\CartList::widget([
		'editable' => false,
		'checkout' => true,
		'cart' => $cart,
		'buttons' => [
			'prev' => [
				'label' => '<i class="fa fa-angle-left"></i> Back',
				'url' => ['/cart/cart'],
			],
			'next' => [
				'label' => 'Confirm <i class="fa fa-angle-right"></i>',
				'url' => ['/ecommerce/cart/confirm'],
			],
		],
	]) ?>
	
	<?= DiscountCoupon::widget([
			'cart' => $cart,
	]) ?>

<?php else: ?>
	No product added
<?php endif ?>