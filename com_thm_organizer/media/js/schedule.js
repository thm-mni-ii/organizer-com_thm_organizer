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

var activeDay, schedules, scheduleWrapper, isMobile, dateField;

jQuery(document).ready(function ()
{
    var startX, startY, touch, distX, distY, minDist;

    initSchedule();
    computeTableHeight();

    /** calendar.js */
    if (typeof initCalendar === 'function')
    {
        initCalendar();
    }
    else
    {
        document.getElementById('calendar-icon').style.display = 'none';
    }

    /**
     * swipe touch event handler changing the shown day and date
     * @see http://www.javascriptkit.com/javatutors/touchevents.shtml
     * @see http://www.html5rocks.com/de/mobile/touch/
     */
    window.scheduleWrapper.addEventListener('touchstart', function (event)
    {
        touch = event.changedTouches[0];
        startX = parseInt(touch.pageX);
        startY = parseInt(touch.pageY);
    });

    window.scheduleWrapper.addEventListener('touchend', function (event)
    {
        touch = event.changedTouches[0];
        distX = parseInt(touch.pageX) - startX;
        distY = parseInt(touch.pageY) - startY;
        minDist = 50;

        if (Math.abs(distX) > Math.abs(distY))
        {
            if (distX < -(minDist))
            {
                event.preventDefault();
                changeDate();
            }
            if (distX > minDist)
            {
                event.preventDefault();
                changeDate(false);
            }
        }
    });

    /**
     * EventListener for the control buttons of the date input
     * in JS for a better overview
     */
    document.getElementById('previous-month').addEventListener('click', function ()
    {
        changeDate(false, true);
    });

    document.getElementById('previous-day').addEventListener('click', function ()
    {
        changeDate(false);
    });

    document.getElementById('next-day').addEventListener('click', function ()
    {
        changeDate(true);
    });

    document.getElementById('next-month').addEventListener('click', function ()
    {
        changeDate(true, true);
    });

    /**
     * Change Checkbox behaviour for the checkboxes in the menubar
     * just one of the checkboxes is checked at the same time
     */
    jQuery('input[type="checkbox"]').on('change', function ()
    {
        jQuery('input[type="checkbox"]').not(this).prop('checked', false);
    });
});

/**
 * sets values for the start and shows only the actual day on mobile devices
 */
function initSchedule()
{
    var today = new Date();

    window.activeDay = today.getDay();
    window.schedules = document.getElementsByClassName('scheduler');
    window.scheduleWrapper = document.getElementById('scheduleWrapper');
    window.isMobile = (document.getElementsByClassName('tmpl-mobile').length > 0);
    window.dateField = document.getElementById('date');

    window.dateField.valueAsDate = today;
    setWeekday(today);

    if (!isBrowserSupportingDateInput())
    {
        window.dateField.value = parseDateToString(today);
    }
    if (window.isMobile)
    {
        showDay(window.activeDay);
    }
}

/**
 * goes one day for- or backward in the schedules and takes the date out of the input field with 'date' as id
 *
 * @param nextDate boolean goes forward by default, backward with false
 * @param nextMonth boolean indicates the step the date takes
 */
function changeDate(nextDate, nextMonth)
{
    var next = (typeof nextDate === 'undefined') ? true : nextDate,
        month = (typeof nextMonth === 'undefined') ? false : nextMonth,
        oldDate = window.dateField.valueAsDate,
        newDate = month ? changeMonth(oldDate, next) : changeDay(oldDate, next);

    window.dateField.valueAsDate = newDate;
    window.activeDay = newDate.getDay();
    setWeekday(newDate);

    // for browsers which doesn't update the value with the valueAsDate property for type=date
    if (!isBrowserSupportingDateInput())
    {
        window.dateField.value = parseDateToString(newDate);
    }

    if (window.isMobile)
    {
        showDay(window.activeDay);
    }
}

/**
 * gets the maximum count of days of the given month
 *
 * @param date  Date object
 *
 * @returns number (28|29|30|31)
 */
function getDaysInMonth(date)
{
    /** getMonth() is zero based: + 1 */
    var month = date.getMonth() + 1, months30days = [4, 6, 9, 11], daysInMonth = 31;

    if (months30days.indexOf(month) > -1)
    {
        daysInMonth = 30;
    }
    if (month == 2)
    {
        daysInMonth = (date.getFullYear() % 4 == 0) ? 29 : 28;
    }

    return daysInMonth;
}

/**
 * gets a string with dot-punctuation for a date object
 * created for browsers which don't support valueAsDate property
 */
function parseDateToString(dateObject)
{
    var day = dateObject.getDate(),
        dayString = (day < 10) ? "0" + day.toString() : day.toString(),
        month = dateObject.getMonth() + 1,
        monthString = (month < 10) ? "0" + month.toString() : month.toString(),
        year = dateObject.getFullYear();

    if (isBrowserSupportingDateInput())
    {
        return year.toString() + "-" + monthString + "-" + dayString;
    }
    return dayString + "." + monthString + "." + year.toString();
}

/**
 * increase or decrease the given date object for one day and
 * recognizes if it reaches the next month, years and/or sundays.
 *
 * @param date Date which should get changed
 * @param nextDate boolean goes forward by default, backwards with false
 *
 * @return Date object
 */
