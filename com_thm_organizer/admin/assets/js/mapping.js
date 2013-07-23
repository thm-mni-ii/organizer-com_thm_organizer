var jq = jQuery.noConflict();

/**
 * Moves the values of the calling row up one row in the children table
 * 
 * @param {type} oldOrder
 * 
 * @returns {void}
 */
function moveUp(oldOrder)
{
    if (oldOrder <= 1)
    {
        return;
    }
    var currentOrder = getCurrentOrder();
    var reorderObjects = currentOrder.splice(oldOrder - 2, 2);

    // Set current to lower
    jq('#child' + (oldOrder - 1) + 'name').text(reorderObjects[1].name);
    jq('#child' + (oldOrder - 1)).val(reorderObjects[1].id);
    jq('#child' + (oldOrder - 1) + 'link').attr('href', reorderObjects[1].link);

    // Set current with lower
    jq('#child' + (oldOrder) + 'name').text(reorderObjects[0].name);
    jq('#child' + (oldOrder)).val(reorderObjects[0].id);
    jq('#child' + (oldOrder) + 'link').attr('href', reorderObjects[0].link);
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
    var currentOrder = getCurrentOrder();
    if (oldOrder >= currentOrder.length)
    {
        return;
    }
    var reorderObjects = currentOrder.splice(oldOrder - 1, 2);

    // Set current to lower
    jq('#child' + (oldOrder) + 'name').text(reorderObjects[1].name);
    jq('#child' + (oldOrder)).val(reorderObjects[1].id);
    jq('#child' + (oldOrder) + 'link').attr('href', reorderObjects[1].link);

    // Set current with lower
    newOrder = parseInt(oldOrder, 10) + 1;
    jq('#child' + newOrder + 'name').text(reorderObjects[0].name);
    jq('#child' + newOrder).val(reorderObjects[0].id);
    jq('#child' + newOrder + 'link').attr('href', reorderObjects[0].link);
}

/**
 * Sets the current values of a row to another row indicated by the value of the
 * order field
 * 
 * @param   {int} oldOrder
 * 
 * @returns  {void}
 */
function order(oldOrder)
{
    var currentOrder = getCurrentOrder();
    var newOrder = currentOrder[oldOrder - 1].order;
    if (newOrder === oldOrder)
    {
        return;
    }
    if (newOrder <= 0 || newOrder > currentOrder.length)
    {
        jq('#child' + oldOrder + 'order').val(oldOrder);
        alert(Joomla.JText._('COM_THM_ORGANIZER_INVALID_ORDER'));
        return;
    }
    var i;
    if (Math.min(newOrder, oldOrder) == newOrder)
    {
        for (i = newOrder - 1; i < oldOrder - 1; i++)
        {
            currentOrder[i].order++;
        }
    }
    else
    {
        for (i = oldOrder ; i < newOrder; i++)
        {
            currentOrder[i].order--;
        }
    }
    for (i = 0; i < currentOrder.length; i++)
    {
        jq('#child' + currentOrder[i].order + 'name').text(currentOrder[i].name);
        jq('#child' + currentOrder[i].order).val(currentOrder[i].id);
        jq('#child' + currentOrder[i].order + 'link').attr('href', currentOrder[i].link);
        jq('#child' + currentOrder[i].order + 'order').val(currentOrder[i].order);
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
    var rowCount = jq('#childList tr').length - 1;
    for (var i = 0; i < rowCount; i++)
    {
        var order = i + 1;
        currentOrder[i] = {};
        currentOrder[i].name = jq('#child' + order + 'name').text().trim();
        currentOrder[i].id = jq('#child' + order).val();
        currentOrder[i].link = jq('#child' + order + 'link').attr('href');
        currentOrder[i].order = jq('#child' + order + 'order').val();
    }
    return currentOrder;
}

/**
 * Removes a child row from the display
 * 
 * @param   {int}  rowNumber  the number of the row to be deleted
 * 
 * @returns  {void}
 */
function remove(rowNumber)
{
    "use strict";
    var currentOrder = getCurrentOrder();
    jq('#childRow' + rowNumber).remove();
    for (var i = rowNumber + 1 ; i <= currentOrder.length; i++)
    {
        var oneLower = i - 1;
        jq('#childRow' + i).attr('id', '#childRow' + oneLower);
        jq('#child' + i + 'name').text(currentOrder[oneLower].name);
        jq('#child' + i + 'name').attr('id', '#child' + oneLower + 'name');
        jq('#child' + i).val(currentOrder[oneLower].id);
        jq('#child' + i).attr('id', '#child' + oneLower);
        jq('#child' + i + 'link').attr('href', currentOrder[oneLower].link);
        jq('#child' + i + 'link').attr('id', '#child' + oneLower + 'link');
        jq('#child' + i + 'order').val(currentOrder[oneLower].order - 1);
        jq('#child' + i + 'order').attr('id', '#child' + oneLower + 'order');
    }
}
