jQuery(document).ready(function () {
    'use strict';
    document.formvalidator.setHandler('ip',
        function (value) {
            return (/^[0-2]*[0-9]*[0-9].[0-2]*[0-9]*[0-9].[0-2]*[0-9]*[0-9].[0-2]*[0-9]*[0-9]$/).test(value);
        }
    );

    document.formvalidator.setHandler('german-address',
        function (value) {
            return (/^([a-zA-ZäöüÄÖÜß0-9\-]+ *)+$/).test(value);
        }
    );

    document.formvalidator.setHandler('gps',
        function (value) {
            return (/^[0-9]{1,2}.[0-9]{6},\s*[0-9]{1,2}.[0-9]{6}$/).test(value);
        }
    );

    document.formvalidator.setHandler('select', function (value) {
        return (value !== 0);
    });
});