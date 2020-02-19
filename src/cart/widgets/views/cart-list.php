<?php

use ant\helpers\Currency;
use ant\helpers\StringHelper as Str;
use ant\widgets\JsBlock;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

\ant\cart\widgets\CartListAsset::register($this);

?>
		
<table id="<?= $this->context->id ?>" class="table table-hover table-condensed">
	<thead>
		<tr>
			<th style="width:50%">Product</th>
			<th style="width:10%">Price</th>
			<th style="width:8%">Quantity</th>
			<?= $this->context->renderColumnsHeader() ?>
			<th style="width:22%" class="text-center">Subtotal</th>
			<th style="width:10%"></th>
		</tr>
	</thead>
	<tbody>
		<?php /*
		<?php if(Yii::$app->request->post('cart-item')): ?>
			<?php foreach ($ids as $value): ?>
				<?= $form->field($cart, 'remark')->label() ?>
				<?= Html::hiddenInput('cart-item[]', $value) ?>
			<?php endforeach ?>
		<?php endif ?>
		*/ ?>
		<?php foreach ($cart->selectedCartItems as $index => $item): ?>
		<tr data-item="<?= $item->id ?>">
			<td data-th="Product">
			<div class="row cart-item" able-checkout="<?= $item->isAbleToCheckout ? 1 : 0 ?>"?>
					<?php /*
					<?php if (isset($item->item)): ?>
						<?= $item->item->uniqueHashId ?>
					<?php endif; ?>
					*/?>
					<div class="col-sm-2 hidden-xs">
						<img src="<?= Str::default($item->image, 'http://placehold.it/100x100') ?>" class="img-responsive img-fluid"/>
					</div>
					<div class="col-sm-10">
						<?= $this->context->renderCheckbox($item) ?>
						
						<h4 class="nomargin"><a href="<?= $item->url ?>" data-pjax="0"><?= $item->name ?></a></h4>
						
						<?= $cart->getDescriptionForCartItem($item) ?>
				
						<?= $this->render('_debug-cart-list', ['item' => $item]) ?>
						
						<?php if (isset($item->remark)): ?>
							<p>Remark: <?= $item->remark ?></p>
						<?php endif ?>
					</div>
				</div>
			</td>
			<td data-th="Price">
				<?php if ($item->unitPrice != $item->discountedUnitPrice): ?>
					<strike><?= $item->unitPrice ?></strike>
				<?php endif ?>
				
				<?= $item->discountedUnitPrice ?>
			</td>
			<td data-th="Quantity">
				<?php if ($this->context->editable && !$item->is_locked): ?>
					<input data-field="quantity" type="number" class="form-control text-center" value="<?= $item->quantity ?>">
				<?php else: ?>
					<?= $item->quantity ?>
				<?php endif ?>
			</td>
			<?= $this->context->renderColumns($item, $item->id, $index) ?>
			<td data-th="Subtotal" class="text-right">
				<?= $item->netTotal ?>
			</td>
			<td class="actions" data-th="">
				<?php if ($this->context->editable): ?>
					<button data-action='delete' class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>		
				<?php endif ?>
			</td>
		</tr>
		<?php endforeach ?>
	</tbody>
	<tfoot>
		<?php foreach ($this->context->summary->rows as $attribute): ?>
			<tr class="visible-xs d-block d-sm-none">
				<td class="text-center"><strong><?= $this->context->renderSummaryLabelCellContent($cart, $attribute) ?> <?= $this->context->renderSummaryValueCellContent($cart, $attribute) ?></strong></td>
			</tr>
		<?php endforeach ?>
		
		<?php foreach ($this->context->summary->rows as $attribute): ?>
			<tr>
				<td></td>
				<td colspan="<?= $this->context->summary->colspan ?>" class="hidden-xs text-right"><?= $this->context->renderSummaryLabelCellContent($cart, $attribute) ?> </td>
				<td class="hidden-xs text-right"><strong><?= $this->context->renderSummaryValueCellContent($cart, $attribute) ?></strong></td>
				<td></td>
			</tr>
		<?php endforeach ?>
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
		<?= $this->context->renderNext() ?>
	</div>
</div>

<?php JsBlock::begin() ?>
<script>
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
							/*$checkoutBtn = $('.next.btn');
							$requestQuotationBtn = $('.next2.btn');
							
							$checkoutBtn.attr('disabled' , !data.checkout);
							$requestQuotationBtn.attr('disabled' , !data.requestQuotation);*/
						},
					});
			})
		}
		var settings = <?= json_encode($this->context->options) ?>;
		var $list = $('#<?=$this->context->id ?>');
		cart = new Cart(settings, $list);

	})(jQuery);
</script>
<?php JsBlock::end() ?>
