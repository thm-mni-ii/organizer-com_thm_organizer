/*global jq: false, categories: false*/
window.addEvent('domready', function() {
    "use strict";
    document.formvalidator.setHandler('germandate',
        function (value) { return (/^[0-3][0-9].[0-1][0-9].[0-9]{4}$/).test(value); });
});

window.addEvent('domready', function() {
    "use strict";
    document.formvalidator.setHandler('time',
        function (value) { return (/^[0-2]?[0-9]{1}:[0-5]{1}[0-9]{1}$/).test(value); });
});

window.addEvent('domready', function() {
    "use strict";
    document.formvalidator.setHandler('title', function (value) { return value !== ''; });
});

window.addEvent('domready', function() {
    "use strict";
    document.formvalidator.setHandler('category', function (value) { return value > 0; });
});

/**
* Changes a dynamically generated list
*/
function changeCategoryInformation()
{
    "use strict";
    var index = document.getElementById('category').selectedIndex;
    var id = document.getElementById('category').options[index].value;
    document.getElementById('thm_organizer_ee_event_cat_desc_div').innerHTML = categories[id][0];
    document.getElementById('thm_organizer_ee_event_cat_disp_div').innerHTML = categories[id][1];
    document.getElementById('thm_organizer_ee_content_cat_name_div').innerHTML = categories[id][2];
    document.getElementById('thm_organizer_ee_content_cat_desc_div').innerHTML = categories[id][3];
    document.getElementById('thm_organizer_ee_content_cat_access_div').innerHTML = categories[id][4];
}

/**
 * returns the value of the recurrence type input
 */
function getRecType()
{
    "use strict";
    var rec_type = 0;
    for(var i=0; i < document.eventForm.rec_type.length; i++)
    {
        if(document.eventForm.rec_type[i].checked)
        {
            rec_type = document.eventForm.rec_type[i].value;
        }
    }
    return rec_type;
}

/**
 * returns a string containing the resource values selected
 */
function getResources(resourceID)
{
    "use strict";
    var selectedResources = jq(resourceID).val();
    if(typeof selectedResources !== 'undefined'){
        if(jq.isArray(selectedResources))
        {
            selectedResources = selectedResources.join(",");
            return selectedResources;
        }
        if(jq.isNumeric(selectedResources))
        {
            selectedResources = selectedResources.toString();
            return selectedResources;
        }
        else
        {
            return selectedResources;
        }
    }
    else
    {
        return '';
    }
}

/**
 * toggles the value of the checkbox since joomla didnt bother to create standardized
 * js for this form field type
 */
function toggleCheckValue(isitchecked)
{
    "use strict";
    if(isitchecked === true)
    {
        document.eventForm.jform_emailNotification.value++;
    }
    else
    {
        document.eventForm.jform_emailNotification.value--;
    }
}