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

jQuery(document).ready(function ()
{
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
    var startX, startY;

    window.scheduleWrapper.addEventListener('touchstart', function (event)
    {
        var touch = event.changedTouches[0];
        startX = parseInt(touch.pageX);
        startY = parseInt(touch.pageY);
    });

    window.scheduleWrapper.addEventListener('touchend', function (event)
    {
        var touch = event.changedTouches[0];
        var distX = parseInt(touch.pageX) - startX;
        var distY = parseInt(touch.pageY) - startY;
        var minDist = 50;

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

    document.getElementById('exam-time').addEventListener('click', function ()
    {
        changeTimes(true);
    });

    document.getElementById('own-time').addEventListener('click', function ()
    {
        ownTimes();
    });

    document.getElementById('standard-time').addEventListener('click', function ()
    {
        standardTimes();
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
    window.isMobile = (document.getElementsByClassName('tmpl-component').length > 0);
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
    var next = (typeof nextDate === 'undefined') ? true : nextDate;
    var month = (typeof nextMonth === 'undefined') ? false : nextMonth;

    var oldDate = window.dateField.valueAsDate;
    var newDate = month ? changeMonth(oldDate, next) : changeDay(oldDate, next);

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
    var month = date.getMonth() + 1;
    var months30days = [4, 6, 9, 11];
    var daysInMonth = 31;

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
    var day = dateObject.getDate();
    var dayString = (day < 10) ? "0" + day.toString() : day.toString();
    var month = dateObject.getMonth() + 1;
    var monthString = (month < 10) ? "0" + month.toString() : month.toString();
    var year = dateObject.getFullYear();

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
    var next = (typeof nextDate === 'undefined') ? true : nextDate;

    var day = date.getDate();
    var month = date.getMonth();
    var year = date.getFullYear();

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
    var next = (typeof nextDate === 'undefined') ? true : nextDate;
    var newMonth = date.getMonth();

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
    var input = document.createElement('input');
    var notValidDate = 'not-valid-date';

    input.setAttribute('type', 'date');
    input.setAttribute('value', notValidDate);

    return input.value !== notValidDate;
}

/**
 * changes the schedules div min-height as high as the maximum of all the lessons
 */
function computeTableHeight()
{
    var scheduleLessons = new Array(window.schedules.length);

    // counting the lessons in horizontal order
    for (var schedule = 0; schedule < window.schedules.length; ++schedule)
    {
        var rows = window.schedules[schedule].getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        var rowLessons = new Array(rows.length);
        for (var row = 0; row < rows.length; ++row)
        {
            var cells = rows[row].getElementsByTagName('td');
            var lessonCount = 0;
            for (var cell = 1; cell < cells.length; ++cell)
            {
                lessonCount = Math.max(cells[cell].getElementsByClassName('lesson').length, lessonCount);
            }
            rowLessons[row] = lessonCount;
        }
        scheduleLessons[schedule] = rowLessons;
    }

    // total sum of all lessons in vertical order
    var maxLessons = 0, noLessonInRows = 0;
    for (var line = 0; line < scheduleLessons.length; ++line)
    {
        var sLessons = 0, emptyRows = 0;
        for (var column = 0; column < scheduleLessons[line].length; ++column)
        {
            sLessons += scheduleLessons[line][column];
            if (scheduleLessons[line][column] == 0)
            {
                ++emptyRows;
            }
        }
        if (sLessons > maxLessons)
        {
            noLessonInRows = emptyRows;
        }
        maxLessons = Math.max(sLessons, maxLessons);
    }

    var headerHeight = 60; //include break
    var pixelPerEmptyRow = 55;
    var minHeight = window.isMobile ? 650 : 500;
    var pixelPerLesson = window.isMobile ? 120 : 140;
    var calcHeight = (maxLessons * pixelPerLesson) + (noLessonInRows * pixelPerEmptyRow) + headerHeight;
    var totalPixelHeight = Math.max(calcHeight, minHeight);
    window.scheduleWrapper.style.minHeight = totalPixelHeight + 'px';
}

/**
 * sets the name of the weekday in the element with id = weekday
 *
 * @param date  Date object
 * @param english = false  boolean
 */
function setWeekday(date, english)
{
    var eng = (typeof english === 'undefined') ? false : english;

    var days = ["Mo", "Di", "Mi", "Do", "Fr", "Sa"];

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
    var vDay = (typeof visibleDay === 'undefined') ? 1 : visibleDay;

    for (var schedule = 0; schedule < window.schedules.length; ++schedule)
    {
        /** for chrome, which can not collapse cols or colgroups */
        var rows = window.schedules[schedule].getElementsByTagName('tr');
        for (var row = 0; row < rows.length; ++row)
        {
            var heads = rows[row].getElementsByTagName('th');
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
            var cells = rows[row].getElementsByTagName('td');
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

/**
 * switches between the visibility of exam times and normal semester times
 *
 * @param examTimes = false boolean  change to regular exam time
 */
function changeTimes(examTimes)
{
    var eTimes = (typeof examTimes === 'undefined') ? false : examTimes;
    var exams = document.getElementsByClassName('time-exams');

    for (var exam = 0; exam < exams.length; ++exam)
    {
        exams[exam].style.display = eTimes ? 'table-cell' : 'none';
    }

    var semester = document.getElementsByClassName('time-semester');
    for (var sem = 0; sem < semester.length; ++sem)
    {
        semester[sem].style.display = eTimes ? 'none' : 'table-cell';
    }

    var owns = document.getElementsByClassName('own-time');
    if (owns[0].style.display != 'none')
    {
        for (var own = 0; own < owns.length; ++own)
        {
            owns[own].style.display = 'none';
        }
    }

    for (var schedule = 0; schedule < window.schedules.length; ++schedule)
    {
        window.schedules[schedule].getElementsByTagName('table')[0].className = examTimes ? 'no-break' : '';
    }
}

/**
 * own times are shown and regular times are hidden
 */
function ownTimes()
{
    var owns = document.getElementsByClassName('own-time');
    for (var own = 0; own < owns.length; ++own)
    {
        owns[own].style.display = 'block'
    }

    for (var schedule = 0; schedule < window.schedules.length; ++schedule)
    {
        window.schedules[schedule].getElementsByTagName('table')[0].className = 'invisible-time';
    }
}

/**
 * jumps back to the standard time visibility settings of window.page
 * now semester-times
 */
function standardTimes()
{
    // TODO: standard time grid aus Datenbank nehmen
    changeTimes(false);
}
