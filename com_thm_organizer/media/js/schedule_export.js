/**
 * Created by James Antrim on 11/15/2016.
 */

$( document ).ready(function() {
    $('label').tooltip({delay: 200, placement: 'right'});
});

/**
 * Clear the current list and add new pools to it
 *
 * @param   {object}  pools   the pools received
 */
function addPools(pools)
{
    "use strict";

    var poolSelection = $('#poolIDs'), selectedPools = poolSelection.val(), selected;

    poolSelection.children().remove();

    $.each(pools, function (name, id)
    {
        selected = $.inArray(id, selectedPools) > -1 ? 'selected' : '';
        poolSelection.append("<option value=\"" + id + "\" " + selected + ">" + name + "</option>");
    });

    poolSelection.chosen("destroy");
    poolSelection.chosen();
}

/**
 * Clear the current list and add new programs to it
 *
 * @param   {object}  programs   the programs received
 */
function addPrograms(programs)
{
    "use strict";

    var programSelection = $('#programIDs'), selectedPrograms = programSelection.val(), selected;

    programSelection.children().remove();

    $.each(programs, function (key, value)
    {
        var name = value.name == null ? value.ppName : value.name;
        selected = $.inArray(value.id, selectedPrograms) > -1 ? 'selected' : '';
        programSelection.append("<option value=\"" + value.id + "\" " + selected + ">" + name + "</option>");
    });

    programSelection.chosen("destroy");
    programSelection.chosen();
}

/**
 * Clear the current list and add new rooms to it
 *
 * @param   {object}  rooms   the rooms received
 */
function addRooms(rooms)
{
    "use strict";

    var roomSelection = $('#roomIDs'), selectedRooms = roomSelection.val(), selected;

    roomSelection.children().remove();

    $.each(rooms, function (name, id)
    {
        selected = $.inArray(id, selectedRooms) > -1 ? 'selected' : '';
        roomSelection.append("<option value=\"" + id + "\" " + selected + ">" + name + "</option>");
    });

    roomSelection.chosen("destroy");
    roomSelection.chosen();
}

/**
 * Clear the current list and add new teachers to it
 *
 * @param   {object}  teachers   the teachers received
 */
function addTeachers(teachers)
{
    "use strict";

    var teacherSelection = $('#teacherIDs'), selectedTeachers = teacherSelection.val(), selected;

    teacherSelection.children().remove();

    $.each(teachers, function (name, id)
    {
        selected = $.inArray(id, selectedTeachers) > -1 ? 'selected' : '';
        teacherSelection.append("<option value=\"" + id + "\" " + selected + ">" + name + "</option>");
    });

    teacherSelection.chosen("destroy");
    teacherSelection.chosen();
}

function copyLink(success)
{
    var format, url, selectedPools, emptyPools,
        selectedRooms, emptyRooms,
        selectedTeachers, emptyTeachers;

    if (!success)
    {
        return false;
    }

    format = $("input[name=format]").val();
    url = rootURI + 'index.php?option=com_thm_organizer&view=schedule_export&format=ics';

    if (format !== 'ics')
    {
        return true;
    }

    selectedPools = $('#poolIDs').val();
    emptyPools = selectedPools == undefined ||selectedPools == null || selectedPools.length === 0;

    if (!emptyPools)
    {
        url += '&poolIDs=' + selectedPools;
    }

    selectedRooms = $('#roomIDs').val();
    emptyRooms = selectedRooms == undefined || selectedRooms == null || selectedRooms.length === 0;

    if (!emptyRooms)
    {
        url += '&roomIDs=' + selectedRooms;
    }

    selectedTeachers = $('#teacherIDs').val();
    emptyTeachers = selectedTeachers == undefined || selectedTeachers == null || selectedTeachers.length === 0;

    if (!emptyTeachers)
    {
        url += '&teacherIDs=' + selectedTeachers;
    }

    window.prompt(copyText, url);

    return false;
}

function handleSubmit()
{
    var validSelection = validateSelection();

    if (!validSelection)
    {
        return false;
    }

    if ($("input[name=format]").val() == 'ics')
    {
        copyLink();
        return true;
    }

    $("#adminForm").submit();

    return true;
}

/**
 * Load pools dependent on the selected departments and programs
 */
