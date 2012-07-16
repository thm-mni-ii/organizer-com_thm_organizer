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
    document.formvalidator.setHandler('title', function (value) { return value != ''; });
});

window.addEvent('domready', function() {
    document.formvalidator.setHandler('category', function (value) { return value > 0; });
});

/**
* Changes a dynamically generated list
*/
function changeCategoryInformation()
{
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
    var resourceObject = document.getElementById(resourceID);
    var selectedResources = new Array();
    var index;
    var count = 0;
    for (index = 0; index < resourceObject.options.length; index++)
    {
        if (resourceObject.options[index].selected) {
            selectedResources[count] = resourceObject.options[index].value;
            count++;
        }
    }
    if(count)return selectedResources.toString();
    else return '';
}

/**
 * toggles the value of the checkbox since joomla didnt bother to create standardized
 * js for this form field type
 */
function toggleCheckValue(isitchecked)
{
    if(isitchecked == true) document.eventForm.jform_emailNotification.value++;
    else document.eventForm.jform_emailNotification.value--;
}

