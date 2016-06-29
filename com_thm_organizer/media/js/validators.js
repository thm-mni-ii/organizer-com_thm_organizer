window.addEvent('domready', function ()
{
    "use strict";
    document.formvalidator.setHandler('ip',
        function (value)
        {
            return (/^[0-2]*[0-9]*[0-9].[0-2]*[0-9]*[0-9].[0-2]*[0-9]*[0-9].[0-2]*[0-9]*[0-9]$/).test(value);
        });
    document.formvalidator.setHandler('select', function (value)
    {
        return (value != 0);
    });
});