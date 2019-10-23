<?php
namespace ant\cart\widgets;

class CartListAsset extends \yii\web\AssetBundle {
    public $depends = [
        'yii\web\JqueryAsset',
    ];
    public $sourcePath = '@ant/cart/widgets/public';
    public $js = ['js/cartList.js'];
}
