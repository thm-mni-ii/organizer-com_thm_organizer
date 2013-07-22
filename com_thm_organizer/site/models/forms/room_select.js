window.addEvent('domready', function()
{
    "use strict";
    document.formvalidator.setHandler('germandate',
        function (value)
        {
            return (/^[0-3][0-9].[0-1][0-9].[0-9]{4}$/).test(value);
        });
});
