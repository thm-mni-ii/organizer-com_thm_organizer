/**
 * Move the buttons from the tool bar to the right place
 */
function moveButton()
{
    var poolButton = jQuery('#toolbar-popup-list').detach(),
        subjectButton = jQuery('#toolbar-popup-book').detach(),
        childToolbar = jQuery('#children-toolbar');

    poolButton.appendTo(childToolbar);
    if (subjectButton.length)
    {
        subjectButton.appendTo(childToolbar);
    }

}

/**
 * Gets all checked items and add the it the children table
 *
 * @param divID the id of the div
 * @param type the type of the source
 */
function getCheckedItems(divID, type)
{
    var iframe = jQuery('iframe');
    jQuery(divID + ' input:checked', iframe.contents()).each(function ()
    {
        var id = jQuery(this).val() + type;
        var name = jQuery(jQuery(this).parent().parent().children()[1]).html();
        var currentOrder = window.parent.getCurrentOrder();
        var length = parseInt(currentOrder.length, 10);
        createNewRow(length, 'childList', id, name, type);
    });
}

/**
 * Calls function getCheckedItems() and calls the close button click event to close the iFrame
 *
 * @param divID the id of the div
 * @param type the type of the source
 */
function closeIframeWindow(divID, type)
{
    getCheckedItems(divID, type);
    jQuery("button.close").trigger("click");
}

/**
 * Moves the values of the calling row up one row in the children table
 *
 * @param {type} oldOrder
 *
 * @returns {void}
 */
function moveUp(oldOrder)
{
    "use strict";

    var currentOrder, reorderObjects;

    currentOrder = getCurrentOrder();
    if (oldOrder <= 1 || (currentOrder.length === Number(oldOrder) && currentOrder[oldOrder - 2].name === ""))
    {
        return;
    }

    // Gets the two rows needing reordering
    reorderObjects = currentOrder.splice(oldOrder - 2, 2);

    // Set calling row to one row lower
    overrideElement((oldOrder - 1), reorderObjects[1]);

    // Set previous lower row to calling positon
    overrideElement(oldOrder, reorderObjects[0]);

}

/**
 * Moves the values of the calling row down one row in the children table
 *
 * @param {type} oldOrder
 *
 * @returns {void}
 */
function moveDown(oldOrder)
{
    "use strict";
    var currentOrder = getCurrentOrder();
    if (oldOrder >= currentOrder.length || (currentOrder.length === Number(oldOrder) + 1 && currentOrder[oldOrder - 1].name === ""))
    {
        return;
    }
    var reorderObjects = currentOrder.splice(oldOrder - 1, 2);

    // Set current to lower
    overrideElement(oldOrder, reorderObjects[1]);

    // Set current with lower
    var newOrder = parseInt(oldOrder, 10) + 1;
    overrideElement(newOrder, reorderObjects[0]);

}

/**
 * Add new empty row
 *
 * @param {int} position
 *
 * @returns {void}
 */
function setEmptyElement(position)
{
    "use strict";

    var currentOrder = getCurrentOrder(),
        length = parseInt(currentOrder.length, 10),
        newOrder,
        oldIndex;

    createNewRow(length, 'childList');

    while (position <= length)
    {
        newOrder = length + 1;
        oldIndex = length - 1;

        overrideElement(newOrder, currentOrder[oldIndex]);
        length--;
    }

    overrideElementWithDummy(position);
}

/**
 * Pushes all rows a step up.
 * The first row 'position' is moved to the last postion.
 *
 * @param {int} position
 * @returns {void}
 */
function setElementOnLastPosition(position)
{
    "use strict";

    var currentOrder = getCurrentOrder();
    var length = parseInt(currentOrder.length, 10);
    var tmpElement = currentOrder[position - 1];

    if (tmpElement.name !== "")
    {
        pushAllUp(position, length, currentOrder);

        overrideElement(length, tmpElement);
    }
}

/**
 * Sets the current values of a row to another row indicated by the value of the
 * order field
 *
 * @param   {int} firstPos
 *
 * @returns  {void}
 */
