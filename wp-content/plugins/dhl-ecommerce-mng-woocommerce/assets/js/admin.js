(function () {
	'use strict';

	function toggleAuthFields() {
		var selector = document.querySelector('[data-demw-auth-type="1"]');
		if (!selector) {
			return;
		}

		var selected = selector.value;
		var rows = document.querySelectorAll('[data-demw-auth]');
		rows.forEach(function (row) {
			var supported = (row.getAttribute('data-demw-auth') || '').split(' ');
			row.style.display = supported.indexOf(selected) !== -1 ? '' : 'none';
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		toggleAuthFields();
		var selector = document.querySelector('[data-demw-auth-type="1"]');
		if (selector) {
			selector.addEventListener('change', toggleAuthFields);
		}
	});
})();
