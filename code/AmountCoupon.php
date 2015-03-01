<?php
/**
 * Coupon rates that can be set in {@link SiteConfig}. Several flat rates can be set 
 * for any supported shipping country.
 */
class AmountCoupon extends Coupon {
	
	private static $singular_name = 'Coupon - Amount';
	private static $plural_name = 'Coupons - Amount';
	
	private static $db = array(
		'DiscountAmount' 	=> 'Decimal(19,8)',
		'OrderOver' 		=> 'Decimal(19,8)'		// this coupon can be applied if order over $xx.xx
	);
	
	private static $summary_fields = array(
		'Title' 			=> 'Title',
		'Code' 				=> 'Code',
		'DiscountAmount' 	=> 'Discount Amount',
		'StartDate' 		=> 'Start Date',
		'Expiry' 			=> 'Expiry'
	);

	/**
	 * Field for editing a {@link Coupon}.
	 * 
	 * @return FieldSet
	 */
	public function getCMSFields() {

		return new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('CouponRate',
					TextField::create('Title', _t('Coupon.TITLE', 'Title')),
					TextField::create('Code', _t('Coupon.CODE', 'Code')),
					PriceField::create('DiscountAmount', 'Coupon discount amount'),
					PriceField::create('OrderOver', 'Coupon condition')
						->setRightTitle('This coupon is valid, if order sub total over this condition amount. This value should larger than discount amount. If condition is less than discount amount, it will be automatically set as discount amount.'),
					DateField::create('StartDate')
						->setRightTitle('Leave it blank if start date is not required')
						->setConfig('showcalendar', true),
					DateField::create('Expiry')
						->setConfig('showcalendar', true)
				)
			)
		);
	}
	
	public function onBeforeWrite(){
		parent::onBeforeWrite();
		
		if($this->DiscountAmount && ($this->DiscountAmount > $this->OrderOver) ){
			$this->OrderOver = $this->DiscountAmount;
		}
	}
	
	/**
	 * Label for using on {@link CouponModifierField}s.
	 * 
	 * @see CouponModifierField
	 * @return String
	 */
	public function Label() {
		return $this->Title;
	}
	
	public function Amount($order) {

		// TODO: Multi currency

		$shopConfig = ShopConfig::current_shop_config();

		$amount = new Price();
		$amount->setCurrency($shopConfig->BaseCurrency);
		$amount->setSymbol($shopConfig->BaseCurrencySymbol);

		$amount->setAmount(- $this->DiscountAmount);

		return $amount;
	}

	/**
	 * Display price, can decorate for multiple currency etc.
	 * 
	 * @return Price
	 */
	public function Price($order) {
		
		$amount = $this->Amount($order);
		$this->extend('updatePrice', $amount);
		return $amount;
	}
	
	
	public function CouponConditionPrice() {
	
		$shopConfig = ShopConfig::current_shop_config();

		$amount = new Price();
		$amount->setCurrency($shopConfig->BaseCurrency);
		$amount->setSymbol($shopConfig->BaseCurrencySymbol);

		$amount->setAmount(- $this->OrderOver);

		return $amount;
	}
	
}
