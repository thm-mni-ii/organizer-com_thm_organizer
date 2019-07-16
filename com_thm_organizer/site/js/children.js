"use strict";

/**
 * Calls function getCheckedItems() and calls the close button click event to close the iFrame
 *
 * @param divID the id of the div
 * @param type the type of the source
 */
function closeIframeWindow(divID, type)
{
    getCheckedItems(divID, type);
    jQuery('button.close').trigger('click');
}

/**
 *  Deactivates chosen forms and adds buttons for the selection of children to the form.
 */
window.onload = function () {

    const forms = document.getElementsByTagName('form'),
        childToolbar = jQuery('#children-toolbar'),
        poolButton = jQuery('#toolbar-popup-list').detach(),
        subjectButton = jQuery('#toolbar-popup-book').detach();

    let controlGroups, childrenCG;

    poolButton.appendTo(childToolbar);

    if (subjectButton.length)
    {
        subjectButton.appendTo(childToolbar);
    }

    for (var i = 0; i < forms.length; i++)
    {
        forms[i].onsubmit = function () {
            return false
        };
    }
};

/**
 * Increments the indexing of all subsequent rows, and adds replaces the indexed row with a blank.
 *
 * @param {int} position the index at which a blank row should be added
 * @returns {void} modifies the dom
 */
function addBlankChild(position)
{
    let children = getChildren(),
        length = children.length,
        newOrder,
        oldIndex;

    // Add a new row to buffer the run off.
    addChildRow(length);

    // Increments existing rows starting from the last one.
    while (position <= length)
    {
        newOrder = length + 1;
        oldIndex = length - 1;

        cloneChild(newOrder, children[oldIndex]);
        length--;
    }

    // Empties the information from the current row.
    clearChildData(position);
}

/**
 * Add a new child row to the end of the table.
 *
 * @param {int} lastPosition the index of the last child table element
 * @param {string} tableID   the html id attribute of the table
 * @param {string} resourceID
 * @param {string} resourceName
 * @param {string} resourceType
 *
 * @returns {void}  adds a new row to the end of the table
 */
function addChildRow(lastPosition, resourceID = '', resourceName = '', resourceType = '')
{
    let mID = 0,
        name = '',
        icon = '',
        rawID,
        link,
        nextRowNumber,
        html,
        resourceHTML,
        orderingHTML;

    if (resourceID !== '')
    {
        mID = resourceID;
    }
    if (resourceName !== '')
    {
        name = resourceName;
    }

    rawID = resourceID.substring(0, resourceID.length - 1);

    if (resourceType !== '')
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

    nextRowNumber = parseInt(lastPosition, 10) + 1;

    html = '<tr id="childRow' + nextRowNumber + '">';

    resourceHTML = '<td class="child-name">';
    resourceHTML += '<a id="child' + nextRowNumber + 'Link" href="' + link + '">';
    resourceHTML += '<span id="child' + nextRowNumber + 'Icon" class="' + icon + '"></span>';
    resourceHTML += '<span id="child' + nextRowNumber + 'Name">' + name + '</span>';
    resourceHTML += '</a>';
    resourceHTML += '<input id="child' + nextRowNumber + '" type="hidden" value="' + mID + '" name="child' + nextRowNumber + '">';
    resourceHTML += '</td>';

    orderingHTML = '<td class="child-order">';

    orderingHTML += '<button class="btn btn-small" onclick="setFirst(' + nextRowNumber + ');" title="';
    orderingHTML += Joomla.JText._('THM_ORGANIZER_MAKE_FIRST') + '">';
    orderingHTML += '<span class="icon-first"></span>';
    orderingHTML += '</button>';

    orderingHTML += '<button class="btn btn-small" onclick="moveChildUp(' + nextRowNumber + ');" title="';
    orderingHTML += Joomla.JText._('THM_ORGANIZER_MOVE_UP') + '">';
    orderingHTML += '<span class="icon-previous"></span>';
    orderingHTML += '</button>';

    orderingHTML += '<input type="text" name="child' + nextRowNumber + 'Order" ';
    orderingHTML += 'id="child' + nextRowNumber + 'Order" size="2" value="' + nextRowNumber + '" class="text-area-order" ';
    orderingHTML += 'onchange="moveChildToIndex(' + nextRowNumber + ');">';

    orderingHTML += '<button class="btn btn-small" onclick="addBlankChild(' + nextRowNumber + ');" title="';
    orderingHTML += Joomla.JText._('THM_ORGANIZER_ADD_EMPTY') + '">';
    orderingHTML += '<span class="icon-download"></span>';
    orderingHTML += '</button>';

    orderingHTML += '<button class="btn btn-small" onclick="trash(' + nextRowNumber + ');" title="';
    orderingHTML += Joomla.JText._('THM_ORGANIZER_DELETE') + '">';
    orderingHTML += '<span class="icon-trash"></span>';
    orderingHTML += '</button>';

    orderingHTML += '<button class="btn btn-small" onclick="moveChildDown(' + nextRowNumber + ');" title="';
    orderingHTML += Joomla.JText._('THM_ORGANIZER_MOVE_DOWN') + '">';
    orderingHTML += '<span class="icon-next"></span>';
    orderingHTML += '</button>';

    orderingHTML += '<button class="btn btn-small" onclick="setLast(' + nextRowNumber + ');" title="';
    orderingHTML += Joomla.JText._('THM_ORGANIZER_MAKE_LAST') + '">';
    orderingHTML += '<span class="icon-last"></span>';
    orderingHTML += '</button>';

    orderingHTML += '</td>';

    html += resourceHTML + orderingHTML + '</tr>';
    jQuery(html).appendTo(document.getElementById('childList').tBodies[0]);
}

