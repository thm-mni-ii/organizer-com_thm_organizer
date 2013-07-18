"use strict";
window.addEvent('domready', function()
{
    document.formvalidator.setHandler('resourceName',
        function (value)
        {
            return (/[\#\;]/).test(value);
        });
});