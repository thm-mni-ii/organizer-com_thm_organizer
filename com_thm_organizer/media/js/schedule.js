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

var activeDay, schedules, scheduleWrapper, isMobile, dateField, weekdays, weekdaysShort, mySchedule, Schedule,
    ajaxSelection = null, ajaxLessons = null, ajaxSchedule = null, scheduleObjects = [],
    url = 'index.php?option=com_thm_organizer&view=schedule_ajax&format=raw';

/**
 * Schedule class for saving params and update the already existing schedules.
 *
 * @param id unique string
 */
Schedule = function (id)
{
    this.id = id; // string
    this.table = null; // HTMLTableElement
    this.title = 'Default Schedule'; // title in form selection
    this.task = '';
};

jQuery(document).ready(function ()
{
    var startX, startY;

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
        var touch = event.changedTouches[0];
        startX = parseInt(touch.pageX);
        startY = parseInt(touch.pageY);
    });

    window.scheduleWrapper.addEventListener('touchend', function (event)
    {
        var touch = event.changedTouches[0], minDist = 50,
            distX = parseInt(touch.pageX) - startX,
            distY = parseInt(touch.pageY) - startY;

        if (Math.abs(distX) > Math.abs(distY))
        {
            if (distX < -(minDist))
            {
                event.preventDefault();
                event.stopPropagation();
                changeDate();
                updateSchedule();
            }
            if (distX > minDist)
            {
                event.preventDefault();
                event.stopPropagation();
                changeDate(false);
                updateSchedule();
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
        updateSchedule();
    });

    document.getElementById('previous-day').addEventListener('click', function ()
    {
        changeDate(false);
        updateSchedule();
    });

    document.getElementById('next-day').addEventListener('click', function ()
    {
        changeDate(true);
        updateSchedule();
    });

    document.getElementById('next-month').addEventListener('click', function ()
    {
        changeDate(true, true);
        updateSchedule();
    });

    jQuery('#grid').chosen().change(setGridByClick);

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
    var today = new Date(), mySchedule = new Schedule(text.MY_SCHEDULE);

    //TODO: mySchedule füllen -> backend function für user schedules anlegen usw.
    window.scheduleObjects.push(mySchedule);
    window.activeDay = today.getDay();
    window.schedules = document.getElementsByClassName('scheduler');
    window.scheduleWrapper = document.getElementById('scheduleWrapper');
    window.isMobile = checkMobile();
    window.dateField = document.getElementById('date');
    window.dateField.valueAsDate = today;
    window.url += '&departmentID=' + text.departmentID;
    window.weekdays = [
        text.MONDAY,
        text.TUESDAY,
        text.WEDNESDAY,
        text.THURSDAY,
        text.FRIDAY,
        text.SATURDAY,
        text.SUNDAY
    ];
    window.weekdaysShort = [
        text.MONDAY_SHORT,
        text.TUESDAY_SHORT,
        text.WEDNESDAY_SHORT,
        text.THURSDAY_SHORT,
        text.FRIDAY_SHORT,
        text.SATURDAY_SHORT,
        text.SUNDAY_SHORT
    ];

    setWeekday(today);

    if (!browserSupportsDate())
    {
        window.dateField.value = parseDateToString(today);
    }
    if (window.isMobile)
    {
        showDay(window.activeDay);
    }

    /**
     * form behaviour -> only show field, by selecting the 'parent' before
     */
    jQuery('#category').chosen().change(function ()
    {
        var chosenCategory = document.getElementById('category').value;

        switch (chosenCategory)
        {
            case 'program':
                onlyShowFormInput('program-input');
                break;
            case 'roomtype':
                onlyShowFormInput('room-type-input');
                break;
            case 'teacher':
                onlyShowFormInput('teacher-input');
                break;
            default:
                console.log('searching default category...');
        }

        getRequest(chosenCategory);
    });

    jQuery('#program').chosen().change(function ()
    {
        onlyShowFormInput(['program-input', 'pool-input']);
        getRequest('pool');
    });

    jQuery('#roomtype').chosen().change(function ()
    {
        onlyShowFormInput(['room-type-input', 'room-input']);
        getRequest('room');
    });

    jQuery('#pool').chosen().change(function ()
    {
        getLessonRequest('pool');
    });

    jQuery('#teacher').chosen().change(function ()
    {
        getLessonRequest('teacher');
    });

    jQuery('#room').chosen().change(function ()
    {
        getLessonRequest('room');
    });

    jQuery('#schedules').chosen().change(function ()
    {
        var scheduleInput = document.getElementById(jQuery('#schedules').val());

        /** to show the schedule after this input field (by css) */
        scheduleInput.checked = 'checked';
    });
}

/**
 * TODO: updaten auf endgültige Unterscheidung
 * true when css says, that the page should be in mobile mode.
 *
 * @returns boolean
 */
function checkMobile()
{
    return document.getElementsByClassName('tmpl-mobile').length > 0;
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
    if (!browserSupportsDate())
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
 * @param date Date object
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

    if (browserSupportsDate())
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
function browserSupportsDate()
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
 * @param date Date object
 */
function setWeekday(date)
{
    document.getElementById('weekday').innerHTML = weekdaysShort[date.getDay() - 1];
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

/**
 * onclick of a grid selection sets the selected grid on every schedule
 */
function setGridByClick()
{
    var schedules = document.getElementsByClassName('scheduler'),
        grid = JSON.parse(document.getElementById('grid').value);

    for (var scheduleIndex = 0; scheduleIndex < schedules.length; ++scheduleIndex)
    {
        setGridDays(schedules[scheduleIndex], grid);
        setGridTime(schedules[scheduleIndex], grid);

        // TODO: prüfen ob break vorhanden sein soll (anhand json data?)
        // schedules[scheduleIndex].getElementsByTagName('table')[0].className = examTimes ? 'no-break' : '';
    }
}

/**
 * here the table head changes to the grids specified weekdays with start day and end day
 *
 * @param schedule DOM element table
 * @param grid object with day data
 */
function setGridDays(schedule, grid)
{
    var head = schedule.getElementsByTagName('thead')[0],
        headItems = head.getElementsByTagName('th'), currentDay = grid.startDay, endDay = grid.endDay;

    headItems[0].style.display = grid.hasOwnProperty('periods') ? '' : 'none';

    for (var thElement = 1; thElement < headItems.length; ++thElement)
    {
        if (thElement == currentDay && currentDay <= endDay)
        {
            headItems[thElement].innerHTML = weekdays[currentDay - 1];
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
 * @param schedule   table-DOM-Element
 * @param grid grid with start- and end times
 */
function setGridTime(schedule, grid)
{
    var rows = schedule.getElementsByTagName('tbody')[0].getElementsByTagName('tr'),
        hasPeriods = grid.hasOwnProperty('periods'), ownElements = document.getElementsByClassName('own-time'),
        period = 1, timeCell, startTime, endTime;

    for (var row = 0; row < rows.length; ++row)
    {
        timeCell = rows[row].getElementsByTagName('td')[0];
        if (timeCell.className != 'break')
        {
            if (hasPeriods)
            {
                startTime = grid.periods[period].startTime;
                startTime = startTime.replace(/(\d{2})(\d{2})/, "$1:$2");
                endTime = grid.periods[period].endTime;
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

/******* ********* *******/
/******* Ajax part *******/
/******* ********* *******/

/**
 * starts an Ajax request to fill form fields with values
 *
 * @param resource  string
 */
function getRequest(resource)
{
    var task;

    switch (resource)
    {
        case 'program':
            task = '&task=getPrograms';
            break;
        case 'pool':
            task = '&task=getPools' + '&programIDs=' + getSelectedValues('program');
            break;
        case 'roomtype':
            task = '&task=getRoomTypes';
            break;
        case 'room':
            task = '&task=getRooms' + '&typeID=' + getSelectedValues('roomtype');
            break;
        case 'teacher':
            task = '&task=getTeachers';
            break;
        default:
            console.log('searching default category...');
    }

    /** global variable for catching responds in other functions */
    ajaxSelection = new XMLHttpRequest();
    ajaxSelection.open('GET', url + task, true);
    ajaxSelection.onreadystatechange = updateForm;
    ajaxSelection.send(null);
}

/**
 * updates form fields with data from Ajax requests
 */
function updateForm()
{
    var values, category, fieldID, formField, option, nextName, optionWithVersion = false;

    if (ajaxSelection.readyState == 4 && ajaxSelection.status == 200)
    {
        values = JSON.parse(ajaxSelection.responseText);
        category = ajaxSelection.responseURL.match(/\&task\=get(\w+)s/);
        fieldID = category[1].toLowerCase();
        formField = document.getElementById(fieldID);
        removeChildren(formField);

        for (var index = 0; index < values.length; ++index)
        {
            option = document.createElement('option');
            option.setAttribute('value', values[index].id);
            option.innerHTML = values[index].name;

            /** check for the need of a version number in case of two equally program names (name ordering required) */
            if (category == 'program')
            {
                nextName = (values.length > index + 1) ? values[index + 1].name : '';
                if (optionWithVersion)
                {
                    option.innerHTML += ' ' + values[index].version;
                    optionWithVersion = false;
                }
                else if (nextName == values[index].name)
                {
                    option.innerHTML += ' ' + values[index].version;
                    optionWithVersion = true;
                }
                else
                {
                    optionWithVersion = false;
                }
            }

            formField.appendChild(option);
        }

        formField.removeAttribute('disabled');
        jQuery('#' + fieldID).chosen('destroy').chosen();
    }
}

/**
 * starts an Ajax request to get lessons for the selected resource
 *
 * @param resource  string
 */
function getLessonRequest(resource)
{
    var task = '', id = getSelectedValues(resource), schedule, field,
        chosenDate = document.getElementById('date').valueAsDate;

    if (id.match(/^\d+$/) == null)
    {
        return;
    }

    switch (resource)
    {
        case 'pool':
            task = '&task=getLessonsByPools' + '&poolIDs=';
            break;
        case 'room':
            task = '&task=getLessonsByRooms' + '&roomIDs=';
            break;
        case 'teacher':
            task = '&task=getLessonsByTeacher' + '&teacherID=';
            break;
        default:
            console.log('searching default category...');
            return;
    }

    task += id;
    task += '&date=' + new Date(chosenDate + " UTC").toISOString().slice(0, 10);

    schedule = getScheduleById(resource + id);
    if (schedule)
    {
        updateSchedule(id);
        return;
    }

    schedule = new Schedule(resource + id);
    schedule.task = task;
    field = document.getElementById(resource);
    schedule.title = field.options[field.selectedIndex].text;
    scheduleObjects.push(schedule);

    /** load only one day for mobile */
    if (checkMobile())
    {
        task += '&oneDay=' + 'true';
    }

    /** global variable for catching responds in other functions */
    ajaxLessons = new XMLHttpRequest();
    ajaxLessons.open('GET', url + task, true);
    ajaxLessons.onreadystatechange = updateLessons;
    ajaxLessons.send(null);
}

/**
 * Creates a new schedule with lessons out of the Ajax request by selecting pools
 */
function updateLessons()
{
    var lessons, schedule, scheduleTable;

    if (ajaxLessons.readyState == 4 && ajaxLessons.status == 200)
    {
        schedule = getScheduleByResponse(ajaxLessons.responseURL);

        if (!schedule)
        {
            return;
        }

        lessons = JSON.parse(ajaxLessons.responseText);

        scheduleTable = createScheduleTable(schedule);
        schedule.table = scheduleTable;
        fillTimes(scheduleTable);
        insertLessons(scheduleTable, lessons);
        addScheduleToSelection(schedule);
    }
}

/**
 * create a new entry in the dropdown field for selecting a schedule
 *
 * @param schedule Schedule object
 */
function addScheduleToSelection(schedule)
{
    var option = document.createElement('option');

    option.innerHTML = schedule.title;
    option.value = schedule.id;
    option.selected = 'selected';
    document.getElementById('schedules').appendChild(option);

    /** updating chosen.js */
    jQuery('#schedules').chosen('destroy').chosen();
}

/**
 * Creates a table DOM-element with an input and label for selecting it and a caption with the given title.
 * It gets appended to the scheduleWrapper.
 *
 * @param schedule Schedule object
 * @returns HTMLTableElement
 */
function createScheduleTable(schedule)
{
    var scheduleWrapper = document.getElementById('scheduleWrapper'),
        input, div, table, tbody, row, weekEnd = 7;

    /** create input field for selecting this schedule */
    input = document.createElement('input');
    input.className = 'scheduler-input';
    input.type = 'radio';
    input.setAttribute('id', schedule.id);
    input.setAttribute('name', 'schedules');
    input.setAttribute('checked', 'checked');
    scheduleWrapper.appendChild(input);

    /** making a new schedule table */
    div = document.createElement('div');
    div.setAttribute('id', schedule.id + '-schedule');
    div.setAttribute('class', 'scheduler');
    table = document.createElement('table');
    div.appendChild(table);
    scheduleWrapper.appendChild(div);

    tbody = document.createElement('tbody');
    table.appendChild(tbody);

    /** filled with rows and cells (by -1 for last position */
    if (text.defaultTimes.hasOwnProperty('periods'))
    {
        for (var periods in text.defaultTimes.periods)
        {
            row = tbody.insertRow(-1);

            for (var firstDay = 1; firstDay < weekEnd; ++firstDay)
            {
                row.insertCell(-1);
            }
        }
    }
    else
    {
        row = tbody.insertRow(-1);

        for (var weekStart = 1; weekStart < weekEnd; ++weekStart)
        {
            row.insertCell(-1);
        }
    }

    return table;
}

/**
 * Creates a lesson which means a div element filled by a JSON data
 *
 * @param lessonData  JSON
 * @returns HTMLDivElement
 */
function createLesson(lessonData)
{
    var lesson, ownTimeSpan, nameSpan, moduleSpan, personSpan, plusButton, plusIcon;

    lesson = document.createElement('div');
    lesson.className = 'lesson';

    if (lessonData.lessonDelta)
    {
        lesson.className += ' old';
    }

    if (lessonData.startTime && lessonData.endTime)
    {
        ownTimeSpan = document.createElement('span');
        ownTimeSpan.className = 'own-time';
        ownTimeSpan.innerHTML = lessonData.startTime + ' - ' + lessonData.endTime;
        lesson.appendChild(ownTimeSpan);
    }

    if (lessonData.subjectName)
    {
        nameSpan = document.createElement('span');
        nameSpan.className = 'name';
        nameSpan.innerHTML = lessonData.subjectName;
        lesson.appendChild(nameSpan);
    }

    if (lessonData.subjectNo)
    {
        moduleSpan = document.createElement('span');
        moduleSpan.className = 'module';
        moduleSpan.innerHTML = lessonData.subjectNo;
        lesson.appendChild(moduleSpan);
    }

    if (lessonData.teacherName)
    {
        personSpan = document.createElement('span');
        personSpan.className = 'person';
        personSpan.innerHTML = lessonData.teacherName;
        lesson.appendChild(personSpan);
    }

    plusButton = document.createElement('button');
    plusButton.className = 'add-lesson';
    plusIcon = document.createElement('span');
    plusIcon.className = 'icon-plus-2';
    plusButton.appendChild(plusIcon);
    lesson.appendChild(plusButton);

    return lesson;
}

/**
 * Insert table head and side cells with time data
 *
 * @param table HTMLTableElement
 */
function fillTimes(table)
{
    var thead, tr, th;

    /** head with days of the week */
    thead = table.createTHead();
    tr = thead.insertRow(0);

    for (var headIndex = 0; headIndex < 6; ++headIndex)
    {
        th = document.createElement('th');
        th.innerHTML = (headIndex == 0) ? text.TIME : weekdays[headIndex - 1];
        tr.appendChild(th);
    }

    setGridDays(table, text.defaultTimes);
    setGridTime(table, text.defaultTimes);
}

/**
 * inserts lessons into a schedule table
 *
 * @param table HTMLTableElement | object
 * @param lessons JSON data
 */
function insertLessons(table, lessons)
{
    var lesson, lessonDate, lessonElement, block, nextBlockExist = false, nextBlock, blockDateTime,
        startTime, nextStartTime, nextBlockDateTime, lessonDateTime, lessonDay, cells,
        blocks = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (var lessonIndex = 0; lessonIndex < lessons.length; ++lessonIndex)
    {
        lesson = lessons[lessonIndex];
        lessonDate = lesson.schedule_date;
        lessonElement = createLesson(lessons[lessonIndex]);

        for (var blockIndex = 0; blockIndex < blocks.length; ++blockIndex)
        {
            /**
             * comparison of start times (lesson <-> time grid)
             * @see http://stackoverflow.com/a/6212346/6355472
             */
            lessonDateTime = Date.parse(lessonDate + ' ' + lesson.startTime);
            block = blocks[blockIndex].getElementsByTagName('td')[0];
            startTime = block.innerHTML.match(/(\d{2}:\d{2})/)[0];
            blockDateTime = Date.parse(lessonDate + ' ' + startTime);

            nextBlock = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr')[blockIndex + 1];
            if (nextBlock !== undefined)
            {
                nextStartTime = nextBlock.innerHTML.match(/(\d{2}:\d{2})/)[0];
                nextBlockDateTime = Date.parse(lessonDate + ' ' + nextStartTime);
                nextBlockExist = true;
            }
            else
            {
                nextBlockExist = false;
            }

            /**
             * insert lesson when time of lesson is same or later than the actual block and earlier than the next block.
             */
            if (lessonDateTime >= blockDateTime && (!nextBlockExist || lessonDateTime < nextBlockDateTime))
            {
                lessonDay = new Date(lessonDateTime).getDay();

                if (blocks[blockIndex + 1] !== undefined)
                {
                    cells = blocks[blockIndex + 1].getElementsByTagName('td');
                    cells[lessonDay].appendChild(lessonElement);
                }
                /** found right block - go to next lesson */
                break;
            }
        }
    }
    computeTableHeight();
}

/**
 * Sends an Ajax request for the active schedule, to update it.
 * @param id   string
 */
function updateSchedule(id)
{
    var updateTask,
        scheduleID = (id != undefined) ? id : getSelectedValues('schedules'),
        schedule = getScheduleById(scheduleID),
        selectedDate = document.getElementById('date').valueAsDate,
        date = selectedDate.getFullYear() + "-" + (selectedDate.getMonth() + 1) + "-" + selectedDate.getDate();

    if (!schedule)
    {
        return;
    }

    updateTask = schedule.task.replace(/(date\=)\d{4}\-\d{2}\-\d{2}/, "$1" + date);

    /** load only one day for mobile */
    if (checkMobile())
    {
        updateTask += '&oneDay=' + 'true';
    }

    /** global variable for catching responds in other functions */
    ajaxSchedule = new XMLHttpRequest();
    ajaxSchedule.open('GET', url + updateTask, true);
    ajaxSchedule.onreadystatechange = insertUpdatedScheduleData;
    ajaxSchedule.send(null);
}

/**
 * the active schedule is shown with new information, like another date
 */
function insertUpdatedScheduleData()
{
    var lessons, schedule;

    if (ajaxSchedule.readyState == 4 && ajaxSchedule.status == 200)
    {
        lessons = JSON.parse(ajaxSchedule.responseText);
        schedule = getScheduleByResponse(ajaxSchedule.responseURL);

        if (!schedule)
        {
            return;
        }

        resetSchedule(schedule.table);
        insertLessons(schedule.table, lessons);
    }
}

/**
 * removes all lessons of a schedule for updating it
 *
 * @param table HTMLTableElement | object
 */
function resetSchedule(table)
{
    var lessons = table.getElementsByClassName('lesson'), lessonCount = lessons.length - 1;

    for (var index = lessonCount; index >= 0; --index)
    {
        lessons[index].parentNode.removeChild(lessons[index]);
    }
}

/**
 * every div with the class 'input-wrapper' gets hidden, when it is not named as param.
 *
 * @param fieldIDsToShow string|Array
 */
function onlyShowFormInput(fieldIDsToShow)
{
    var form = document.getElementById('schedule-form'), fields = form.getElementsByClassName('input-wrapper'),
        field, fieldElement, fieldToShow;

    for (var fieldIndex = 0; fieldIndex < fields.length; ++fieldIndex)
    {
        field = fields[fieldIndex];

        if (fieldIDsToShow instanceof Array)
        {
            if (fieldIDsToShow.indexOf(field.id) == -1)
            {
                field.style.display = '';
            }
            else
            {
                field.style.display = 'inline-block';
                fieldToShow = field;
            }
        }
        else
        {
            if (field.id != fieldIDsToShow)
            {
                field.style.display = '';
            }
            else
            {
                field.style.display = 'inline-block';
                fieldToShow = field;
            }
        }
    }

    /** gets enabled after Ajax request came in */
    fieldElement = fieldToShow.getElementsByTagName('select')[0];
    fieldElement.disabled = true;
    jQuery('#' + fieldElement.id).chosen('destroy').chosen();
}

/**
 * removes all children elements of one given parent element
 *
 * @param element object parent element
 */
function removeChildren(element)
{
    var children = element.children, maxIndex = children.length - 1;

    for (var index = maxIndex; index >= 0; --index)
    {
        children[index].remove();
    }
}

/**
 * gets the concatenated and selected values of one multiple form field
 *
 * @param fieldID  int
 * @returns string
 */
function getSelectedValues(fieldID)
{
    var chosenOptions = document.getElementById(fieldID).selectedOptions, selectedValues = '';

    for (var selectIndex = 0; selectIndex < chosenOptions.length; ++selectIndex)
    {
        selectedValues += chosenOptions[selectIndex].value;

        if (chosenOptions[selectIndex + 1] !== undefined)
        {
            selectedValues += ",";
        }
    }

    return selectedValues;
}

/**
 * gets the Schedule object which belongs to the given response url from an Ajax request
 * @param responseUrl  string
 * @return Schedule | false  default = user schedule ('my schedule')
 */
function getScheduleByResponse(responseUrl)
{
    var id = responseUrl.match(/\&(\w+)IDs\=(\d+)/), schedule = scheduleObjects[0], scheduleID;

    /** check for teacher response */
    if (id == null)
    {
        id = responseUrl.match(/\&(teacher)ID\=(\d+)/);

        if (id == null)
        {
            return false;
        }
    }

    scheduleID = id[1] + id[2];

    scheduleObjects.forEach(function (element)
    {
        if (element.id == scheduleID)
        {
            schedule = element;
        }
    });

    return schedule;
}

/**
 * gets the Schedule object which belongs to the given id
 * @param id  string
 * @return Schedule | false
 */
function getScheduleById(id)
{
    var schedule = false;

    scheduleObjects.forEach(function (element)
    {
        if (element.id == id)
        {
            schedule = element;
        }
    });

    return schedule;
}