<?php
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->params['headerRightPanel'] = ['Home'];
?>

<?php $pjax = Pjax::begin(['timeout' => 1000]) ?>
<div class="cart-default-index">
    <?= GridView::widget([
		'dataProvider' => $dataProvider,
		'columns' => [
			[
				'attribute' => 'created_by',
				'label' => 'Email',
				'value' => function($data) {
					if (isset($data->createdBy)) {
						return $data->createdBy->email;
					}
				},
			],
			[
				'attribute' => 'created_by',
				'label' => 'Contact Number',
				'value' => function($data) {
					if (isset($data->createdBy)) {
						return $data->createdBy->profile->contact;
					}
				},
			],
			//'created_ip',
			[
				'attribute' => 'updated_at',
				'value' => function($data) {
					if (isset($data->updated_at)) {
						return $data->getRelativeTime('updated_at');
					}
				},
			],
			[
				'value' => function($data) {
					return count($data->cartItems);
				},
				'label' => 'Cart Items',
			],
			'itemsTotalQuantity',
			[
				'attribute' => 'status',
				'value' => function($data) {
					return $data->statusText;
				},
			],
			[
				'class' => \yii\grid\ActionColumn::className(),
				'visibleButtons' => [
					'update' => false,
					'delete' => function ($model, $key, $index) {
						return false;
						return !$model->isActive;
					}
				],
			],
		],
	]) ?>
</div>
<?php Pjax::end() ?>

<?php 
	$this->registerJs(' 
		setInterval(function(){  
			 $.pjax.reload({container: "#'.$pjax->id.'"});
		}, 5000);', \yii\web\View::POS_HEAD
	); 
?>