/**
 * Gets programs, pools and rooms depending on selected department (or program) and inserts them as the only options
 */
jQuery(document).ready(function () {
    'use strict';

    var department = jQuery('#jform_params_departmentID'),
        category = jQuery('#jform_params_categoryIDs'),
        group = jQuery('#jform_params_groupIDs'),
        room = jQuery('#jform_params_roomIDs');

    // init loading
    if (department.val() !== '0') {
        update();
    }

    department.change(update);
    category.change(update);

    /**
     * All values get filtered by department/program through Ajax
     * When triggered by event - discard old value
     * @param {Event} [event]
     */
    function update(event) {
        const categoryAjax = new XMLHttpRequest(), groupAjax = new XMLHttpRequest(), roomAjax = new XMLHttpRequest(),
            url = '../index.php?option=com_thm_organizer&format=json',
            params = '&departmentIDs=' + department.val() + '&categoryIDs=' + (category.val() || ''),
            keepValue = !event;

        // Update programs, when it is not its own trigger
        if (!event || (event && event.target.id !== category.attr('id'))) {
            categoryAjax.open('GET', url + '&view=category_options' + params, true);
            categoryAjax.onreadystatechange = function () {

                if (categoryAjax.readyState === 4 && categoryAjax.status === 200) {
                    insertOptions(category, JSON.parse(categoryAjax.responseText), keepValue);
                }
            };
            categoryAjax.send();
        }

        // Update pools
        groupAjax.open('GET', url + '&view=group_options' + params, true);
        groupAjax.onreadystatechange = function () {

            if (groupAjax.readyState === 4 && groupAjax.status === 200) {
                insertOptions(group, JSON.parse(groupAjax.responseText), keepValue);
            }
        };
        groupAjax.send();

        // Update rooms
        roomAjax.open('GET', url + '&view=room_options&roomtypeIDs=-1' + params, true);
        roomAjax.onreadystatechange = function () {

            if (roomAjax.readyState === 4 && roomAjax.status === 200) {
                insertOptions(room, JSON.parse(roomAjax.responseText), keepValue);
            }
        };
        roomAjax.send();
    }

    /**
     * Fills options of given field with an Ajax request
     * @params {object} field
     * @params {string} request
     * @params {boolean} keepValue
     */
    function insertOptions(field, values, keepValue) {
        const oldValues = field.val();
        let key, option, value;

        // Remove all options other than the placeholder.
        field.find('option:not(:first)').remove();

        for (key in values) {
            if (values.hasOwnProperty(key)) {
                value = values[key].value;
                option = jQuery('<option></option>').val(value).html(values[key].text);
                if (keepValue && (jQuery.inArray(value, oldValues) !== -1 || value === oldValues)) {
                    option.attr('selected', 'selected');
                }
                field.append(option);
            }
        }
        jQuery(field).chosen('destroy').chosen();
    }
});