function orderWithNumber(firstPos)
{
    "use strict";

    var currentOrder = getCurrentOrder(), length = currentOrder.length, tmpElement = currentOrder[firstPos - 1],
        secondPosOrder = jQuery('#child' + firstPos + 'order'), secondPos = secondPosOrder.val();

    secondPos = parseInt(secondPos);

    if (isNaN(secondPos) === true || secondPos > length || (Number(secondPos) === length && tmpElement.name === ""))
    {
        secondPosOrder.val(firstPos);
        return;
    }

    if (firstPos < secondPos)
    {
        pushAllUp(firstPos, secondPos, currentOrder);
    }
    else
    {
        pushAllDown(firstPos, secondPos, currentOrder);
    }

    overrideElement(secondPos, tmpElement);
}

/**
 * Removes a child row from the display
 *
 * @param   {int}  rowNumber  the number of the row to be deleted
 *
 * @returns  {void}
 */
function removeRow(rowNumber)
{
    "use strict";

    var currentOrder = getCurrentOrder();
    var length = currentOrder.length;

    pushAllUp(rowNumber, length, currentOrder);

    jQuery('#childRow' + length).remove();
}

/**
 * Push all Ements up.
 *
 * @param {int} position
 * @param {int} length
 * @param {array} elementArray
 * @returns {void}
 */
function pushAllUp(position, length, elementArray)
{
    "use strict";

    while (position < length)
    {
        overrideElement(position, elementArray[position]);
        position++;
    }
}

/**
 * Push all Elements down.
 *
 * @param {int} position
 * @param {int} length
 * @param {array} elementArray
 * @returns {void}
 */
function pushAllDown(position, length, elementArray)
{
    "use strict";

    while (position > length)
    {
        var newOrder = position;
        var oldIndex = position - 2;

        overrideElement(newOrder, elementArray[oldIndex]);
        position--;
    }
}

/**
 * Retreives an array of objects mapping form values
 *
 * @returns {Array}
 */
function getCurrentOrder()
{
    "use strict";
    var currentOrder = [];

    // The header row needs to be removed from the count
    var rowCount = jQuery('#childList').find('tr').length - 1;
    for (var i = 0; i < rowCount; i++)
    {
        var order = i + 1;
        currentOrder[i] = {};
        currentOrder[i].class = jQuery('#child' + order + 'icon').attr('class').trim();
        currentOrder[i].name = jQuery('#child' + order + 'name').text().trim();
        currentOrder[i].id = jQuery('#child' + order).val();
        currentOrder[i].link = jQuery('#child' + order + 'link').attr('href');
        currentOrder[i].order = jQuery('#child' + order + 'order').val();
    }
    return currentOrder;
}

/**
 * Override a DOM-Element with the ID '#child'+newOrder.
 *
 * @param {int} newOrder
 * @param {Object} oldElement
 * @returns {void}
 */
function overrideElement(newOrder, oldElement)
{
    "use strict";

    jQuery('#child' + newOrder + 'icon').attr('class', (oldElement.class));
    jQuery('#child' + newOrder + 'name').text(oldElement.name);
    jQuery('#child' + newOrder).val(oldElement.id);
    jQuery('#child' + newOrder + 'link').attr('href', oldElement.link);
    jQuery('#child' + newOrder + 'order').val(newOrder);
}

/**
 * Overrides a DOM-Element with a Dummy-Element.
 *
 * @param {int} position
 * @returns {void}
 */
function overrideElementWithDummy(position)
{
    "use strict";

    jQuery('#child' + position + 'icon').attr('class', '');
    jQuery('#child' + position + 'name').text('');
    jQuery('#child' + position).val('');
    jQuery('#child' + position + 'link').attr('href', "");
    jQuery('#child' + position + 'order').val(position);
}

/**
 * Add a new row on the end of the table.
 *
 * @param {int} lastPosition
 * @param {string} tableID
 * @param {int} resourceID
 * @param {string} resourceName
 * @param {string} resourceType
 *
 * @returns {void}  adds a new row to the end of the table
 */
