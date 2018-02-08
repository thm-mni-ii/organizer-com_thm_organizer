/**
 * Created by James Antrim on 11/15/2016.
 */

$(document).ready(function () {
    $('label').tooltip({delay: 200, placement: 'right'});
});

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

    $.each(rooms, function (name, id) {
        selected = $.inArray(id, selectedRooms) > -1 ? 'selected' : '';
        roomSelection.append("<option value=\"" + id + "\" " + selected + ">" + name + "</option>");
    });

    roomSelection.chosen("destroy");
    roomSelection.chosen();
}

/**
 * Load rooms dependent on the selected departments and programs
 */
function repopulateRooms()
{
    "use strict";

    var selectedTypes = $('#typeIDs').val(), validTypes, componentParameters;

    validTypes = selectedTypes != null && selectedTypes.length !== 0;

    componentParameters = 'index.php?option=com_thm_organizer&view=room_ajax&format=raw&task=getPlanOptions';

    if (validTypes)
    {
        componentParameters += '&typeIDs=' + selectedTypes;
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
