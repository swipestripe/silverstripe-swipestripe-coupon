<?php
/**
 * Default settings.
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage admin
 */

//Extensions
Object::add_extension('ShopConfig', 'Coupon_Extension');
Object::add_extension('Order', 'Coupon_OrderExtension');
Object::add_extension('OrderForm', 'Coupon_CheckoutFormExtension');
Object::add_extension('CheckoutPage_Controller', 'CouponModifierField_Extension');

if (class_exists('ExchangeRate_Extension')) {
	Object::add_extension('Coupon', 'ExchangeRate_Extension');
}