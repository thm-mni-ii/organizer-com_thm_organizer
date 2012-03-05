window.addEvent('domready', function() {
    document.formvalidator.setHandler('ip',
        function (value) {
                regex=/^[0-2][0-9][0-9].[0-2][0-9][0-9].[0-2][0-9][0-9].[0-2][0-9][0-9]$/;
                return regex.test(value);
    });
});