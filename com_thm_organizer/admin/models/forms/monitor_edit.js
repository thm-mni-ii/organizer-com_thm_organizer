"use strict";
window.addEvent('domready', function()
{
    document.formvalidator.setHandler('ip',
        function (value)
        {
            return (/^[0-2][0-9][0-9].[0-2][0-9][0-9].[0-2][0-9][0-9].[0-2][0-9][0-9]$/).test(value);
        });
});