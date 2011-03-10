window.addEvent('domready', function() {
    document.formvalidator.setHandler('germandate',
        function (value) {
                regex=/^[0-3][0-9].[0-1][0-9].[0-9]{4}$/;
                return regex.test(value);
    });
});

window.addEvent('domready', function() {
    document.formvalidator.setHandler('time',
        function (value) {
                regex=/^[0-2]?[0-9]{1}:[0-5]{1}[0-9]{1}$/;
                return regex.test(value);
    });
});

window.addEvent('domready', function() {
    document.formvalidator.setHandler('title',
        function (value) { return (trim(value) != '');
    });
});