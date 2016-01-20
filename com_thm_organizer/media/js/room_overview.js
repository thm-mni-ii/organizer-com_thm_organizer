/* global $, hideFilterText, showFilterText*/

function toggleFilters()
{
    "use strict";
    var toggleContainer = jQuery("#additional-filters-container"),
        toggleIcon = jQuery("#toggle-icon"),
        toggleText = jQuery("#toggle-text");

    if (toggleContainer.hasClass('toggle-closed'))
    {
        toggleContainer.removeClass('toggle-closed');
        toggleContainer.addClass('toggle-open');
        toggleIcon.removeClass('icon-plus-2');
        toggleIcon.addClass('icon-cancel-2');
        toggleText.html(hideFilterText);
    }
    else
    {
        toggleContainer.removeClass('toggle-open');
        toggleContainer.addClass('toggle-closed');
        toggleIcon.removeClass('icon-cancel-2');
        toggleIcon.addClass('icon-plus-2');
        toggleText.html(showFilterText);
    }
}

function showPostLoader() {
    $("body").append('<div class="loading-background"></div>');
    $("body").append('<div class="postloader">Loading</div>');
}

Array.prototype.diff = function(oldValues) {
    return this.filter(function(newValues) { return newValues.indexOf(oldValues) < 0; });
};

function cleanSelection (elementID, container)
{
    var selectBox = jQuery('#' + elementID),
        selectedValues = selectBox.val(),
        allIndex,
        newValues;

    newValues = selectedValues.diff(window[container]);

    // All index is new
    allIndex = newValues.indexOf('-1');
    if (allIndex >= 0)
    {
        selectedValues = ["-1"];
        window[container] = selectedValues;
        selectBox.val(selectedValues);
        return;
    }

    allIndex = selectedValues.indexOf('-1');
    if (allIndex >= 0)
    {
        selectedValues.splice(allIndex, 1);
    }

    window[container] = selectedValues;
    selectBox.val(selectedValues);
}