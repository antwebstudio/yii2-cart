<?php
namespace frontend\modules\cart\widgets;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\modules\ecommerce\models\AddToCartForm;

class AddToCartButton extends \yii\base\Widget {
	
	const TYPE_ORDER = AddToCartForm::TYPE_ORDER;
	const TYPE_QUOTE = AddToCartForm::TYPE_QUOTE;
	const TYPE_CHECK_PRICE = AddToCartForm::TYPE_CHECK_PRICE;
	
	public $productId;
	public $label = 'Add To Cart';
	public $type = AddToCartForm::TYPE_ORDER;
	public $url = ['/cart/cart/ajax-add-item'];
	public $removeUrl = ['/cart/cart/ajax-remove-item'];
	public $getCartUrl = ['/cart/cart/ajax-get-cart'];
	
	public $buttonOptions = [];
	public $clientEvents = [];
	public $clientEventMap = [];
	
	public function init() {
		$this->registerClientEvents($this->id);
		
		$this->view->registerJs('
			jQuery(function($) {
				var url = {
					getCart: "'.Url::to($this->getCartUrl).'",
					addItem: "'.Url::to($this->url).'",
					removeItem: "'.Url::to($this->removeUrl).'"
				};
				
				$.get(url.getCart).done(function(data) {
					cart = data;
					initAllButtons(cart);
				});
				
				function checkIfProductAdded(cart, productId) {
					for (i in cart.cartItems) {
						if (cart.cartItems[i].item_id == productId) {
							return true;
						}
					}
					return false;
				}
				
				function initAllButtons(cart) {
					$("[data-product]").each(function() {
						var productId = $(this).data("product");
						var self = $(this);
						
						if (checkIfProductAdded(cart, productId)) {
							self.trigger("added");
						}
					});
				}
				
				function updateButton(button) {
					//$(button).text("Checkout");
					$(button).addClass("btn-warning");
					$(button).data("action", "remove");
				}
				
				function revertButton(button) {
					$(button).removeClass("btn-warning");
					$(button).data("action", "");
				}
				
				$("[data-product]").each(function() {
					var data = $(this).data();
					var self = $(this);
					
					$(this).click(function() {
						if (self.data("action") == "remove") {
							self.trigger("removed");
							
							$.post(url.removeItem, data).fail(function() {
								self.trigger("added");
							}).done(function() {
								self.trigger("done");
							});
							console.log("remove");
						} else {
							self.trigger("added");
							
							$.post(url.addItem, data).fail(function() {
								self.trigger("removed");
							}).done(function() {
								self.trigger("done");
							});
						}
					});
				});
			});
		');
	}
	
	protected function registerClientEvents($id)
    {
        if (!empty($this->clientEvents)) {
            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
                if (isset($this->clientEventMap[$event])) {
                    $eventName = $this->clientEventMap[$event];
                } else {
                    $eventName = strtolower($event);
                }
                $js[] = "jQuery('#$id').on('$eventName', $handler);";
            }
            $this->getView()->registerJs(implode("\n", $js));
        }
    }
	
	public function run() {
		$buttonOptions = ArrayHelper::merge([
			'id' => $this->id,
			'class' => 'btn btn-primary',
		], $this->buttonOptions);
		
		$buttonOptions = ArrayHelper::merge($buttonOptions, [
			'data-product' => $this->productId,
			'data-type' => $this->type,
		]);
		
		return Html::a($this->label, 'javascript:;', $buttonOptions);
	}
}