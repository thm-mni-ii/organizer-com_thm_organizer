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
 * Clear the current list and add new categories to it
 *
 * @param  {object}  categories   the category received
 */
function addCategories(categories)
{
    'use strict';

    const categorySelection = jQuery('#categoryIDs'),
        selectedCategories = categorySelection.val();
    let selected;

    categorySelection.children().remove();

    jQuery.each(categories, function (key, value) {
        var name = value.name == null ? value.ppName : value.name;
        selected = jQuery.inArray(value.id, selectedCategories) > -1 ? 'selected' : '';
        categorySelection.append('<option value="' + value.id + '" ' + selected + '>' + name + '</option>');
    });

    if (si !== true)
    {
        categorySelection.chosen('destroy');
        categorySelection.chosen();
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
 * Clear the current list and add new persons to it
 *
 * @param  {object}  persons   the persons received
 */
function addPersons(persons)
{
    'use strict';

    const personSelection = jQuery('#personIDs'),
        selectedPersons = personSelection.val();
    let selected;

    personSelection.children().remove();

    jQuery.each(persons, function (name, id) {
        selected = jQuery.inArray(id, selectedPersons) > -1 ? 'selected' : '';
        personSelection.append('<option value="' + id + '" ' + selected + '>' + name + '</option>');
    });

    if (si !== true)
    {
        personSelection.chosen('destroy');
        personSelection.chosen();
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
    let addAuth = false, authDefined = false, emptyPools, emptyRooms, emptyPersons,
        mySchedule, selectedPools, selectedRooms, selectedPersons, url;

    if (format !== 'ics')
    {
        return true;
    }

    authDefined = username !== undefined && auth !== undefined;
    url = rootURI + 'index.php?option=com_thm_organizer&view=schedule_export&format=ics&interval=ics';
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

        selectedPersons = jQuery('#personIDs').val();
        emptyPersons = selectedPersons === undefined || selectedPersons == null || selectedPersons.length === 0;

        if (!emptyPersons && authDefined)
        {
            addAuth = true;
            url += '&personIDs=' + selectedPersons;
        }
    }

    if (addAuth)
    {
        url += '&username=' + username + '&auth=' + auth;
    }

    window.prompt(Joomla.JText._('ORGANIZER_COPY_SUBSCRIPTION'), url);

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
 * Load pools dependent on the selected departments and categories
 */
function repopulateResources()
{
    'use strict';

    const selectedDepartments = jQuery('#departmentIDs').val(),
        selectedCategories = jQuery('#categoryIDs').val();
    let invalidDepartments, invalidCategories, componentParameters, selectionParameters = '';

    invalidDepartments = selectedDepartments == null || selectedDepartments.length === 0;
    invalidCategories = selectedCategories == null || selectedCategories.length === 0;

    // The all selection was revoked from something.
    if (invalidDepartments && invalidCategories)
    {
        return;
    }

    componentParameters = 'index.php?option=com_thm_organizer&format=json';

    if (!invalidDepartments)
    {
        selectionParameters += '&departmentIDs=' + selectedDepartments;
    }

    if (!invalidCategories)
    {
        selectionParameters += '&categoryIDs=' + selectedCategories;
    }

    jQuery.ajax({
        type: 'GET',
        url: rootURI + componentParameters + selectionParameters + '&view=group_options',
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
        url: rootURI + componentParameters + selectionParameters + '&view=person_options',
        dataType: 'json',
        success: function (data) {
            addPersons(data);
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
        url: rootURI + componentParameters + selectionParameters + '&view=room_options',
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
 * Load categories dependent on the selected departments
 */
function repopulateCategories()
{
    'use strict';

    const componentParameters = '/index.php?option=com_thm_organizer&view=category_options&format=json',
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
            addCategories(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            if (xhr.status === 404 || xhr.status === 500)
            {
                jQuery.ajax(repopulateCategories());
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
        intervalContainer = jQuery('#interval-container'),
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
            actionButton.text(Joomla.JText._('ORGANIZER_GENERATE_LINK') + ' ');
            actionButton.append('<span class="icon-feed"></span>');
            dateContainer.hide();
            intervalContainer.hide();
            groupingContainer.hide();
            titlesContainer.hide();
            xlsFormatContainer.hide();
            break;
        case 'xls':
            formatInput.val(format);
            documentFormat = documentFormat === undefined ? 'si' : documentFormat;
            documentFormatContainer.val(documentFormat);
            actionButton.text(Joomla.JText._('ORGANIZER_DOWNLOAD') + ' ').append('<span class="icon-file-excel"></span>');
            dateContainer.show();
            intervalContainer.show();
            groupingContainer.hide();
            linkContainer.hide();
            linkTarget.text('');
            titlesContainer.hide();
            xlsFormatContainer.show();
            break;
        case 'pdf':
        default:
            formatInput.val(format);
            documentFormat = documentFormat === undefined ? 'a4' : documentFormat;
            documentFormatContainer.val(documentFormat);
            actionButton.text(Joomla.JText._('ORGANIZER_DOWNLOAD') + ' ').append('<span class="icon-file-pdf"></span>');
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
            dateContainer.show();
            intervalContainer.show();
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
        jQuery('#personIDs-container').hide();
        jQuery('input[name=myschedule]').val(1);
    }
    else
    {
        jQuery('#filterFields').show();
        jQuery('#poolIDs-container').show();
        jQuery('#roomIDs-container').show();
        jQuery('#personIDs-container').show();
        jQuery('input[name=myschedule]').val(0);
    }

}

function validateSelection()
{
    const mySchedule = jQuery('#myschedule:checked').val(),
        selectedPools = jQuery('#poolIDs').val(),
        selectedRooms = jQuery('#roomIDs').val(),
        selectedPersons = jQuery('#personIDs').val();
    let emptyPools, emptyRooms, emptyPersons;

    if (mySchedule === 'on')
    {
        return true;
    }

    emptyPools = selectedPools == null || selectedPools.length === 0;
    emptyRooms = selectedRooms == null || selectedRooms.length === 0;
    emptyPersons = selectedPersons == null || selectedPersons.length === 0;

    if (emptyPools && emptyRooms && emptyPersons)
    {
        alert(Joomla.JText._('ORGANIZER_LIST_SELECTION_WARNING'),);
        return false;
    }

    return true;
}