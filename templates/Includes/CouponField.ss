<div id="$Name" class="field<% if extraClass %> $extraClass<% end_if %>">
	<% if Title %><label class="left" for="$ID">$Title</label><% end_if %>
	<div class="middleColumn">
		$Field
		<input type="button" value="Apply" id="apply-coupon-js" />
	</div>
	<% if RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
	<span class="message $MessageType hide">$Message</span>
</div>