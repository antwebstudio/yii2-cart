<?php
if (YII_DEBUG) throw new \Exception('DEPRECATED');

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use ant\orders\models\Order;

$this->title = $this->context->module->getPageTitle($this->context, 'My Shopping Cart');
$ids = Yii::$app->request->post('cart-item');
if(!is_array($ids)) {
	$ids = [$ids];
}
?>
<div>
	<?php $form = ActiveForm::begin([
	]) ?>
	<?php if(Yii::$app->request->post('cart-item')): ?>
		<?php foreach ($ids as $value): ?>
			<?= Html::hiddenInput('cart-item[]', $value) ?>
		<?php endforeach; ?>
	<?php else: ?>
	<?php endif ?>

		<?= $form->field($model->getModel('billTo'), '[billTo]firstname') ?>

		<?= $form->field($model->getModel('billTo'), '[billTo]lastname') ?>
		
		<?= $form->field($model->getModel('billTo'), '[billTo]email') ?>
		
		<?= $form->field($model->getModel('billTo'), '[billTo]contact_number') ?>

		<?= $form->field($model->getModel('billTo'), '[billTo]addressString') ?>

		<?= $form->field($model->getModel('shipTo'), '[shipTo]firstname') ?>

		<?= $form->field($model->getModel('shipTo'), '[shipTo]lastname') ?>

		<?= $form->field($model->getModel('shipTo'), '[shipTo]email') ?>

		<?= $form->field($model->getModel('shipTo'), '[shipTo]contact_number') ?>

		<?= $form->field($model->getModel('shipTo'), '[shipTo]addressString') ?>
		
	<?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
	<?php ActiveForm::end() ?>
</div>