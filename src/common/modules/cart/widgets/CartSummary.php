<?php
namespace common\modules\cart\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

class CartSummary extends \yii\base\Widget {
	public $cart;
	public $editable = true;
	public $layout = '{items}';
	public $options = [];
	public $clientEvents = [];
	public $currencySymbol = '';
	
	//public $url = ['/cart/cart/ajax-widget'];
	public $url = '';
	
	public function init() {
		$type = null;
		if (!isset($this->cart)) {
			$this->cart = Yii::$app->cart->getLastCart($type);
		}
        
		$this->options['cartType'] = $this->cart->type;
		$this->options['url'] = \yii\helpers\Url::to($this->url);
		$this->options['clientEvents'] = $this->clientEvents;
	}
	
	public function run() {
		$content = preg_replace_callback("/{\\w+}/", function ($matches) {
			$content = $this->renderSection($matches[0]);

			return $content === false ? $matches[0] : $content;
		}, $this->layout);
		
        $options = $this->options;
		ArrayHelper::remove($options, 'clientEvents');
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        echo Html::tag($tag, $content, $options);
	}
	
	protected function renderSection($name)
    {
        switch ($name) {
            //case '{summary}':
            //    return $this->renderSummary();
            case '{items}':
                return $this->renderItems();
            //case '{pager}':
            //    return $this->renderPager();
            //case '{sorter}':
            //    return $this->renderSorter();
            default:
                return false;
        }
    }
	
	public function renderItems() {
		return $this->render('cart-summary', [
			'cart' => $this->cart,
		]);
	}
}