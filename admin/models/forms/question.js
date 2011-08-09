window.addEvent('domready', function() {
	document.formvalidator.setHandler('question',
		function (value) {
			regex=/^[^0-9]+$/;
			return regex.test(value);
	});
});

