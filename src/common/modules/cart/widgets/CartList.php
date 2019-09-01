<?php
namespace common\modules\cart\widgets;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\helpers\TemplateHelper;

class CartList extends \yii\base\Widget {
	public $cart;
	public $editable = true;
	public $checkbox = true;
	public $buttonNext2 = false;
	public $layout = '{items}';
	public $prevLayout = '{prev}';
	public $nextLayout = '{next}';
	public $next2Layout = '{next2}';
	public $options = [];
	public $currencySymbol = '';
	public $form;
	
	public $buttons = [];
	
	public $summary = [
		'subtotal', 'discountAmount', 'taxCharges', /*'tax_amount', */
		[
			'attribute' => 'netTotal',
			'label' => 'Total',
			'format' => 'html',
		],
	];
	
	protected $_form;
	
	// Default buttons
	protected $_buttonParams = [
		'prev' => [
			'label' => '<i class="fa fa-angle-left"></i> Continue Shopping',
			'url' => ['/ecommerce/product'],
			'options' => ['class' => 'prev btn btn-default'],
		],
		'next' => [
			'label' => 'Checkout <i class="fa fa-angle-right"></i>', 
			'url' => ['/cart/cart/checkout'],
			'options' => ['class' => 'next btn btn-primary'],
		],
		'next2' => [
			'label' => 'Request Quotation <i class="fa fa-angle-right"></i>', 
			'url' => ['/cart/cart/confirm'],
			'options' => ['class' => 'next2 btn btn-primary'],
		],
	];
	
	protected $_buttonUrls = [];
	
	//public $url = ['/cart/cart/ajax-widget'];
	public $url = '';
	
	public function init() {
		if (!isset($this->cart)) throw new \Exception(get_called_class().' need cart property set. ');
		$this->options['cartType'] = $this->cart->type;
		$this->options['url'] = \yii\helpers\Url::to($this->url);
		
		//$this->buttons = \yii\helpers\ArrayHelper::merge($this->_buttonParams, $this->buttons);
		
		//$this->initDefaultButtons();
		//$this->initButtons();
	}
	
	protected function _renderTemplate($layout) {

		return TemplateHelper::renderTemplate($layout, function($name) use ($layout) {
			$content = $this->renderSection('{'.$name.'}');
			return $content === false ? $name : $content;
		});
	}
	
	public function run() {
		$this->beginForm();
		
		$content = $this->_renderTemplate($this->layout);
		
		/*$content .= TemplateHelper::renderTemplate('{test} asdf', [
			'test' => function() {
				return 'abc';
			}
		]);*/
		
        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        echo Html::tag($tag, $content, $options);
		
		$this->endForm();
	}
	
	protected function beginForm() {
		if (is_array($this->form)) {
			$this->_form = ActiveForm::begin($this->form);
		} else if (!isset($this->form)) {
			$this->_form = ActiveForm::begin();
		} else {
			$this->_form = null;
		}
	}
	
	protected function endForm() {
		if (isset($this->_form)) {
			ActiveForm::end();
		}
	}
	
	protected function renderSection($name)
    {
        switch ($name) {
            //case '{summary}':
            //    return $this->renderSummary();
            case '{items}':
                return $this->renderItems();
            case '{prev}':
                return $this->renderButton('prev');
            case '{next}':
                return $this->renderButton('next');
            case '{next2}':
                return $this->renderButton('next2');
            //case '{pager}':
            //    return $this->renderPager();
            //case '{sorter}':
            //    return $this->renderSorter();
            default:
                return false;
        }
    }
	
	public function renderPrev() {
		return $this->_renderTemplate($this->prevLayout);
	}
	
	public function renderNext() {
		return $this->_renderTemplate($this->nextLayout);
	}

	public function renderNext2() {
		return $this->_renderTemplate($this->next2Layout);
	}
	
	public function renderSummaryLabelCellContent($model, $attribute) {
		$format = isset($attribute['format']) ? $attribute['format'] : 'text';
		
		if (is_array($attribute)) {
		}
		return $this->formatter->format($this->getSummaryCellLabel($model, $attribute), $format);
	}
	
	public function renderSummaryValueCellContent($model, $attribute) {
		$format = isset($attribute['format']) ? $attribute['format'] : 'text';
		
		if (is_array($attribute)) {
            return $this->formatter->format($this->getSummaryCellValue($model, $attribute), $format);
		}
		return $model->{$attribute};
	}
	
	protected function getSummaryCellValue($model, $attribute) {
		return $this->getDataCellValue($model, $attribute);
	}
	
	public function getDataCellValue($model, $column, $index = null)
    {
		$attribute = isset($column['attribute']) ? $column['attribute'] : null;
		$value = isset($column['value']) ? $column['value'] : null;
		
        if ($value !== null) {
            if (is_string($value)) {
                return ArrayHelper::getValue($model, $value);
            } else {
                return call_user_func($value, $model, $attribute, $index, $this);
            }
        } elseif ($attribute !== null) {
            return ArrayHelper::getValue($model, $attribute);
        }
        return null;
    }
	
	protected function getSummaryCellLabel($model, $attribute) {
		if (is_array($attribute)) {
			$column = $attribute;
			
			if (isset($column['label'])) {
				return $column['label'];
			} else if (isset($column['attribute'])) {
				return $model->getAttributeLabel($column['attribute']);
			}
		} else {
			return $model->getAttributeLabel($attribute);
		}
	}
	
	public function renderItems() {
		return $this->render('cart-list', [
			'cart' => $this->cart,
		]);
	}
	
	protected function getFormatter() {
		return \Yii::$app->formatter;
	}
	
	protected function getButtonParam($buttonName, $paramName, $defaultValue = null) {
		if (isset($this->buttons[$buttonName]) && is_array($this->buttons[$buttonName]) && isset($this->buttons[$buttonName][$paramName])) {
			return $this->buttons[$buttonName][$paramName];
		} else if (isset($this->_buttonParams[$buttonName]) && is_array($this->_buttonParams[$buttonName]) && isset($this->_buttonParams[$buttonName][$paramName])) {
			return $this->_buttonParams[$buttonName][$paramName];
		}
		return $defaultValue;
	}
	
	public function renderButton($name) {
		if (isset($this->buttons[$name]) && is_callable($this->buttons[$name])) {
			$params = [$this->getButtonParam($name, 'url')];
			return call_user_func_array($button, $params);
		} else {
			$url = $this->getButtonParam($name, 'url');
			$url = Url::toRoute($url);
			$options = $this->getButtonParam($name, 'options');
			$label = $this->getButtonParam($name, 'label');
			$isSubmitButton = $this->getButtonParam($name, 'isSubmit', false);
			
			return $isSubmitButton ? Html::submitButton($label, $options) : Html::a($label, $url, $options);
		}
	}
	
	/*protected function initButtons() {
		foreach ($this->buttons as $name => $button) {
			if (!is_callable($button)) {
				$this->_buttonUrls[$name] = isset($button['url']) ? $button['url'] : null;
				$options = isset($button['options']) ? $button['options'] : [];
				$label = isset($button['label']) ? $button['label'] : '';
				
				$this->initButton($name, $label, $options);
			}
		}
	}*/
	
	/*protected function initDefaultButtons() {
		foreach ($this->_buttonParams as $name => $button) {
			if (!isset($this->buttons[$name])) {
				$this->initButton($name, $button['label'], $button['options']);
			}
		}
	}*/
	
	/*protected function initButton($name, $label, $options) {
		$this->buttons[$name] = function($url) use ($label, $options) {
			return Html::a($label, $url, $options);
		};
	}*/
}