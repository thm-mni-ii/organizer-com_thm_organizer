/**
 * Created by James Antrim on 11/15/2016.
 */

$( document ).ready(function() {
    $('label').tooltip({delay: 200, placement: 'right'});
});

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
 * Clear the current list and add new planning periods to it
 *
 * @param   {object}  planningPeriods   the rooms received
 */
function addPlanningPeriods(planningPeriods)
{
    "use strict";

    var ppSelection = $('#planningPeriodIDs'), selectedPP = ppSelection.val(), selected;

    ppSelection.children().remove();

    $.each(planningPeriods, function (index, data)
    {
        selected = $.inArray(data.value, selectedPP) > -1 ? 'selected' : '';
        ppSelection.append("<option value=\"" + data.value + "\" " + selected + ">" + data.text + "</option>");
    });

    ppSelection.chosen("destroy");
    ppSelection.chosen();
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
 * Changes the displayed form fields dependent on the date restriction
 */
function handleDateRestriction()
{
    var drValue = $('#dateRestriction').find(":selected").val();

    switch (drValue)
    {
        case 'semester':
            $("#date-container").hide();
            $("#planningPeriodIDs-container").show();
            $("input[name=use]").val('planningPeriodIDs');
            break;
        case 'month':
        case 'week':
        default:
            $("#date-container").show();
            $("#planningPeriodIDs-container").hide();
            $("input[name=use]").val('date');
            break;
    }
}

/**
 * Load planning periods dependent on the selected departments and programs
 */
function repopulatePlanningPeriods()
{
    "use strict";

    var selectedDepartments = $('#departmentIDs').val(),
        selectedPrograms = $('#programIDs').val(),
        validDepartments, validPrograms,
        componentParameters, selectionParameters = '';

    validDepartments = selectedDepartments != null && selectedDepartments.length !== 0;
    validPrograms = selectedPrograms != null && selectedPrograms.length !== 0;

    componentParameters = 'index.php?option=com_thm_organizer&view=planning_period_ajax&format=raw&task=getOptions';

    if (validDepartments)
    {
        componentParameters += '&departmentIDs=' + selectedDepartments;
    }

    if (validPrograms)
    {
        componentParameters += '&programIDs=' + selectedPrograms;
    }

    $.ajax({
        type: 'GET',
        url: rootURI + componentParameters,
        dataType: 'json',
        success: function (data)
        {
            addPlanningPeriods(data);
        },
        error: function (xhr, textStatus, errorThrown)
        {
            if (xhr.status === 404 || xhr.status === 500)
            {
                $.ajax(repopulatePlanningPeriods());
            }
        }
    });
}

/**
 * Load rooms dependent on the selected departments and programs
 */
function repopulateRooms()
{
    "use strict";

    var selectedDepartments = $('#departmentIDs').val(),
        selectedPrograms = $('#programIDs').val(),
        selectedTypes = $('#typeIDs').val(),
        validDepartments, validPrograms, validTypes,
        componentParameters;

    validDepartments = selectedDepartments != null && selectedDepartments.length !== 0;
    validPrograms = selectedPrograms != null && selectedPrograms.length !== 0;
    validTypes = selectedTypes != null && selectedTypes.length !== 0;

    componentParameters = 'index.php?option=com_thm_organizer&view=room_ajax&format=raw&task=getPlanOptions';

    if (validDepartments)
    {
        componentParameters += '&departmentIDs=' + selectedDepartments;
    }

    if (validPrograms)
    {
        componentParameters += '&programIDs=' + selectedPrograms;
    }

    if (validTypes)
    {
        componentParameters += '&typeIDs=' + selectedTypes;
    }

    $.ajax({
        type: 'GET',
        url: rootURI + componentParameters,
        dataType: 'json',
        success: function (data)
        {
            addRooms(data);
        },
        error: function (xhr, textStatus, errorThrown)
        {
            if (xhr.status === 404 || xhr.status === 500)
            {
                $.ajax(repopulateRooms());
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
