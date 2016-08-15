/**
 * @category    JavaScript library
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        schedule.js
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

"use strict";

/**
 * ajax properties
 */
var ajaxGrid = null, url = 'index.php?option=com_thm_organizer&view=schedule_ajax&format=raw', weekdays = [];

/**
 * instantiates the scheduler element and the weekdays
 */
jQuery(document).ready(function ()
{
    /**
     * Event listener for Ajax selecting grids
     */
    document.getElementById('time-menu-item').addEventListener('click', function()
    {
        getGrids();
    });

    var firstScheduler = document.getElementsByClassName('scheduler')[0],
        headDays = firstScheduler.getElementsByTagName('thead')[0].getElementsByTagName('th');
    for (var index = 1; index < headDays.length; ++index)
    {
        weekdays.push(headDays[index].innerHTML);
    }
});

/**
 * create an XMLHTTPRequest object, get the saved grids
 */
function getGrids()
{
    /** global variable 'ajaxGrid' for access from other functions */
    ajaxGrid = new XMLHttpRequest();
    ajaxGrid.open('GET', url + '&task=grids', true);
    ajaxGrid.onreadystatechange = updateGridList;
    ajaxGrid.send(null);
}

/**
 * get the grids through ajax and add them as buttons with event handlers to the time-selection menu
 */
function updateGridList()
{
    if (ajaxGrid.readyState == 4 && ajaxGrid.status == 200)
    {
        var grids = JSON.parse(ajaxGrid.responseText),
            gridList = document.getElementById('time-selection').getElementsByTagName('ul')[0];

        removeChildren(gridList);

        for (var index = 0; index < grids.length; ++index)
        {
            var button = document.createElement('button'),
                grid = JSON.parse(grids[index].grid),
                listItem = document.createElement("li");

            button.setAttribute('type', 'button');
            button.setAttribute('id', grids[index].name_de);
            button.setAttribute('name', grids[index].name_de);
            button.innerHTML = grids[index].name_de;

            /**
             * anonymous functions necessary to bind a variable in the EventListener function while for-loops
             * @see http://stackoverflow.com/a/28633276/6355472
             */
            button.addEventListener('click', (function(grid)
                {
                    return function() {
                        setGridByClick(grid);
                    }
                })(grid)
            );
            listItem.appendChild(button);
            gridList.appendChild(listItem);
        }
    }
}

/**
 * onclick of a time button sets the specified grid on every schedule table
 *
 * @param grid   json data from database
 */
function setGridByClick(grid)
{
    var schedules = document.getElementsByClassName('scheduler');
    for (var scheduleIndex = 0; scheduleIndex < schedules.length; ++scheduleIndex)
    {
        var head = schedules[scheduleIndex].getElementsByTagName('thead')[0],
            rows = schedules[scheduleIndex].getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        setGridDays(head, grid);
        setGridTime(rows, grid);

        // TODO: prüfen ob break vorhanden sein soll (anhand json data?)
        // schedules[scheduleIndex].getElementsByTagName('table')[0].className = examTimes ? 'no-break' : '';
    }
}

/**
 * here the table head gets set up with the grids specified weekdays with start day and end day
 *
 * @param head  DOM element thead
 * @param grid  object with day data
 */
function setGridDays(head, grid)
{
    var headItems = head.getElementsByTagName('th'), currentDay = grid.start_day, endDay = grid.end_day;

    headItems[0].style.display = grid.hasOwnProperty('periods') ? '' : 'none';

    for (var thElement = 1; thElement < headItems.length; ++thElement)
    {
        if (thElement == currentDay && currentDay <= endDay)
        {
            headItems[thElement].innerHTML = weekdays[currentDay-1];
            ++currentDay;
        }
        else
        {
            headItems[thElement].innerHTML = '';
        }
    }
}

/**
 * sets the chosen times of the grid in the schedules tables
 *
 * @param rows   tr-DOM-Elements of table
 * @param grid   grid with start- and end times
 */
function setGridTime(rows, grid)
{
    var hasPeriods = grid.hasOwnProperty('periods'), ownElements = document.getElementsByClassName('own-time'),
        period = 1;

    for (var row = 0; row < rows.length; ++row)
    {
        var timeCell = rows[row].getElementsByTagName('td')[0];
        if (timeCell.className != 'break')
        {
            if (hasPeriods)
            {
                var startTime = grid.periods[period].start_time, endTime = grid.periods[period].end_time;
                startTime = startTime.replace(/(\d{2})(\d{2})/, "$1:$2");
                endTime = endTime.replace(/(\d{2})(\d{2})/, "$1:$2");
                timeCell.style.display = '';
                timeCell.innerHTML = startTime + "<br> - <br>" + endTime;

                ++period;
            }
            else
            {
                timeCell.style.display = 'none';
            }
        }
    }

    for (var element = 0; element < ownElements.length; ++element)
    {
        ownElements[element].style.display = hasPeriods ? 'none' : 'block';
    }
}

/**
 * removes all children elements of one given parent element
 * @param element object  parent element
 */
function removeChildren(element)
{
    var children = element.children, maxIndex = children.length - 1;

    for (var index = maxIndex; index >= 0; --index)
    {
        children[index].remove();
    }
}