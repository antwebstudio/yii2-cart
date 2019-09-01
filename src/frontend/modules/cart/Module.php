<?php

namespace frontend\modules\cart;

/**
 * cart module definition class
 */
class Module extends \common\modules\cart\Module
{
    /**
     * @inheritdoc
     */
	public $pageTitles;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
	
	public function getPageTitle($controller, $default = null) {
		if (isset($this->pageTitles[$controller->route])) {
			return $this->pageTitles[$controller->route];
		}
		return $default;
	}
}