function changeDay(date, nextDate)
{
    var next = (typeof nextDate === 'undefined') ? true : nextDate, day = date.getDate(), month = date.getMonth(),
        year = date.getFullYear();

    do
    {
        if (next)
        {
            if (day == getDaysInMonth(date))
            {
                day = 1;
                if (month == 12)
                {
                    month = 1;
                    ++year;
                }
                else
                {
                    ++month;
                }
            }
            else
            {
                ++day;
            }
        }
        else
        {
            if (day == 1)
            {
                if (month == 1)
                {
                    month = 12;
                    --year;
                }
                else
                {
                    --month;
                }
                day = getDaysInMonth(new Date(year, month, day));
            }
            else
            {
                --day;
            }
        }

        /**
         * 12h because valueAsDate is GMT+0
         * @see https://austinfrance.wordpress.com/2012/07/09/html5-date-input-field-and-valueasdate-timezone-gotcha-3/
         */
        date = new Date(year, month, day, 12);

    }
    while (date.getDay() == 0);

    return date;
}

/**
 * goes one hole month in date-field for- or backward
 * and sets the visibility of schedule columns and the weekday
 *
 * @param date Date object
 * @param nextDate boolean goes forward by default, backwards with false
 * @return Date object
 */
function changeMonth(date, nextDate)
{
    var next = (typeof nextDate === 'undefined') ? true : nextDate, newMonth = date.getMonth();

    if (next)
    {
        newMonth = (newMonth == 12) ? 1 : ++newMonth;
    }
    else
    {
        newMonth = (newMonth == 1) ? 12 : --newMonth;
    }

    date.setMonth(newMonth);

    if (date.getDay() == 0)
    {
        date = changeDay(date, next);
    }

    return date;
}

/**
 * tests the support of the browser for the input type=date
 * @see http://stackoverflow.com/questions/10193294/how-can-i-tell-if-a-browser-supports-input-type-date
 *
 * @returns boolean
 */
function isBrowserSupportingDateInput()
{
    var input = document.createElement('input'), notValidDate = 'not-valid-date';

    input.setAttribute('type', 'date');
    input.setAttribute('value', notValidDate);

    return input.value !== notValidDate;
}

/**
 * changes the schedules div min-height as high as the maximum of all the lessons
 */
function computeTableHeight()
{
    var schedules = document.getElementsByClassName('scheduler'),
        rows, cell, lessonsInCell, lessonCount, emptyCellCount, maxLessons = 0, emptyBlocksInMaxLessons = 0,
        headerHeight, remPerEmptyRow, minHeight, remPerLesson, calcHeight, totalRemHeight;

    // counting the lessons in horizontal order
    for (var schedule = 0; schedule < schedules.length; ++schedule)
    {
        rows = schedules[schedule].getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        var rowLength = rows.length;
        var cellLength = rows[0].getElementsByTagName('td').length;

        // Monday to Fr/Sa/Su
        for (var day = 1; day < cellLength; ++day)
        {
            lessonCount = 0;
            emptyCellCount = 0;

            // Block 1 to ~6
            for (var block = 0; block < rowLength; ++block)
            {
                // To jump over break (cell length == 1)
                if (rows[block].getElementsByTagName('td').length > 1)
                {
                    cell = rows[block].getElementsByTagName('td')[day];
                    lessonsInCell = cell.getElementsByClassName('lesson').length;
                    if (lessonsInCell > 0)
                    {
                        lessonCount += lessonsInCell;
                    }
                    else
                    {
                        ++emptyCellCount;
                    }
                }
            }

            maxLessons = Math.max(lessonCount, maxLessons);
            /* TODO: mehr testen
             if (maxLessons < lessonCount)
             {
             maxLessons = lessonCount;
             emptyBlocksInMaxLessons = emptyCellCount;
             }*/
        }
    }

    headerHeight = 6; // Include caption & break
    remPerEmptyRow = 7;
    minHeight = window.isMobile ? 50 : 40;
    remPerLesson = window.isMobile ? 5 : 9;
    calcHeight = (maxLessons * remPerLesson) + (emptyBlocksInMaxLessons * remPerEmptyRow) + headerHeight;
    totalRemHeight = Math.max(calcHeight, minHeight);
    window.scheduleWrapper.style.minHeight = totalRemHeight + 'rem';
}

/**
 * sets the name of the weekday in the element with id = weekday
 *
 * @param date  Date object
 * @param english = false  boolean
 */
function setWeekday(date, english)
{
    var eng = (typeof english === 'undefined') ? false : english,
        days = ["Mo", "Di", "Mi", "Do", "Fr", "Sa"];

    if (eng)
    {
        days = ["Mo", "Tu", "We", "Th", "Fr", "Sa"];
    }

    document.getElementById('weekday').innerHTML = days[date.getDay() - 1];
}

/**
 * makes the selected day and the time column visible only
 *
 * @param visibleDay number 1 = monday (standard), 2 = tuesday...
 */
function showDay(visibleDay)
{
    var vDay = (typeof visibleDay === 'undefined') ? 1 : visibleDay, rows, heads, cells;

    for (var schedule = 0; schedule < window.schedules.length; ++schedule)
    {
        /** for chrome, which can not collapse cols or colgroups */
        rows = window.schedules[schedule].getElementsByTagName('tr');
        for (var row = 0; row < rows.length; ++row)
        {
            heads = rows[row].getElementsByTagName('th');
            for (var head = 0; head < heads.length; ++head)
            {
                if (head == vDay)
                {
                    heads[head].style.display = "table-cell";
                }
                else if (head != 0)
                {
                    heads[head].style.display = "none";
                }
            }
            cells = rows[row].getElementsByTagName('td');
            for (var cell = 0; cell < cells.length; ++cell)
            {
                if (cell == vDay)
                {
                    cells[cell].style.display = "table-cell";
                }
                else if (cell != 0)
                {
                    cells[cell].style.display = "none";
                }
            }
        }
    }
}