<?php

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = $this->context->module->getPageTitle($this->context, 'My Shopping Cart');
?>

<?php if (count($cart->cartItems)): ?>
	<?php \yii\widgets\Pjax::begin() ?>
		<?= \common\modules\cart\widgets\CartList::widget([
			'cart' => $cart,
		]) ?>
	<?php \yii\widgets\Pjax::end() ?>
<?php else: ?>
	<?= \yii\bootstrap\Alert::widget([
		'closeButton' => false,
		'options' => [
			'class' => 'alert-warning',
		],
		'body' => 'No product added',
	]); ?>
<?php endif; ?>