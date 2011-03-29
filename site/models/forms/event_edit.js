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
