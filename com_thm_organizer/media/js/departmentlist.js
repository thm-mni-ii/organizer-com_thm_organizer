/**
 * Gets programs, rooms and teachers depending on selected department/program and inserts them into options
 */
jQuery(document).ready(function () {
    "use strict";

    var department = jQuery('#jform_params_departmentID'),
        program = jQuery('#jform_params_programIDs'),
        pool = jQuery('#jform_params_poolIDs'),
        room = jQuery('#jform_params_roomIDs'),
        teacher = jQuery('#jform_params_teacherIDs');

    // init loading
    if (department.val() !== "0")
    {
        update();
    }

    department.change(update);
    program.change(update);

    /**
     * All values get filtered by department/program through Ajax
     * When triggered by event - discard old value
     * @param {Event} [event]
     */
    function update(event)
    {
        var ajaxBaseUrl = '../index.php?option=com_thm_organizer&view=schedule_ajax&format=raw',
            ajaxParams = '&departmentIDs=' + department.val() + '&programIDs=' + (program.val() || ''),
            keepValue = !event;

        // Update programs, when it is not its own trigger
        if (!event || (event && event.target.id !== program.attr('id')))
        {
            jQuery.ajax(ajaxBaseUrl + '&task=getPrograms' + ajaxParams)
                .done(function (request) {
                    insertOptions(program, request, keepValue);
                });
        }
        // Update pools
        jQuery.ajax(ajaxBaseUrl + '&task=getPools' + ajaxParams)
            .done(function (request) {
                insertOptions(pool, request, keepValue);
            });
        // Update rooms
        jQuery.ajax(ajaxBaseUrl + '&task=getRooms&roomtypeIDs=-1' + ajaxParams)
            .done(function (request) {
                insertOptions(room, request, keepValue);
            });
        // Update teachers
        jQuery.ajax(ajaxBaseUrl + '&task=getTeachers' + ajaxParams)
            .done(function (request) {
                insertOptions(teacher, request, keepValue);
            });
    }

    /**
     * Fills options of given field with an Ajax request
     * @params {object} field
     * @params {string} request
     * @params {boolean} keepValue
     */
    function insertOptions(field, request, keepValue)
    {
        var values = JSON.parse(request), oldValue = field.val();

        field.find('option:not(:first)').remove();
        jQuery.each(values, function (name, id) {
            var option = jQuery('<option></option>').val(id).html(name);
            if (keepValue && (jQuery.inArray(id, oldValue) !== -1 || id === oldValue))
            {
                option.attr('selected', 'selected');
            }
            field.append(option);
        });
        jQuery(field).chosen('destroy').chosen();
    }
});