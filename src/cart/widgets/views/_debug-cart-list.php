
<?php if (YII_DEBUG): ?>
	<?php $id = 'debug_' . $item->id ?>
	
	<a class="btn btn-sm btn-dark" href="#<?= $id ?>" data-toggle="collapse">Debug</a>
	<div id="<?= $id ?>" class="collapse">
		<p>Cart Item ID: <?= $item->id ?></p>
		<p>Status: <?= $item->status ?></p>

		<?php if ($item->status != $item::CODE_QUOTATION): ?>
			<span class="<?= $item->isAbleToCheckout ? 'label label-success badge badge-success' : 'label label-danger badge badge-danger' ?>">
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
	</div>
<?php endif ?>