<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use ant\cart\widgets\CartList;

//$this->context->layout = '//main';

$this->params['content-header-buttons'][] = Html::a('Back', ['/cart/backend'], ['class' => 'btn btn-sm btn-primary btn-labeled']);
?>

<div class="row">
	<div class="col-md-6">
		<h3>Customer</h3>
		<?php if (isset($model->createdBy)): ?>
			<p>Email: <?= $model->createdBy->email ?></p>
			<p>Contact Number: <?= $model->createdBy->profile->contact ?></p>
		<?php else: ?>
			<p>Unknown</p>
		<?php endif; ?>
	</div>

	<div class="col-md-6">
		<h3>Cart</h3>
		<p>Status: <?= $model->statusText ?></p>
		<p>Updated At: <?= $model->getRelativeTime('updated_at') ?></p>
	</div>
</div>

<?php $pjax = Pjax::begin(['timeout' => 1000]) ?>
<div class="cart-default-view">
	<h3>Items</h3>
    <?= CartList::widget([
		'cart' => $model,
		'editable' => false,
		'prevLayout' => '',
		'nextLayout' => '',
	]) ?>
</div>
<?php Pjax::end() ?>

<?php 
	$miliseconds = $model->isActive ? 5000 : 3600 * 1000;
	$this->registerJs(' 
		setInterval(function(){  
			 $.pjax.reload({container: "#'.$pjax->id.'"});
		}, '.$miliseconds.');', \yii\web\View::POS_HEAD
	); 
?>