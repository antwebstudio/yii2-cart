<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use common\modules\securityMobileApp\models\Customer;

/* @var $this yii\web\View */
/* @var $model common\modules\incident\models\Incident */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="incident-form">

    <?php $form = ActiveForm::begin(); ?>
	
    <?= $form->field($model, 'attachments')->widget(
        \trntv\filekit\widget\Upload::className(),
        [
            'url' => ['/file/file-storage-item/upload'],
            'sortable' => true,
            'maxFileSize' => 10000000, // 10 MiB
            'maxNumberOfFiles' => 10
        ]);
    ?>
    <?= $form->field($model, 'attachments2')->widget(
        \trntv\filekit\widget\Upload::className(),
        [
            'url' => ['/file/file-storage-item/upload'],
            'sortable' => true,
            'maxFileSize' => 10000000, // 10 MiB
            'maxNumberOfFiles' => 10
        ]);
    ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
		<?= Html::a('Back', ['/cart/cart'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
