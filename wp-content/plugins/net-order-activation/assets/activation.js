/* global jQuery, NOA_Settings */
(function ($) {
	'use strict';

	// URL'deki ?code= parametresini oku ve aktivasyon formundaki alana yaz.
	$(function () {
		if (typeof URLSearchParams === 'undefined') {
			return;
		}

		var urlParams = new URLSearchParams(window.location.search);
		var codeFromUrl = urlParams.get('code');

		if (codeFromUrl) {
			$('#noa_activation_code').val(codeFromUrl);
		}
	});

	function showStep(stepId) {
		$('.noa-step').removeClass('is-active');
		$(stepId).addClass('is-active');
	}

	function renderOrderSummary(data) {
		var $summary = $('#noa-order-summary');
		var html = '';

		html += '<div class="noa-order-card">';
		if (data.product_name) {
			html += '<p><strong>' + $('<div>').text(data.product_name).html() + '</strong></p>';
		}
		if (data.dimensions) {
			html += '<p>' + $('<div>').text(data.dimensions).html() + '</p>';
		}
		if (data.order_date) {
			html += '<p>' + $('<div>').text(data.order_date).html() + '</p>';
		}
		html += '</div>';

		$summary.html(html);
	}

	$(document).on('submit', '#noa-activation-form', function (e) {
		e.preventDefault();

		var $form = $(this);
		var $message = $('#noa-activation-message');

		$message.removeClass('noa-error noa-success').text('');

		var data = {
			action: 'noa_verify_activation',
			nonce: NOA_Settings.nonce,
			activation_code: $.trim($('#noa_activation_code').val()),
			phone_number: $.trim($('#noa_phone_number').val())
		};

		$form.addClass('is-loading');

		$.post(NOA_Settings.ajax_url, data)
			.done(function (response) {
				if (!response || !response.success) {
					$message.addClass('noa-error').text(response && response.data && response.data.message ? response.data.message : NOA_Settings.i18n.unknown_error);
					return;
				}

				renderOrderSummary(response.data);
				$('#noa_order_token').val(response.data.order_token);
				$('#noa-account-form').data('order-id', response.data.order_id);
				$('#noa-account-form').data('activation-code', data.activation_code);
				$('#noa-account-form').data('phone-number', data.phone_number);

				showStep('#noa-step-order');
			})
			.fail(function () {
				$message.addClass('noa-error').text(NOA_Settings.i18n.unknown_error);
			})
			.always(function () {
				$form.removeClass('is-loading');
			});
	});

	$(document).on('click', '#noa-go-to-account', function () {
		showStep('#noa-step-account');
	});

	$(document).on('submit', '#noa-account-form', function (e) {
		e.preventDefault();

		var $form = $(this);
		var $message = $('#noa-account-message');

		$message.removeClass('noa-error noa-success').text('');

		var orderId = $form.data('order-id');
		var activationCode = $form.data('activation-code');
		var phoneNumber = $form.data('phone-number');

		var data = {
			action: 'noa_create_user_and_link_order',
			nonce: NOA_Settings.nonce,
			name: $.trim($('#noa_name').val()),
			email: $.trim($('#noa_email').val()),
			password: $('#noa_password').val(),
			privacy: $form.find('input[name="privacy"]').is(':checked') ? 1 : 0,
			order_id: orderId,
			activation_code: activationCode,
			phone_number: phoneNumber,
			order_token: $('#noa_order_token').val()
		};

		$form.addClass('is-loading');

		$.post(NOA_Settings.ajax_url, data)
			.done(function (response) {
				if (!response || !response.success) {
					$message.addClass('noa-error').text(response && response.data && response.data.message ? response.data.message : NOA_Settings.i18n.unknown_error);
					return;
				}

				$message.addClass('noa-success').text(response.data.message || '');
				showStep('#noa-step-success');

				if (response.data.redirectTo) {
					setTimeout(function () {
						window.location.href = response.data.redirectTo;
					}, 3000);
				}
			})
			.fail(function () {
				$message.addClass('noa-error').text(NOA_Settings.i18n.unknown_error);
			})
			.always(function () {
				$form.removeClass('is-loading');
			});
	});
})(jQuery);

