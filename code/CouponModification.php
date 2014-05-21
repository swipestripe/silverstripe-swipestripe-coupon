<?php

class CouponModification extends Modification {

	private static $has_one = array(
		'Coupon' => 'Coupon'
	);

	private static $defaults = array(
		'SubTotalModifier' => false,
		'SortOrder' => 200
	);

	public function add($order, $value = null) {

		//Get valid coupon for this order
		if($value !== null){
			$code = Convert::raw2sql($value);	
		}else{
			$code = Convert::raw2sql($order->CouponCode);		
		}
		
		$date = date('Y-m-d');
		$coupon = Coupon::get()
			->where("\"Code\" = '$code' AND \"Expiry\" >= '$date'")
			->first();

		if ($coupon && $coupon->exists()) {

			//Generate the Modification
			$mod = new CouponModification();
			$mod->Price = $coupon->Amount($order)->getAmount();
			$mod->Currency = $coupon->Currency;
			$mod->Description = $coupon->Label();
			$mod->OrderID = $order->ID;
			$mod->Value = $coupon->ID;
			$mod->CouponID = $coupon->ID;
			$mod->write();
		}
	}

	public function getFormFields() {

		$fields = new FieldList();

		$coupon = $this->Coupon();
		if ($coupon && $coupon->exists()) {

			$field = CouponModifierField::create($this, $coupon->Label(), $coupon->Code)
				->setAmount($coupon->Price($this->Order()));

			$fields->push($field);
		}

		return $fields;
	}
}
