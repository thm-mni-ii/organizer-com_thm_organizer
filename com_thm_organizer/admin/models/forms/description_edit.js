window.addEvent('domready', function() {
    document.formvalidator.setHandler('gpuntisid',
        function (value) {
    		if (value.trim() != '') {
    			return checkIfGpuntisIdIsUnique(value.trim());
    		} else {
    			return false;
    		}
    });
});

window.addEvent('domready', function() {
    document.formvalidator.setHandler('category',
        function (value) {
    		return value.trim() != '';
    });
});