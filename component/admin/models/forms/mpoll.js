window.addEvent('domready', function() {
	document.formvalidator.setHandler('mpoll',
		function (value) {
			regex=/^[^0-9]+$/;
			return regex.test(value);
	});
});

