<?php

											  
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use ant\helpers\Currency;

\ant\cart\widgets\CartListAsset::register($this);
?>
<?php \ant\widgets\JsBlock::begin() ?>
<script>
	(function($) {
		var settings = <?= Json::encode($this->context->options) ?>;
		var $list = $('#<?=$this->context->id ?>');
		cart = new Cart(settings, $list);
		
	})(jQuery);
</script>
<?php \ant\widgets\JsBlock::end() ?>

<style>
.summaryPrice{
    text-align: right;
}
.code h2, .code .quatity,.codeRemark h2,.codeRemark p  {
    display: inline-block;
}
.code h2, .codeRemark h2 {
    width: 84%;
}
</style>

<div class="summary summaryCart" id="<?= $this->context->id ?>">
	<div class="summaryTitle" >
		<h2>Summary</h2>
	</div>
	<?php foreach ($cart->cartItems as $item): ?>
	<div class="summaryDesc" data-item="<?= $item->id ?>">
		<div class="item">
			<div class="code">
					<h2><?= $item->name ?></h2>
					<p class="quatity"><?= $item->quantity ?></p>
					<a href="" data-action='delete' ><i class="fa fa-trash" ></i> </a>
					<p><?= $item->remark ?></p>
			</div>
			<?php if ($item->hasMethod('getDynamicAttribute')): ?>
				<?php foreach ($item->getDynamicAttribute('subproducts') as $subproduct): ?>
																							   
	   
					<div class="codeRemark">
						<h2><strong class="no"></strong><?= $subproduct['name'] ?><strong class="summaryRemark"><?=$subproduct['remark']?></strong></h2>
						<p class="quatity"><?= isset($subproduct['quantity']) && strlen(trim($subproduct['quantity'])) ? $subproduct['quantity'] : $subproduct['defaultQuantity'] ?></p>
										  
					</div>
				<?php endforeach; ?>
			<?php endif ?>
		</div>
		
	</div>
		<?php endforeach; ?>								

	<div class="summaryPrice">
		<p>TOTAL: <?= $this->context->currencySymbol ?><strong> <?= Currency::rounding($cart->subtotal) ?> </strong></p>
	</div>

	<br />

	<div class="text-right">
		<?=Html::a('Quotation',Url::to(['/cart/cart']), ['data-pjax' => 0, 'class' => 'btn btn-primary']) ?>
	</div>
</div>