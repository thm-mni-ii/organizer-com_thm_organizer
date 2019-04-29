/**
 * Created by James Antrim on 11/15/2016.
 */

jQuery(document).ready(function () {
    jQuery('label').tooltip({delay: 200, placement: 'right'});
});

/**
 * Clear the current list and add new pools to it
 *
 * @param  {object}  pools   the pools received
 */
function addPools(pools)
{
    'use strict';

    const poolSelection = jQuery('#poolIDs'),
        selectedPools = poolSelection.val();
    let selected;

    poolSelection.children().remove();

    jQuery.each(pools, function (name, id) {
        selected = jQuery.inArray(id, selectedPools) > -1 ? 'selected' : '';
        poolSelection.append('<option value="' + id + '" ' + selected + '>' + name + '</option>');
    });

    if (si !== true)
    {
        poolSelection.chosen('destroy');
        poolSelection.chosen();
    }
}

/**
 * Clear the current list and add new programs to it
 *
 * @param  {object}  programs   the programs received
 */
function addPrograms(programs)
{
    'use strict';

    const programSelection = jQuery('#programIDs'),
        selectedPrograms = programSelection.val();
    let selected;

    programSelection.children().remove();

    jQuery.each(programs, function (key, value) {
        var name = value.name == null ? value.ppName : value.name;
        selected = jQuery.inArray(value.id, selectedPrograms) > -1 ? 'selected' : '';
        programSelection.append('<option value="' + value.id + '" ' + selected + '>' + name + '</option>');
    });

    if (si !== true)
    {
        programSelection.chosen('destroy');
        programSelection.chosen();
    }
}

/**
 * Clear the current list and add new rooms to it
 *
 * @param  {object}  rooms   the rooms received
 */
function addRooms(rooms)
{
    'use strict';

    const roomSelection = jQuery('#roomIDs'),
        selectedRooms = roomSelection.val();
    let selected;

    roomSelection.children().remove();

    jQuery.each(rooms, function (name, id) {
        selected = jQuery.inArray(id, selectedRooms) > -1 ? 'selected' : '';
        roomSelection.append('<option value="' + id + '" ' + selected + '>' + name + '</option>');
    });

    if (si !== true)
    {
        roomSelection.chosen('destroy');
        roomSelection.chosen();
    }
}

/**
 * Clear the current list and add new teachers to it
 *
 * @param  {object}  teachers   the teachers received
 */
function addTeachers(teachers)
{
    'use strict';

    const teacherSelection = jQuery('#teacherIDs'),
        selectedTeachers = teacherSelection.val();
    let selected;

    teacherSelection.children().remove();

    jQuery.each(teachers, function (name, id) {
        selected = jQuery.inArray(id, selectedTeachers) > -1 ? 'selected' : '';
        teacherSelection.append('<option value="' + id + '" ' + selected + '>' + name + '</option>');
    });

    if (si !== true)
    {
        teacherSelection.chosen('destroy');
        teacherSelection.chosen();
    }
}

/**
 * Creates a link to a generated ics file
 *
 * @returns {boolean}
 */
function copyLink()
{
    const format = jQuery('input[name=format]').val();
    let addAuth = false, authDefined = false, emptyPools, emptyRooms, emptyTeachers,
        mySchedule, selectedPools, selectedRooms, selectedTeachers, url;

    if (format !== 'ics')
    {
        return true;
    }

    authDefined = username !== undefined && auth !== undefined;
    url = rootURI + 'index.php?option=com_thm_organizer&view=schedule_export&format=ics';
    mySchedule = jQuery('#myschedule:checked').val();

    if (mySchedule === 'on' && authDefined)
    {
        url += '&myschedule=1';
        addAuth = true;
    }
    else
    {
        selectedPools = jQuery('#poolIDs').val();
        emptyPools = selectedPools === undefined || selectedPools == null || selectedPools.length === 0;

        if (!emptyPools)
        {
            url += '&poolIDs=' + selectedPools;
        }

        selectedRooms = jQuery('#roomIDs').val();
        emptyRooms = selectedRooms === undefined || selectedRooms == null || selectedRooms.length === 0;

        if (!emptyRooms)
        {
            url += '&roomIDs=' + selectedRooms;
        }

        selectedTeachers = jQuery('#teacherIDs').val();
        emptyTeachers = selectedTeachers === undefined || selectedTeachers == null || selectedTeachers.length === 0;

        if (!emptyTeachers && authDefined)
        {
            addAuth = true;
            url += '&teacherIDs=' + selectedTeachers;
        }
    }

    if (addAuth)
    {
        url += '&username=' + username + '&auth=' + auth;
    }

    window.prompt(copyText, url);

    return false;
}

