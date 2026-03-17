(function ($) {
	'use strict';

	function getPendingText() {
		if (window.wcsCheckoutShippingSummary && window.wcsCheckoutShippingSummary.pendingText) {
			return window.wcsCheckoutShippingSummary.pendingText;
		}

		return 'Adres girildikten sonra hesaplanir';
	}

	function readShippingValue() {
		var $shippingRow = $('#order_review .woocommerce-checkout-review-order-table tfoot tr.shipping').first();
		if (!$shippingRow.length) {
			return null;
		}

		var $amount = $shippingRow.find('.woocommerce-Price-amount').last();
		if ($amount.length) {
			return $('<div/>').append($amount.clone()).html();
		}

		var textValue = $.trim($shippingRow.find('td').text()).replace(/\s+/g, ' ');
		return textValue || null;
	}

	function updateShippingSummary() {
		var $target = $('[data-wcs-checkout-shipping-value]');
		if (!$target.length) {
			return;
		}

		var shippingValue = readShippingValue();
		if (shippingValue) {
			$target.html(shippingValue);
			return;
		}

		$target.text(getPendingText());
	}

	$(document.body).on('updated_checkout', updateShippingSummary);
	$(updateShippingSummary);
})(jQuery);