/**
 * Replaces data with empty values for the row at the given position.
 *
 * @param {int} position the row position to clear
 * @returns {void} modifies the dom
 */
function clearChildData(position)
{
    jQuery('#child' + position + 'Icon').attr('class', '');
    jQuery('#child' + position + 'Name').text('');
    jQuery('#child' + position).val('');
    jQuery('#child' + position + 'Link').attr('href', "");
    jQuery('#child' + position + 'Order').val(position);
}

/**
 * Replaced the data at the new position with the data from the old position.
 *
 * @param {int} position the position to whose data will be replaced with cloned data
 * @param {Object} child the element whose data will be used for cloning
 * @returns {void} modifies the dom
 */
function cloneChild(position, child)
{
    jQuery('#child' + position + 'Icon').attr('class', (child.class));
    jQuery('#child' + position + 'Name').text(child.name);
    jQuery('#child' + position).val(child.id);
    jQuery('#child' + position + 'Link').attr('href', child.link);
    jQuery('#child' + position + 'Order').val(position);
}

/**
 * Gets the selected items from the list and adds them to the children table.
 *
 * @param {string} divID the id of the div
 * @param {string} type the type of the source
 * @return {void} modifies the dom
 */
function getCheckedItems(divID, type)
{
    const iFrame = jQuery('iframe');
    let children, id, name;

    jQuery(divID + ' input:checked', iFrame.contents()).each(function () {
        children = getChildren();
        id = jQuery(this).val() + type;
        name = jQuery(jQuery(this).parent().parent().children()[1]).html();
        addChildRow(children.length, id, name, type);
    });
}

/**
 * Retrieves an array of objects mapping current form values
 *
 * @returns {array} the map of the current children and their values
 */
function getChildren()
{
    // -1 Because of the header row.
    const childCount = jQuery('#childList').find('tr').length - 1;
    let currentChildren = [],
        index,
        order;

    for (index = 0; index < childCount; index++)
    {
        order = index + 1;
        currentChildren[index] = {};
        currentChildren[index].class = jQuery('#child' + order + 'Icon').attr('class').trim();
        currentChildren[index].name = jQuery('#child' + order + 'Name').text().trim();
        currentChildren[index].id = jQuery('#child' + order).val();
        currentChildren[index].link = jQuery('#child' + order + 'Link').attr('href');
        currentChildren[index].order = jQuery('#child' + order + 'Order').val();
    }
    return currentChildren;
}

