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

// _,-*#%&.* CALENDAR functions *.&%#*-,_ //

function initCalendar()
{
    // when browser is supporting date input, hide the selfmade calendar and change dates format
    if (isBrowserSupportingDateInput())
    {
        document.getElementById('calendar-icon').style.display = 'none';
    }
    insertDate();
    window.calVisible = false;
}

function showCalendar()
{
    document.getElementById('choose-date').style.visibility = (window.calVisible == false) ? 'visible' : 'hidden';
    window.calVisible = !this.calVisible;

    if (window.calVisible == true)
    {
        setUpCalendar();
    }
}

function hideCalendar()
{
    document.getElementById('choose-date').style.visibility = 'hidden';
    window.calVisible = false;
}

function setUpCalendar(optionalDate)
{
    var date = (typeof optionalDate === 'undefined') ? document.getElementById('date').value : optionalDate;
    var chosenDate = new Date();

    if (date != '')
    {
        var parts = date.split('.', 3);
        /** found at https://wiki.selfhtml.org/wiki/JavaScript/Objekte/String/split */
        chosenDate = new Date(parseInt(parts[2], 10), parseInt(parts[1] - 1, 10), parseInt(parts[0], 10));
    }

    resetTable();
    setUpCalendarHead(chosenDate);
    fillCalendar(chosenDate);
}

function resetTable()
{
    var table = document.getElementById('calendar-table').getElementsByTagName('tbody')[0];
    var rowCount = table.getElementsByTagName('tr').length;
    for (var r = rowCount; r > 0; r--)
    {
        table.deleteRow(r - 1);
    }
}

function setUpCalendarHead(chosenDate)
{
    document.getElementById('display-month').innerHTML = castMonth(chosenDate.getMonth());
    document.getElementById('display-year').innerHTML = chosenDate.getFullYear().toString();
}

function fillCalendar(chosenDate)
{
    /** inspired by https://wiki.selfhtml.org/wiki/JavaScript/Anwendung_und_Praxis/Monatskalender */
    var generalMonth = new Date(chosenDate.getFullYear(), chosenDate.getMonth(), 1);
    var weekdayStart = generalMonth.getDay() == 0 ? 7 : generalMonth.getDay();
    var month = chosenDate.getMonth() + 1;
    var year = chosenDate.getFullYear();
    var months30days = [4, 6, 9, 11];
    var days = 31;

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
    var table = document.getElementById('calendar-table').getElementsByTagName('tbody')[0];
    var day = 1;
    var rows = Math.min(Math.ceil((days + generalMonth.getDay() - 1) / 7), 6);
    for (var r = 0; r <= rows; r++)
    {
        var row = table.insertRow(r);
        for (var c = 0; c <= 6; c++)
        {
            var cell = row.insertCell(c);
            if ((r == 0 && c < weekdayStart - 1) || day > days)
            {
                cell.innerHTML = ' ';
            }
            else
            {
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

function insertDate(date)
{
    if (date === undefined)
    {
        date = new Date();
    }

    document.getElementById('date').valueAsDate = date;

    if (!isBrowserSupportingDateInput())
    {
        var day = date.getDate();
        var dayString = (day < 10) ? "0" + day.toString() : day.toString();
        var month = date.getMonth() + 1;
        var monthString = (month < 10) ? "0" + month.toString() : month.toString();
        var year = date.getFullYear();

        document.getElementById('date').value = dayString + "." + monthString + "." + year.toString();
    }

    hideCalendar();
}

function previousMonth()
{
    var year = document.getElementById('display-year').innerHTML;
    var oldMonth = document.getElementById('display-month').innerHTML;
    var newMonth = castMonth(oldMonth);
    var newDate = "1." + newMonth + "." + year;

    setUpCalendar(newDate);
}

function nextMonth()
{
    var year = document.getElementById('display-year').innerHTML;
    var oldMonth = document.getElementById('display-month').innerHTML;
    var newMonth = castMonth(oldMonth) + 2; //because of the array
    var newDate = "1." + newMonth + "." + year;

    setUpCalendar(newDate);
}

function castMonth(month)
{
    var months = ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August",
        "September", "Oktober", "November", "Dezember"];

    if (typeof month === 'string')
    {
        return months.indexOf(month);
    }

    if (typeof month === 'number')
    {
        return months[month];
    }

    return undefined;
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