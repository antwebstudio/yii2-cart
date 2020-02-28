<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use ant\file\widgets\Upload;
use ant\securityMobileApp\models\Customer;

/* @var $this yii\web\View */
/* @var $model ant\incident\models\Incident */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="incident-form">

    <?php $form = ActiveForm::begin(); ?>
	
    <?= $form->field($model, 'attachments')->widget(
        Upload::className(),
        [
            'url' => ['upload'],
            'sortable' => true,
            'maxFileSize' => 10000000, // 10 MiB
            'maxNumberOfFiles' => 10
        ]);
    ?>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('cart', $model->isNewRecord ? 'Create' : 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
		<?= Html::a(Yii::t('cart', 'Back'), ['/cart/cart'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end() ?>

</div>