/**
 * Moves the values of the calling row down one row in the children table
 *
 * @param {int} currentOrder
 *
 * @returns {void}
 */
function moveChildDown(position)
{
    let children = getChildren(), currentOrder = parseInt(position, 10), current, next;

    // Child is currently the last child or the child is a blank
    if (currentOrder >= children.length || (children.length === currentOrder + 1 && children[currentOrder - 1].name === ""))
    {
        return;
    }

    current = children[currentOrder - 1];
    next = children[currentOrder];

    // Set next child to current index
    cloneChild(currentOrder, next);

    // Set current child to next index
    cloneChild(currentOrder + 1, current);
}

/**
 * Places the current child an explicit position.
 *
 * @param {int} currentPosition
 *
 * @returns  {void}
 */
function moveChildToIndex(currentPosition)
{
    let children = getChildren(),
        length = children.length,
        child = children[currentPosition - 1],
        secondPosOrder = jQuery('#child' + currentPosition + 'Order'),
        requestedPosition = secondPosOrder.val();

    requestedPosition = parseInt(requestedPosition, 10);

    if (isNaN(requestedPosition) === true || requestedPosition > length || (Number(requestedPosition) === length && child.name === ""))
    {
        secondPosOrder.val(currentPosition);
        return;
    }

    if (currentPosition < requestedPosition)
    {
        shiftUp(currentPosition, requestedPosition, children);
    }
    else
    {
        shiftDown(currentPosition, requestedPosition, children);
    }

    cloneChild(requestedPosition, child);
}

/**
 * Moves the values of the calling row up one row in the children table
 *
 * @param {int} position
 *
 * @returns {void}
 */
function moveChildUp(position)
{
    let children = getChildren(), currentOrder = Number(position), current, previous;

    // Child is currently the first child or the child is a blank
    if (currentOrder <= 1 || (children.length === currentOrder && children[currentOrder - 2].name === ""))
    {
        return;
    }

    previous = children[currentOrder - 2];
    current = children[currentOrder - 1];

    // Set current child to previous index
    cloneChild(currentOrder - 1, current);

    // Set previous child to current index
    cloneChild(currentOrder, previous);

}

/**
 * Moves the child to the first position in the table. Moves down all children which previously were ordered before it.
 *
 * @param {int} position the position of the child to be moved
 * @returns {void} modifies the dom
 */
function setFirst(position)
{
    const children = getChildren(), child = children[position - 1];

    if (child.name !== "")
    {
        shiftDown(position, 1, children);

        cloneChild(1, child);
    }
}

/**
 * Moves the child to the last position in the table. Moves up all children subsequent to the child being moved.
 *
 * @param {int} position the position of the child to be moved
 * @returns {void} modifies the dom
 */
function setLast(position)
{
    const children = getChildren(), child = children[position - 1];

    if (child.name !== "")
    {
        shiftUp(position, children.length, children);

        cloneChild(children.length, child);
    }
}

/**
 * Shifts all children subsequent to the position down.
 *
 * @param {int} position the highest child position which will be replaced
 * @param {int} stopPosition the position which defines the end of the shift process
 * @param {array} children the map of the children
 * @returns {void} modifies the dom
 */
function shiftDown(position, stopPosition, children)
{
    let newPosition, sourcePosition;

    while (position > stopPosition)
    {
        newPosition = position;
        sourcePosition = position - 2;

        cloneChild(newPosition, children[sourcePosition]);
        position--;
    }
}

/**
 * Shift all children subsequent to the position up one.
 *
 * @param {int} position the lowest child position which will be replaced
 * @param {int} stopPosition the position which defines the end of the shift process
 * @param {array} children the map of the children
 * @returns {void} modifies the dom
 */
function shiftUp(position, stopPosition, children)
{
    while (position < stopPosition)
    {
        cloneChild(position, children[position]);
        position++;
    }
}

/**
 * Removes a child element from the form.
 *
 * @param {int} position the current position of the child to be removed
 * @returns  {void} modifies the dom
 */
function trash(position)
{
    let children = getChildren(),
        length = children.length;

    shiftUp(position, length, children);

    jQuery('#childRow' + length).remove();
}