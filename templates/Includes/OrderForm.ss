<% if IncludeFormTag %>
<form $FormAttributes>
<% end_if %>

	<% if Message %>
		<p id="{$FormName}_error" class="message $MessageType">$Message</p>
	<% else %>
		<p id="{$FormName}_error" class="message $MessageType" style="display: none"></p>
	<% end_if %>
	
	<fieldset>

		<% if Fields(PersonalDetails) %>
		<section class="personal-details">
			<% loop Fields(PersonalDetails) %>
				$FieldHolder
			<% end_loop %>
		</section>
		
		<hr />
		<% end_if %>

		<section class="address">
			<div id="address-shipping">
				<% loop Fields(ShippingAddress) %>
					$FieldHolder
				<% end_loop %>
			</div>
		</section>

		<hr />
	
		<section class="address">
			<div id="address-billing">
				<% loop Fields(BillingAddress) %>
					$FieldHolder
				<% end_loop %>
			</div>
		</section>
		
		<hr />

		<!-- Add coupon fields to the OrderForm template -->
		<section class="coupon">
			<h3><% _t('Coupon.COUPON', 'Coupon') %></h3>
			<% loop CouponFields %>
				$FieldHolder
			<% end_loop %>
		</section>
		<!-- End of coupon fields -->
		
		<hr />
		
		<section class="order-details">
			<h3><% _t('CheckoutForm.YOUR_ORDER', 'Your Order') %></h3>
			<% include CheckoutFormOrder %>
		</section>
		
		<section class="notes">
			<% loop Fields(Notes) %>
				$FieldHolder
			<% end_loop %>
		</section>
		
		<hr />
		
		<section class="payment-details">
			<% loop Fields(Payment) %>
				$FieldHolder
			<% end_loop %>
		</section>

		<div class="clear" />
	</fieldset>

	<% if Cart.Items %>
		<% if Actions %>
		<div class="Actions">
			<div class="loading">
				<img src="swipestripe/images/loading.gif" />
			</div>
			<% loop Actions %>
				$Field
			<% end_loop %>
		</div>
		<% end_if %>
	<% end_if %>
	
<% if IncludeFormTag %>
</form>
<% end_if %>