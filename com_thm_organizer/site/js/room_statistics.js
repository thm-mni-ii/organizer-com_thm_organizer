$(document).ready(function () {
    $('label').tooltip({delay: 200, placement: 'right'});
});

/**
 * Clear the current list and add new categories to it
 *
 * @param  {object}  categories   the categories received
 */
function addCategories(categories)
{
    'use strict';

    var categorySelection = $('#categoryIDs'), selectedCategories = categorySelection.val(), selected;

    categorySelection.children().remove();

    $.each(categories, function (key, value) {
        var name = value.name == null ? value.ppName : value.name;
        selected = $.inArray(value.id, selectedCategories) > -1 ? 'selected' : '';
        categorySelection.append('<option value="' + value.id + '" ' + selected + '>' + name + '</option>');
    });

    categorySelection.chosen('destroy');
    categorySelection.chosen();
}

/**
 * Clear the current list and add new terms to it
 *
 * @param  {object}  terms   the terms received
 */
function addTerms(terms)
{
    'use strict';

    var ppSelection = $('#termIDs'), selectedPP = ppSelection.val(), selected;

    ppSelection.children().remove();

    $.each(terms, function (index, data) {
        selected = $.inArray(data.value, selectedPP) > -1 ? 'selected' : '';
        ppSelection.append('<option value="' + data.value + '" ' + selected + '>' + data.text + '</option>');
    });

    ppSelection.chosen('destroy');
    ppSelection.chosen();
}

/**
 * Clear the current list and add new rooms to it
 *
 * @param  {object}  rooms   the rooms received
 */
function addRooms(rooms)
{
    'use strict';

    var roomSelection = $('#roomIDs'), selectedRooms = roomSelection.val(), selected;

    roomSelection.children().remove();

    $.each(rooms, function (name, id) {
        selected = $.inArray(id, selectedRooms) > -1 ? 'selected' : '';
        roomSelection.append('<option value="' + id + '" ' + selected + '>' + name + '</option>');
    });

    roomSelection.chosen('destroy');
    roomSelection.chosen();
}

/**
 * Changes the displayed form fields dependent on the date restriction
 */
function handleInterval()
{
    var drValue = $('#interval').find(':selected').val(), dateContainer = $('#date-container'),
        periodsContainer = $('#termIDs-container'), useInput = $('input[name=use]');

    switch (drValue)
    {
        case 'semester':
            dateContainer.hide();
            periodsContainer.show();
            useInput.val('termIDs');
            break;
        case 'month':
        case 'week':
        default:
            dateContainer.show();
            periodsContainer.hide();
            useInput.val('date');
            break;
    }
}

/**
 * Load rooms dependent on the selected departments and categories
 */
function repopulateRooms()
{
    'use strict';

    var selectedDepartments = $('#departmentIDs').val(),
        selectedCategories = $('#categoryIDs').val(),
        selectedRoomTypes = $('#roomtypeIDs').val(),
        validDepartments, validCategories, validRoomTypes,
        componentParameters;

    validDepartments = selectedDepartments != null && selectedDepartments.length !== 0;
    validCategories = selectedCategories != null && selectedCategories.length !== 0;
    validRoomTypes = selectedRoomTypes != null && selectedRoomTypes.length !== 0;

    componentParameters = 'index.php?option=com_thm_organizer&view=room_options&format=raw';

    if (validDepartments)
    {
        componentParameters += '&departmentIDs=' + selectedDepartments;
    }

    if (validCategories)
    {
        componentParameters += '&categoryIDs=' + selectedCategories;
    }

    if (validRoomTypes)
    {
        componentParameters += '&roomtypeIDs=' + selectedRoomTypes;
    }

    $.ajax({
        type: 'GET',
        url: rootURI + componentParameters,
        dataType: 'json',
        success: function (data) {
            addRooms(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            if (xhr.status === 404 || xhr.status === 500)
            {
                $.ajax(repopulateRooms());
            }
        }
    });
}

/**
 * Load categories dependent on the selected departments
 */
function repopulateCategories()
{
    'use strict';

    var componentParameters, selectedDepartments = $('#departmentIDs').val(), allIndex, selectionParameters;
    componentParameters = '/index.php?option=com_thm_organizer&view=category_options&format=json';

    if (selectedDepartments == null)
    {
        return;
    }

    selectionParameters = '&departmentIDs=' + selectedDepartments;

    $.ajax({
        type: 'GET',
        url: rootURI + componentParameters + selectionParameters,
        dataType: 'json',
        success: function (data) {
            addCategories(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            if (xhr.status === 404 || xhr.status === 500)
            {
                $.ajax(repopulateCategories());
            }
        }
    });
}
