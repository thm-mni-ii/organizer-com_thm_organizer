window.addEvent('domready', function() {
    document.formvalidator.setHandler('resourceName',
        function (value) {
                regex=/[\#\;]/;
                return !regex.test(value);
    });
});