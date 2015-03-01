<?php
/**
 * Form field that represents {@link CouponRate}s in the Checkout form.
 */
class CouponModifierField extends ModificationField_Hidden {
	
	/**
	 * The amount this field represents e.g: 15% * order subtotal
	 * 
	 * @var Money
	 */
	protected $amount;

	/**
	 * Render field with the appropriate template.
	 *
	 * @see FormField::FieldHolder()
	 * @return String
	 */
	public function FieldHolder($properties = array()) {
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript('swipestripe-coupon/javascript/CouponModifierField.js');
		return $this->renderWith($this->template);
	}

	/**
	 * Update value of the field according to any matching {@link Modification}s in the 
	 * {@link Order}. Useful when the source options have changed, if a matching option cannot
	 * be found in a Modification then the first option is set at the value (selected).
	 * 
	 * @param Order $order
	 */
	public function updateValue($order, $data) {
		return $this;
	}

	/**
	 * Ensure that the value is the ID of a valid {@link FlatFeeShippingRate} and that the 
	 * FlatFeeShippingRate it represents is valid for the Shipping country being set in the 
	 * {@link Order}.
	 */
	public function validate($validator){

		$valid = true;
		return $valid;

	}
	
	/**
	 * Set the amount that this field represents.
	 * 
	 * @param Money $amount
	 */
	public function setAmount(Money $amount) {
		$this->amount = $amount;
		return $this;
	}
	
	/**
	 * Return the amount for this tax rate for displaying in the {@link CheckoutForm}
	 * 
	 * @return String
	 */
	public function Description() {
		return $this->amount->Nice();
	}

	/**
	 * Shipping field modifies {@link Order} sub total by default.
	 * 
	 * @return Boolean True
	 */
	public function modifiesSubTotal() {
		return false;
	}
}

class CouponModifierField_Extension extends Extension {

	private static $allowed_actions = array (
		'checkcoupon'
	);

	public function updateOrderForm($form) {
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript('swipestripe-coupon/javascript/CouponModifierField.js');
	}

	public function checkcoupon($request) {
		$data = array('errorMessage' => null);
		$code = Convert::raw2sql($request->postVar('CouponCode'));
		$date = date('Y-m-d');
		$coupon = Coupon::get()
			->where("\"Code\" = '$code' AND ((\"StartDate\" IS NULL AND \"Expiry\" >= '$date') OR (\"StartDate\" IS NOT NULL AND \"StartDate\" <= '$date' AND \"Expiry\" >= '$date'))")
			->first();

		if (!$coupon || !$coupon->exists()) {
			$data['errorMessage'] 		= _t('Coupon.COUPON_EXPIRED_INVALID', 'Coupon is invalid or expired.');
			$data['detail']['coupon']	= $code;
			$data['detail']['status']	= 'Invalid';
		}else{
		
			$order = Cart::get_current_order();
			
			if($order && $order->ID){
				//check is there sale item in this order.
				$Items = $order->Items();
				if($Items && $Items->Count()){
					foreach ($Items as $ItemDO){
						$ProductItem = $ItemDO->Product();
						if($ProductItem && $ProductItem->ID && $ProductItem->IsSale()){
							$data['errorMessage'] 		= _t('Coupon.COUPON_SALE_ITEMS','Sorry, coupon codes are not valid for use on sale items.');
							$data['detail']['coupon']	= $code;
							$data['detail']['status']	= 'Invalid';
							return json_encode($data);							
						}
					}
				}
				
				$orderSubTotal = $order->SubTotalPrice()->getAmount();
				
				if($orderSubTotal && isset($coupon->OrderOver) && $coupon->OrderOver > $orderSubTotal){
					$data['errorMessage'] 		= _t('Coupon.COUPON_VALID_OVER', 'Coupon is only valid for order over ') . $coupon->CouponConditionPrice()->Nice();
					$data['detail']['coupon']	= $code;
					$data['detail']['status']	= 'Invalid';
				}
			}
		
		}
		
		if($data['errorMessage'] === null){
			if($coupon->ClassName == 'Coupon'){
				$discount = number_format($coupon->Discount, 2) . '%';
			}else{
				$discount = number_format($coupon->DiscountAmount, 2);
			}
			
			$data['detail']['coupon']	= $code;
			$data['detail']['status']	= 'Valid';
			$data['detail']['name']		= $coupon->Title;
			$data['detail']['discount']	= $discount;
		}
		
		return json_encode($data);
	}
}	