function createNewRow(lastPosition, tableID, resourceID, resourceName, resourceType)
{
    "use strict";
    var mID = 0, name = '', icon = '', rawID, link, nextRowClass, nextRowNumber, html, resourceHTML, orderingHTML;

    if (typeof resourceID !== 'undefined')
    {
        mID = resourceID;
    }
    if (typeof resourceName !== 'undefined')
    {
        name = resourceName;
    }

    rawID = resourceID.substring(0, resourceID.length - 1);
    if (typeof resourceType !== 'undefined')
    {
        switch (resourceType)
        {
            case 'p':
                link = 'index.php?option=com_thm_organizer&view=pool_edit&id=' + rawID;
                icon = 'icon-list';
                break;
            case 's':
                link = 'index.php?option=com_thm_organizer&view=subject_edit&id=' + rawID;
                icon = 'icon-book';
                break;
        }
    }

    nextRowClass = getNextRowClass(lastPosition);
    nextRowNumber = parseInt(lastPosition, 10) + 1;

    html = '<tr id="childRow' + nextRowNumber + '" class="' + nextRowClass + '">';

    resourceHTML = '<td class="child-name">';
    resourceHTML += '<a id="child' + nextRowNumber + 'link" href="' + link + '">';
    resourceHTML += '<span id="child' + nextRowNumber + 'icon" class="' + icon + '"></span>';
    resourceHTML += '<span id="child' + nextRowNumber + 'name">' + name + '</span>';
    resourceHTML += '</a>';
    resourceHTML += '<input id="child' + nextRowNumber + '" type="hidden" value="' + mID + '" name="child' + nextRowNumber + '">';
    resourceHTML += '</td>';

    orderingHTML = '<td class="child-order">';
    orderingHTML += '<button class="btn btn-small" onclick="moveUp(\'' + nextRowNumber + '\');" title="Move Up">';
    orderingHTML += '<span class="icon-previous"></span>';
    orderingHTML += '</button>';
    orderingHTML += '<input type="text" title="Ordering" name="child' + nextRowNumber + 'order" ';
    orderingHTML += 'id="child' + nextRowNumber + 'order" size="2" value="' + nextRowNumber + '" class="text-area-order" ';
    orderingHTML += 'onchange="orderWithNumber(' + nextRowNumber + ');">';
    orderingHTML += '<button class="btn btn-small" onclick="setEmptyElement(\'' + nextRowNumber + '\');" title="Add Space">';
    orderingHTML += '<span class="icon-add-Space"></span>';
    orderingHTML += '</button>';
    orderingHTML += '<button class="btn btn-small" onclick="removeRow(\'' + nextRowNumber + '\');" title="Delete">';
    orderingHTML += '<span class="icon-trash"></span>';
    orderingHTML += '</button>';
    orderingHTML += '<button class="btn btn-small" onclick="moveDown(\'' + nextRowNumber + '\');" title="Move Down">';
    orderingHTML += '<span class="icon-next"></span>';
    orderingHTML += '</button>';
    orderingHTML += '<button class="btn btn-small" onclick="setElementOnLastPosition(\'' + nextRowNumber + '\');" title="Make Last">';
    orderingHTML += '<span class="icon-last"></span>';
    orderingHTML += '</button>';
    orderingHTML += '</td>';

    html += resourceHTML + orderingHTML + '</tr>';
    jQuery(html).appendTo(document.getElementById(tableID).tBodies[0]);
}

function getNextRowClass(lastPosition)
{
    var nextRowClass, lastRowClass;
    var row = document.getElementById('childRow' + lastPosition);
    if (row)
    {
        lastRowClass = row.className;
        if (lastRowClass === null)
        {
            nextRowClass = 'row1';
        }
        else if (lastRowClass === 'row0')
        {
            nextRowClass = 'row1';
        }
        else
        {
            nextRowClass = 'row0';
        }
    }
    else
    {
        nextRowClass = 'row0';
    }
}

/**
 *  deactivated forms for choosen
 */
window.onload = function ()
{
    var forms = document.getElementsByTagName("form"),
        controlGroups, childrenCG;
    for (var i = 0; i < forms.length; i++)
    {
        forms[i].onsubmit = function ()
        {
            return false
        };
    }
    moveButton();
};
