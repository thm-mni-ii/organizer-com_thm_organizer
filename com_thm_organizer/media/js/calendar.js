/**
 * @category    JavaScript library
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        calendar.js
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

"use strict";

var calendarIsVisible = false, calendar, dateField, year, month, table, months = [];

Date.prototype.getPresentationFormat = function ()
{
    var mm = (this.getMonth() + 1).toString(), // getMonth() is zero-based
        dd = this.getDate().toString();

    return [
        dd < 10 ? '0' + dd : dd,
        mm < 10 ? '0' + mm : mm,
        this.getFullYear()
    ].join('.');
};

/**
 * first function to call for the calendar. sets eventListeners for HTML-elements and variables.
 */
function initCalendar()
{
    window.table = document.getElementById('calendar-table');
    window.calendar = document.getElementById('choose-date');
    window.dateField = document.getElementById('date');
    window.month = document.getElementById('display-month');
    window.year = document.getElementById('display-year');
    window.months = [
        text.JANUARY,
        text.FEBRUARY,
        text.MARCH,
        text.MARCH,
        text.APRIL,
        text.MAY,
        text.JULY,
        text.AUGUST,
        text.SEPTEMBER,
        text.OCTOBER,
        text.NOVEMBER,
        text.DECEMBER
    ];

    showControls();

    /**
     * EventListener for the control buttons of the date input
     */
    window.dateField.addEventListener('change', setUpCalendar);
    document.getElementById('calendar-icon').addEventListener('click', showCalendar);

    document.getElementById('calendar-next-month').addEventListener('click', function ()
    {
        changeCalendarMonth(true);
    });
    document.getElementById('calendar-previous-month').addEventListener('click', function ()
    {
        changeCalendarMonth(false);
    });

    document.getElementById('today').addEventListener('click', function ()
    {
        insertDate();
        setUpCalendar();
    });

    document.getElementById('previous-month').addEventListener('click', function ()
    {
        changeSelectedDate(false, false);
    });

    document.getElementById('previous-day').addEventListener('click', function ()
    {
        changeSelectedDate(false, true);
    });

    document.getElementById('next-day').addEventListener('click', function ()
    {
        changeSelectedDate(true, true);
    });

    document.getElementById('next-month').addEventListener('click', function ()
    {
        changeSelectedDate(true, false);
    });
}

/**
 * increase or decrease displayed month in calendar table.
 *
 * @param increaseMonth boolean default = true
 */
function changeCalendarMonth(increaseMonth)
{
    var date = window.dateField.valueAsDate, increase = (typeof increaseMonth === 'undefined') ? true : increaseMonth;

    if (increase)
    {
        date.setMonth(date.getMonth() + 1);
    }
    else
    {
        date.setMonth(date.getMonth() - 1);
    }

    setUpCalendar(date);
}

/**
 * increase or decrease in steps of days or months in the current date in date field
 *
 * @param increase boolean default = true
 * @param day boolean default = true
 */
function changeSelectedDate(increase, day)
{
    /** in schedule.js */
    changeDate(increase, day);

    if (window.calendarIsVisible)
    {
        setUpCalendar();
    }

    updateSchedule();
}

/**
 * display calendar controls like changing to previous month.
 */
function showControls()
{
    var dateControls = document.getElementsByClassName('date-input')[0].getElementsByClassName('controls');

    for (var controlIndex = 0; controlIndex < dateControls.length; ++controlIndex)
    {
        dateControls[controlIndex].style.display = 'inline';
    }
}

/**
 * hides or shows the calendar, depending on its previous status.
 */
function showCalendar()
{
    window.calendar.style.visibility = (window.calendarIsVisible) ? 'hidden' : 'visible';
    window.calendarIsVisible = !window.calendarIsVisible;

    if (window.calendarIsVisible == true)
    {
        setUpCalendar();
    }
}

/**
 * hides the calendar.
 */
function hideCalendar()
{
    window.calendar.style.visibility = 'hidden';
    window.calendarIsVisible = false;
}

/**
 * builds the calendar (table), depending on a given date or the date field.
 *
 * @param optionalDate string
 */
function setUpCalendar(optionalDate)
{
    var date, parts;

    if (typeof optionalDate === 'string' && optionalDate.match(/\d{2}\.\d{2}\.\d{4}/))
    {
        parts = optionalDate.split('.', 3);
        /** found at https://wiki.selfhtml.org/wiki/JavaScript/Objekte/String/split */
        date = new Date(parseInt(parts[2], 10), parseInt(parts[1] - 1, 10), parseInt(parts[0], 10));
    }
    else if (optionalDate instanceof Date)
    {
        date = optionalDate;
    }
    else
    {
        date = window.dateField.valueAsDate;
    }

    resetTable();
    setUpCalendarHead(date);
    fillCalendar(date);
}

/**
 * displays month and year in calendar table head
 *
 * @param date Date object
 */
function setUpCalendarHead(date)
{
    window.month.innerHTML = window.months[date.getMonth()];
    window.year.innerHTML = date.getFullYear().toString();
}

/**
 * deletes the rows of the calendar table for refreshing.
 */
function resetTable()
{
    var tableBody = window.table.getElementsByTagName('tbody')[0],
        rowLength = window.table.getElementsByTagName('tr').length;

    for (var rowIndex = 0; rowIndex < rowLength; ++rowIndex)
    {
        /** '-1' represents the last row */
        tableBody.deleteRow(-1);
    }
}

/**
 * calendar table gets filled with days of the month, chosen by the given date
 *
 * @param date Date object
 */
function fillCalendar(date)
{
    /** inspired by https://wiki.selfhtml.org/wiki/JavaScript/Anwendung_und_Praxis/Monatskalender */
    var table = window.table.getElementsByTagName('tbody')[0], generalMonth = new Date(date.getFullYear(),
        date.getMonth(), 1), weekdayStart = generalMonth.getDay() == 0 ? 7 : generalMonth.getDay(),
        month = date.getMonth() + 1, year = date.getFullYear(), rows, row, cell,
        months30days = [4, 6, 9, 11], days = 31, day = 1;

    /** compute count of days */
    if (months30days.indexOf(month) != -1)
    {
        days = 30;
    }

    if (month == 2)
    {
        days = (year % 4 == 0) ? 29 : 28;
    }

    /** append rows to table */
    rows = Math.min(Math.ceil((days + generalMonth.getDay() - 1) / 7), 6);

    for (var rowIndex = 0; rowIndex <= rows; rowIndex++)
    {
        row = table.insertRow(rowIndex);
        for (var cellIndex = 0; cellIndex <= 6; cellIndex++)
        {
            cell = row.insertCell(cellIndex);
            if ((rowIndex == 0 && cellIndex < weekdayStart - 1) || day > days)
            {
                cell.innerHTML = ' ';
            }
            else
            {
                /** closure function needed, to give individual params to eventListeners inside of a for-loop */
                (function (day)
                {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'day';
                    button.addEventListener('click', function ()
                    {
                        insertDate(new Date(year, month - 1, day))
                    }, false);
                    button.innerHTML = day.toString();
                    cell.appendChild(button);
                }(day));

                day++;
            }
        }
    }
}

/**
 * the date chosen in the calendar table gets set in the date field
 *
 * @param date Date object
 */
function insertDate(date)
{
    var chosenDate = (typeof date === 'undefined') ? new Date() : date;

    window.dateField.valueAsDate = chosenDate;
    window.dateField.value = chosenDate.getPresentationFormat();

    hideCalendar();

    /** schedule.js */
    updateSchedule();
}