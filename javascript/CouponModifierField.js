(function($) {
	$(document).ready(function() {
		$('#apply-coupon-js').live('click', function() {

			$('.order-form').entwine('sws').updateCart();

			$.ajax({
				url: window.location.pathname + '/checkcoupon',
				type: 'POST',
				data: $('.order-form').serialize(),
				success: function(data){

					var dataObj = $.parseJSON(data),
						$couponMessageHolder = $('#CouponCode .message');

					$couponMessageHolder.html(dataObj.errorMessage);
					if (dataObj.errorMessage) {
						$couponMessageHolder.addClass('required').removeClass('hide');
					}
					else {
						$couponMessageHolder.removeClass('required').addClass('hide');
					}
					
					$('.order-form').entwine('sws').updateCart();
				}
			});
		});
	});
})(jQuery);