<?php
namespace ant\cart\widgets;

use yii\helpers\Html;
use ant\file\models\FileAttachment;

class CartItemAttachmentColumn extends \yii\grid\DataColumn {
	public $uploadUrl = '/cart/cart-item/attachment';
	
	protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return parent::renderDataCellContent($model, $key, $index);
        }
		
		$files = [];
		foreach ($model->{$this->attribute} as $file) {
			$files[] = Html::a(substr($file['name'], 0, 20).'...', FileAttachment::getUrl($file), ['target' => '_blank']);
		}
		$button = $this->uploadUrl !== false ? Html::a('Upload', [$this->uploadUrl, 'id' => $model->id], ['class' => 'btn btn-sm btn-secondary']) : '';
		return implode('', $files).$button;
    }
}