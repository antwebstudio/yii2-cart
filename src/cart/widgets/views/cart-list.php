<?php

use ant\helpers\Currency;
use ant\cart\widgets\CartListAsset;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

CartListAsset::register($this);
$selectedCartItems = $cart->selectedCartItems;
$ids = Yii::$app->request->post('cart-item');
if(!is_array($ids) && $ids !== null) {
	$ids = [$ids];
}
?>
<?php $this->beginBlock('script') ?>
	(function($) {
		registerCheckCanCheckOut();
		function findCheckedIds() {
			var ids = [];
			$('.cart-item-checkbox').each(function(index, value) {
				if ($(value).is(':checked')) {
					var id = $(value).attr('data-id');
					ids.push(id);
				}
			});
			return ids;
		}

		function registerCheckCanCheckOut (){
			console.log('register');
			$('.cart-item-checkbox').change(function(event){
				var ids = findCheckedIds();
				
					$.ajax({
						url: '<?= Url::to(['/cart/cart/check-can-check-out']) ?>',
						data: {
							ids : ids,
						},
						dataType : 'json',
						success : function(data, textStatus, xhr){
							$checkoutBtn = $('.next.btn');
							$requestQuotationBtn = $('.next2.btn');
							
							$checkoutBtn.attr('disabled' , !data.checkout);
							$requestQuotationBtn.attr('disabled' , !data.requestQuotation);
						},
					});
			})
		}
		var settings = <?= json_encode($this->context->options) ?>;
		var $list = $('#<?=$this->context->id ?>');
		cart = new Cart(settings, $list);

	})(jQuery);
<?php $this->endBlock() ?>
<?php $this->registerJs($this->blocks['script']); ?>
<?php $form = ActiveForm::begin(); ?>
		
<table id="<?= $this->context->id ?>" class="table table-hover table-condensed">
	<thead>
		<tr>
			<th style="width:50%">Product</th>
			<th style="width:10%">Price</th>
			<th style="width:8%">Quantity</th>
			<th style="width:22%" class="text-center">Subtotal</th>
			<th style="width:10%"></th>
		</tr>
	</thead>
	<tbody>
		<?php if(Yii::$app->request->post('cart-item')): ?>
			<?php foreach ($ids as $value): ?>
				<?= $form->field($cart, 'remark')->label() ?>
				<?= Html::hiddenInput('cart-item[]', $value) ?>
			<?php endforeach; ?>
		<?php endif ?>
		<?php foreach ($selectedCartItems as $item): ?>
		<tr data-item="<?= $item->id ?>">
			<td data-th="Product">
			<div class="row cart-item" able-checkout="<?= $item->isAbleToCheckout && $item->status != 10 ? 1 : 0 ?>"?>
					<?php /*
					<?php if (isset($item->item)): ?>
						<?= $item->item->uniqueHashId ?>
					<?php endif; ?>
					*/?>
					<div class="col-sm-2 hidden-xs">
						<?php if (true): ?>
							<img src="<?= $item->image ?>" alt="..." class="img-responsive"/>
						<?php else: ?>
							<img src="http://placehold.it/100x100" alt="..." class="img-responsive"/>
						<?php endif; ?>
					</div>
					<div class="col-sm-10">
						<?php if ($this->context->checkbox): ?>
							<?= Html::checkbox('cart-item[]', false, ['value' => $item->id,
							'data-id' => $item->id, 'class' => 'cart-item-checkbox']) ?>
						<?php else: ?>
							<?= Html::hiddenInput('cart-item[]', $item->id) ?>
						<?php endif; ?>
						<h4 class="nomargin"><a href="<?= $item->getUrl() ?>" data-pjax="0"><?= $item->name ?></a></h4>
						
						<?= $cart->getDescriptionForCartItem($item) ?>
						
						<?php if (isset($item->remark)): ?>
							<p>Remark: <?= $item->remark ?></p>
						<?php endif; ?>
					</div>
				</div>
			</td>
			<td data-th="Price">
				<?php if ($item->unitPrice != $item->discountedUnitPrice): ?>
					<strike><?= $item->unitPrice ?></strike>
				<?php endif; ?>
				
				<?= $item->discountedUnitPrice ?>
			</td>
			<td data-th="Quantity">
				<?php if ($this->context->editable && !$item->is_locked): ?>
					<input data-field="quantity" type="number" class="form-control text-center" value="<?= $item->quantity ?>">
				<?php else: ?>
					<?= $item->quantity ?>
				<?php endif; ?>
			</td>
			<td data-th="Subtotal" class="text-right">
				<?= $item->netTotal ?>
				<?php if (YII_DEBUG): ?>
					<p>Cart Item ID: <?= $item->id ?></p>
					<p>Status: <?= $item->status ?></p>

					<?php if ($item->status != $item::CODE_QUOTATION): ?>
						<span class="<?= $item->isAbleToCheckout ? 'label label-success' : 'label label-danger' ?>">
							<?= $item->isAbleToCheckout ? 'OK' : 'Cannot Checkout' ?>
						</span> &nbsp;
					<?php else: ?>
						<span class="label label-info"><?= 'Quotation ' ?></span>
					<?php endif; ?>
					<pre style="text-align: left;">
						<?= print_r($item->data, 1) ?>
					</pre>
					<pre style="text-align: left;">
						<?= print_r($item->errors, 1) ?>
					</pre>
				<?php endif ?>
			</td>
			<td class="actions" data-th="">
				<?php if ($this->context->editable): ?>
					<button data-action='delete' class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i></button>		
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<?php foreach ($this->context->summary as $attribute): ?>
			<tr class="visible-xs">
				<td class="text-center"><strong><?= $this->context->renderSummaryLabelCellContent($cart, $attribute) ?> <?= $this->context->renderSummaryValueCellContent($cart, $attribute) ?></strong></td>
			</tr>
		<?php endforeach; ?>
		
		<?php foreach ($this->context->summary as $attribute): ?>
			<tr>
				<td></td>
				<td colspan="2" class="hidden-xs text-right"><?= $this->context->renderSummaryLabelCellContent($cart, $attribute) ?> </td>
				<td class="hidden-xs text-right"><strong><?= $this->context->renderSummaryValueCellContent($cart, $attribute) ?></strong></td>
				<td></td>
			</tr>
		<?php endforeach; ?>
	</tfoot>
</table>
	<?php if ($this->context->editable): ?>
		<?= $form->field($cart, 'remark')->label() ?>
	<?php else: ?>
		<div><?= Html::activeLabel($cart, 'remark') ?></div>
		<?= $cart->remark ?>
	<?php endif ?>
<div class="row">
	<div class="col-md-6">
		<?= $this->context->renderPrev() ?>
	</div>
	<div class="col-md-6 text-right">
		<?php if($this->context->buttonNext2): ?>
			<?= $this->context->renderNext2() ?>
		<?php endif ?>
		<?= $this->context->renderNext() ?>
	</div>
</div>
<?php ActiveForm::end(); ?>
