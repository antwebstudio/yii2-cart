<?php
namespace common\modules\cart\components;

interface CartableInterface {
	// @return \common\modules\discount\helpers\Discount
	// @return 0 if no discount
	public function getDiscount();
	
	public function getName();
	
	// Unit price before discount
	public function getPrice();
	
	public function getUniqueHashId();
	
	public function getId();
	
	public function getCartItemCustomData();
	
	public function setCartItemCustomData($data);
	
	// Attribute which should not be require method:
	// remark - should get from AddToCartForm instead of the cartable item
	// discountAmount - should get from getDiscount
}