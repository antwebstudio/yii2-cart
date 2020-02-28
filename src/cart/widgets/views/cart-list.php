<?php

use ant\helpers\Currency;
use ant\helpers\StringHelper as Str;
use ant\widgets\JsBlock;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

\ant\cart\widgets\CartListAsset::register($this);

?>
<?php // Desktop Version ?>		
<table id="<?= $this->context->id ?>" class="d-sm-block d-none table table-hover table-condensed">
	<thead>
		<tr>
			<th style="width:50%"><?= Yii::t('cart', 'Product') ?></th>
			<th style="width:10%"><?= Yii::t('cart', 'Price') ?></th>
			<th style="width:8%"><?= Yii::t('cart', 'Quantity') ?></th>
			<?= $this->context->renderColumnsHeader() ?>
			<th style="width:22%" class="text-center"><?= Yii::t('cart', 'Subtotal') ?></th>
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
					<div class="col-sm-2 d-none d-md-block">
						<img src="<?= Str::default($item->image, 'http://placehold.it/100x100') ?>" class="img-responsive img-fluid"/>
					</div>
					<div class="col-sm-10">
						<?= $this->context->renderCheckbox($item) ?>
						
						<h4 class="nomargin"><a href="<?= $item->url ?>" data-pjax="0"><?= $item->name ?></a></h4>
						
						<?= $cart->getDescriptionForCartItem($item) ?>
				
						<?= $this->render('_debug-cart-list', ['item' => $item]) ?>
						
						<?php /*
						<?php if (isset($item->remark)): ?>
							<p>Remark: <?= $item->remark ?></p>
						<?php endif ?>
						*/?>
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
			<tr>
				<td></td>
				<td colspan="<?= $this->context->summary->colspan ?>" class="hidden-xs text-right"><?= $this->context->renderSummaryLabelCellContent($cart, $attribute) ?> </td>
				<td class="text-right"><strong><?= $this->context->renderSummaryValueCellContent($cart, $attribute) ?></strong></td>
				<td></td>
			</tr>
		<?php endforeach ?>
	</tfoot>
</table>

<?php // Mobile version ?>

<div class="d-sm-none mb-3">
	<?php foreach ($cart->selectedCartItems as $index => $item): ?>
		<div class="cart-row my-1 py-1">
			<div class="row  my-1 py-1">
				<div class="col-5">
					<?= $this->context->renderCheckbox($item) ?>
					<img src="<?= Str::default($item->image, 'http://placehold.it/100x100') ?>" class="img-responsive img-fluid"/>
				</div>
				<div class="col-7">
					<h4 class="nomargin"><a href="<?= $item->url ?>" data-pjax="0"><?= $item->name ?></a></h4>
					
					<?= $cart->getDescriptionForCartItem($item) ?>
			
					<?php /*
					<?= $this->render('_debug-cart-list', ['item' => $item]) ?>
					*/?>
					
					<?php /*
					<?php if (isset($item->remark)): ?>
						<p>Remark: <?= $item->remark ?></p>
					<?php endif ?>
					*/?>
					
					<?php if ($this->context->editable): ?>
						<button data-action='delete' class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>		
					<?php endif ?>
				</div>
			</div>
			<div class="row  my-1 py-1">
				<div class="col-4">
					<label class="d-block">Price</label>
					<?php if ($item->unitPrice != $item->discountedUnitPrice): ?>
						<strike><?= Yii::$app->formatter->asCurrency($item->unitPrice) ?></strike>
					<?php endif ?>
					
					<?= Yii::$app->formatter->asCurrency($item->discountedUnitPrice) ?>
				</div>
				<div class="col-4">
					<label class="d-block text-center">Quantity</label>
					<?php if ($this->context->editable && !$item->is_locked): ?>
						<input data-field="quantity" type="number" class="form-control text-center" value="<?= $item->quantity ?>">
					<?php else: ?>
						<?= $item->quantity ?>
					<?php endif ?>
				</div>
				<div class="col-4 text-right">
					<label class="d-block"><?= Yii::t('cart', 'Total') ?></label>
					<?= Yii::$app->formatter->asCurrency($item->netTotal) ?>
				</div>
				<div class="col my-2 py-2">
					<label class="d-block"><?= Yii::t('cart', 'Attachment') ?></label>
					<?= $this->context->renderColumns($item, $item->id, $index) ?>
				</div>
			</div>
		</div>
	<?php endforeach ?>
	<?php foreach ($this->context->summary->rows as $attribute): ?>
		<div class="row">
			<div class="col-7">
				<?= $this->context->renderSummaryLabelCellContent($cart, $attribute) ?>
			</div>
			<div class="col-5 text-right">
				RM <?= $this->context->renderSummaryValueCellContent($cart, $attribute) ?>
			</div>
		</div>
	<?php endforeach ?>
</div>
		

<?php if ($this->context->editable): ?>
	<?= $form->field($cart, 'remark')->label() ?>
<?php else: ?>
	<div><?= Html::activeLabel($cart, 'remark') ?></div>
	<?= $cart->remark ?>
<?php endif ?>

<div class="row">
	<div class="col-sm-6">
		<?= $this->context->renderPrev() ?>
	</div>
	<div class="col-sm-6 text-right">
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
