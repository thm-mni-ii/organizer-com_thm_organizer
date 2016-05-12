/* global jQuery */

function toggleGroupDisplay(containerID)
{
    var container = jQuery(containerID), main = containerID.indexOf('main') !== -1, icon, panelID;

    if (main)
    {
        panelID = containerID.replace('main-', '');
        panelID = panelID.replace('items-', '');
        icon = jQuery(jQuery(panelID + ' div.main-panel-head i')[0]);
    }

    if (container.hasClass('shown'))
    {
        container.removeClass('shown');
        container.addClass('hidden');
        if (main)
        {
            icon.removeClass('icon-minus-2');
            icon.addClass('icon-plus-2');
        }
    }
    else
    {
        container.removeClass('hidden');
        container.addClass('shown');
        if (main)
        {
            icon.removeClass('icon-plus-2');
            icon.addClass('icon-minus-2');
        }
    }
}