function repopulateResources()
{
    "use strict";

    var selectedDepartments = $('#departmentIDs').val(), selectedPrograms = $('#programIDs').val(),
        invalidDepartments, invalidPrograms, allIndex, componentParameters, selectionParameters = '';

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

    $.ajax({
        type: 'GET',
        url: rootURI + componentParameters + selectionParameters + '&view=pool_ajax',
        dataType: 'json',
        success: function (data)
        {
            addPools(data);
        },
        error: function (xhr, textStatus, errorThrown)
        {
            if (xhr.status === 404 || xhr.status === 500)
            {
                $.ajax(repopulateResources());
            }
        }
    });

    $.ajax({
        type: 'GET',
        url: rootURI + componentParameters + selectionParameters + '&view=teacher_ajax',
        dataType: 'json',
        success: function (data)
        {
            addTeachers(data);
        },
        error: function (xhr, textStatus, errorThrown)
        {
            if (xhr.status === 404 || xhr.status === 500)
            {
                $.ajax(repopulateResources());
            }
        }
    });

    $.ajax({
        type: 'GET',
        url: rootURI + componentParameters + selectionParameters + '&view=room_ajax',
        dataType: 'json',
        success: function (data)
        {
            addRooms(data);
        },
        error: function (xhr, textStatus, errorThrown)
        {
            if (xhr.status === 404 || xhr.status === 500)
            {
                $.ajax(repopulateResources());
            }
        }
    });
}

/**
 * Load programs dependent on the selected departments
 */
function repopulatePrograms()
{
    "use strict";

    var componentParameters, selectedDepartments = $('#departmentIDs').val(), allIndex, selectionParameters;
    componentParameters = '/index.php?option=com_thm_organizer&view=program_ajax&format=raw&task=getPlanOptions';

    if (selectedDepartments == null)
    {
        return;
    }

    selectionParameters = '&departmentIDs=' + selectedDepartments;

    $.ajax({
        type: 'GET',
        url: rootURI + componentParameters + selectionParameters,
        dataType: 'json',
        success: function (data)
        {
            addPrograms(data);
        },
        error: function (xhr, textStatus, errorThrown)
        {
            if (xhr.status === 404 || xhr.status === 500)
            {
                $.ajax(repopulatePrograms());
            }
        }
    });
}

function setFormat()
{
    var formatValue = $('#format').find(":selected").val(), formatArray = formatValue.split('.'),
        format = formatArray[0], documentFormat = formatArray[1], actionButton = $("#action-btn"),
        linkContainer =$('#link-container'), linkTarget = $('#link-target');

    switch (format)
    {
        case 'ics':
            $("input[name=format]").val(format);
            actionButton.text(generateText + ' ').append('<span class="icon-feed"></span>');
            $("#displayFormat-container").hide();
            $("#date-container").hide();
            $("#dateRestriction-container").hide();
            $("#pdfWeekFormat-container").hide();
            $("#xlsWeekFormat-container").hide();
            break;
        case 'xls':
            $("input[name=format]").val(format);
            documentFormat = documentFormat === undefined ? 'si' : documentFormat;
            $("input[name=documentFormat]").val(documentFormat);
            actionButton.text(downloadText + ' ').append('<span class="icon-file-xls"></span>');
            linkContainer.hide();
            linkTarget.text('');
            $("#displayFormat-container").hide();
            $("#pdfWeekFormat-container").hide();
            $("#date-container").show();
            $("#dateRestriction-container").show();
            $("#xlsWeekFormat-container").show();
            break;
        case 'pdf':
        default:
            $("input[name=format]").val(format);
            documentFormat = documentFormat === undefined ? 'a4' : documentFormat;
            $("input[name=documentFormat]").val(documentFormat);
            actionButton.text(downloadText + ' ').append('<span class="icon-file-pdf"></span>');
            linkContainer.hide();
            linkTarget.text('');
            $("#displayFormat-container").show();
            $("#date-container").show();
            $("#dateRestriction-container").show();
            $("#pdfWeekFormat-container").show();
            $("#xlsWeekFormat-container").hide();
            break;
    }
}

function validateSelection()
{
    var selectedPools = $('#poolIDs').val(), emptyPools,
        selectedRooms = $('#roomIDs').val(), emptyRooms,
        selectedTeachers = $('#teacherIDs').val(), emptyTeachers;

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