window.addEvent('domready', function()
{
    document.formvalidator.setHandler('germandate',
        function (value)
        {
            "use strict";
            return (/^[0-3][0-9].[0-1][0-9].[0-9]{4}$/).test(value);
        });
});