function handleSubmit()
{
    const validSelection = validateSelection(),
        formatValue = jQuery('input[name=format]').val();

    if (!validSelection)
    {
        return false;
    }

    if (formatValue === 'ics')
    {
        copyLink();
        return true;
    }

    jQuery('#adminForm').submit();

    return true;
}

/**
 * Load pools dependent on the selected departments and programs
 */
function repopulateResources()
{
    'use strict';

    const selectedDepartments = jQuery('#departmentIDs').val(),
        selectedPrograms = jQuery('#programIDs').val();
    let invalidDepartments, invalidPrograms, componentParameters, selectionParameters = '';

    invalidDepartments = selectedDepartments == null || selectedDepartments.length === 0;
    invalidPrograms = selectedPrograms == null || selectedPrograms.length === 0;

    // The all selection was revoked from something.
    if (invalidDepartments && invalidPrograms)
    {
        return;
    }

    componentParameters = 'index.php?option=com_thm_organizer&format=raw&task=getPlanOptions';

    if (!invalidDepartments)
    {
        selectionParameters += '&departmentIDs=' + selectedDepartments;
    }

    if (!invalidPrograms)
    {
        selectionParameters += '&programIDs=' + selectedPrograms;
    }

    jQuery.ajax({
        type: 'GET',
        url: rootURI + componentParameters + selectionParameters + '&view=pool_ajax',
        dataType: 'json',
        success: function (data) {
            addPools(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            if (xhr.status === 404 || xhr.status === 500)
            {
                jQuery.ajax(repopulateResources());
            }
        }
    });

    jQuery.ajax({
        type: 'GET',
        url: rootURI + componentParameters + selectionParameters + '&view=teacher_ajax',
        dataType: 'json',
        success: function (data) {
            addTeachers(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            if (xhr.status === 404 || xhr.status === 500)
            {
                jQuery.ajax(repopulateResources());
            }
        }
    });

    jQuery.ajax({
        type: 'GET',
        url: rootURI + componentParameters + selectionParameters + '&view=room_ajax',
        dataType: 'json',
        success: function (data) {
            addRooms(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            if (xhr.status === 404 || xhr.status === 500)
            {
                jQuery.ajax(repopulateResources());
            }
        }
    });
}

/**
 * Load programs dependent on the selected departments
 */
function repopulatePrograms()
{
    'use strict';

    const componentParameters = '/index.php?option=com_thm_organizer&view=program_ajax&format=raw&task=getPlanOptions',
        selectedDepartments = jQuery('#departmentIDs').val();
    let selectionParameters;

    if (selectedDepartments == null)
    {
        return;
    }

    selectionParameters = '&departmentIDs=' + selectedDepartments;

    jQuery.ajax({
        type: 'GET',
        url: rootURI + componentParameters + selectionParameters,
        dataType: 'json',
        success: function (data) {
            addPrograms(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            if (xhr.status === 404 || xhr.status === 500)
            {
                jQuery.ajax(repopulatePrograms());
            }
        }
    });
}

function setFormat()
{
    const actionButton = jQuery('#action-btn'),
        dateContainer = jQuery('#date-container'),
        formatValue = jQuery('#format').find(':selected').val(),
        formatArray = formatValue.split('.'),
        format = formatArray[0],
        dateRestrictionContainer = jQuery('#dateRestriction-container'),
        /*displayFormatContainer = jQuery('#displayFormat-container'),*/
        documentFormatContainer = jQuery('input[name=documentFormat]'),
        formatInput = jQuery('input[name=format]'),
        groupingContainer = jQuery('#grouping-container'),
        linkContainer = jQuery('#link-container'),
        linkTarget = jQuery('#link-target'),
        titlesContainer = jQuery('#titles-container'),
        /*pdfFormatContainer = jQuery('#pdfWeekFormat-container'),*/
        xlsFormatContainer = jQuery('#xlsWeekFormat-container');

    let documentFormat = formatArray[1];

    switch (format)
    {
        case 'ics':
            formatInput.val(format);
            actionButton.text(generateText + ' ').append('<span class="icon-feed"></span>');
            //displayFormatContainer.hide();
            dateContainer.hide();
            dateRestrictionContainer.hide();
            groupingContainer.hide();
            //pdfFormatContainer.hide();
            titlesContainer.hide();
            xlsFormatContainer.hide();
            break;
        case 'xls':
            formatInput.val(format);
            documentFormat = documentFormat === undefined ? 'si' : documentFormat;
            documentFormatContainer.val(documentFormat);
            actionButton.text(downloadText + ' ').append('<span class="icon-file-excel"></span>');
            dateContainer.show();
            dateRestrictionContainer.show();
            //displayFormatContainer.hide();
            groupingContainer.hide();
            linkContainer.hide();
            linkTarget.text('');
            //pdfFormatContainer.hide();
            titlesContainer.hide();
            xlsFormatContainer.show();
            break;
        case 'pdf':
        default:
            formatInput.val(format);
            documentFormat = documentFormat === undefined ? 'a4' : documentFormat;
            documentFormatContainer.val(documentFormat);
            actionButton.text(downloadText + ' ').append('<span class="icon-file-pdf"></span>');
            linkContainer.hide();
            linkTarget.text('');
            if (documentFormat === 'a4')
            {
                groupingContainer.hide();
            }
            else
            {
                groupingContainer.show();
            }
            //displayFormatContainer.hide();
            dateContainer.show();
            dateRestrictionContainer.show();
            //pdfFormatContainer.hide();
            titlesContainer.show();
            xlsFormatContainer.hide();
            break;
    }
}

/**
 * Toggles the output of resource and filter fields depenent on the selection of my schedule
 */
function toggleMySchedule()
{
    const mySchedule = jQuery('#myschedule:checked').val();

    if (mySchedule === 'on')
    {
        jQuery('#filterFields').hide();
        jQuery('#poolIDs-container').hide();
        jQuery('#roomIDs-container').hide();
        jQuery('#teacherIDs-container').hide();
        jQuery('input[name=myschedule]').val(1);
    }
    else
    {
        jQuery('#filterFields').show();
        jQuery('#poolIDs-container').show();
        jQuery('#roomIDs-container').show();
        jQuery('#teacherIDs-container').show();
        jQuery('input[name=myschedule]').val(0);
    }

}

function validateSelection()
{
    const mySchedule = jQuery('#myschedule:checked').val(),
        selectedPools = jQuery('#poolIDs').val(),
        selectedRooms = jQuery('#roomIDs').val(),
        selectedTeachers = jQuery('#teacherIDs').val();
    let emptyPools, emptyRooms, emptyTeachers;

    if (mySchedule === 'on')
    {
        return true;
    }

    emptyPools = selectedPools == null || selectedPools.length === 0;
    emptyRooms = selectedRooms == null || selectedRooms.length === 0;
    emptyTeachers = selectedTeachers == null || selectedTeachers.length === 0;

    if (emptyPools && emptyRooms && emptyTeachers)
    {
        alert(selectionWarning);
        return false;
    }

    return true;
}