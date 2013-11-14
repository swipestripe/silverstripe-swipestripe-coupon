<?php

//Extensions
Object::add_extension('ShopConfig', 'Coupon_Extension');
Object::add_extension('Order', 'Coupon_OrderExtension');
Object::add_extension('OrderForm', 'Coupon_CheckoutFormExtension');
Object::add_extension('CheckoutPage_Controller', 'CouponModifierField_Extension');

if (class_exists('ExchangeRate_Extension')) {
	Object::add_extension('Coupon', 'ExchangeRate_Extension');
}