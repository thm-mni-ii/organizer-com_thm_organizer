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

var scheduleWrapper, isMobile, dateField, weekdays, Schedule, Schedules, scheduleObjects, datePattern,
    ajaxSelection = null, ajaxLessons = null, ajaxSchedule = null, ajaxSave = null, ajaxUser = null,
    url = 'index.php?option=com_thm_organizer&view=schedule_ajax&format=raw';

/**
 * Schedule class for saving params and update the already existing schedules.
 *
 * @param id unique string
 */
Schedule = function (id)
{
    this.id = id; // string
    this.table = document.getElementsByTagName('table')[1]; // HTMLTableElement
    this.lessons = []; // JSON data of lessons
    this.title = 'Default Schedule'; // title in form selection
    this.task = '';
};

/**
 * Container for all schedule objects
 * including functions to get the right schedule by id or response url.
 */
Schedules = function ()
{
    this.schedules = []; // Schedule objects

    /**
     * adds a schedule to the list
     * @param schedule Schedule object
     */
    this.addSchedule = function (schedule)
    {
        this.schedules.push(schedule);
    };

    /**
     * gets the Schedule object which belongs to the given id
     * @param id  string
     * @return Schedule | false
     */
    this.getScheduleById = function (id)
    {
        var schedule = false;

        this.schedules.forEach(function (element)
        {
            if (element.id == id)
            {
                schedule = element;
            }
        });

        return schedule;
    };

    /**
     * gets the Schedule object which belongs to the given response url from an Ajax request
     * @param responseUrl  string
     * @return Schedule | false  default = user schedule ('my schedule')
     */
    this.getScheduleByResponse = function (responseUrl)
    {
        var id = responseUrl.match(/&(\w+)IDs=(\d+)/), schedule = this.schedules[0], scheduleID;

        /** check for teacher response */
        if (id == null)
        {
            id = responseUrl.match(/&(teacher)ID=(\d+)/);

            if (id == null)
            {
                return false;
            }
        }

        scheduleID = id[1] + id[2];

        this.schedules.forEach(function (element)
        {
            if (element.id == scheduleID)
            {
                schedule = element;
            }
        });

        return schedule;
    };
};

/**
 * get date string in the components specified format.
 * @see http://stackoverflow.com/a/3067896/6355472
 *
 * @returns string
 */
Date.prototype.getPresentationFormat = function ()
{
    var date = text.dateFormat,
        day = this.getDate(),
        dayLong = day < 10 ? '0' + day : day,
        month = this.getMonth() + 1, // getMonth() is zero-based
        monthLong = month < 10 ? '0' + month : month,
        year = this.getYear(),
        yearLong = this.getFullYear();

    /** insert day */
    date = date.replace(/j/, day.toString());
    date = date.replace(/d/, dayLong);
    /** insert month */
    date = date.replace(/n/, month.toString());
    date = date.replace(/m/, monthLong);
    /** insert year */
    date = date.replace(/Y/, yearLong.toString());
    date = date.replace(/y/, year.toString());

    return date;
};

/**
 * get date string in format yyyy-mm-dd.
 * needed for input type='date' supporting browsers to handle a value.
 *
 * @returns string
 */
Date.prototype.getWireFormat = function ()
{
    var mm = this.getMonth() + 1, // getMonth() is zero-based
        dd = this.getDate();

    return [
        this.getFullYear(),
        mm < 10 ? '0' + mm : mm,
        dd < 10 ? '0' + dd : dd
    ].join('-'); // padding
};

Array.prototype.getStartTime = function (index)
{
    var match;
    if (this[index] == undefined)
    {
        return false;
    }
    match = this[index].match(/^(\d{2}:\d{2})/);
    if (match == null)
    {
        return false;
    }
    return match[1];
};

Array.prototype.getEndTime = function (index)
{
    var match;
    if (this[index] == undefined)
    {
        return false;
    }
    match = this[index].match(/(\d{2}:\d{2})$/);
    if (match == null)
    {
        return false;
    }
    return match[1];
};

jQuery(document).ready(function ()
{
    var startX, startY;

    initSchedule();
    computeTableHeight();
    setDatePattern();

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

                if (window.isMobile)
                {
                    showDay();
                }
                updateSchedule();
            }
            if (distX > minDist)
            {
                event.preventDefault();
                event.stopPropagation();
                changeDate(false);

                if (window.isMobile)
                {
                    showDay();
                }
                updateSchedule();
            }
        }
    });

    jQuery('#grid').chosen().change(setGridByClick);
    jQuery('#date').change(updateSchedule);

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

    window.scheduleWrapper = document.getElementById('scheduleWrapper');
    window.isMobile = window.matchMedia("(max-width: 677px)").matches;
    window.dateField = document.getElementById('date');
    window.dateField.valueAsDate = today;
    window.url += '&departmentID=' + text.departmentID;
    window.weekdays = [
        text.MONDAY_SHORT,
        text.TUESDAY_SHORT,
        text.WEDNESDAY_SHORT,
        text.THURSDAY_SHORT,
        text.FRIDAY_SHORT,
        text.SATURDAY_SHORT,
        text.SUNDAY_SHORT
    ];

    window.scheduleObjects = new Schedules;

    /** no 'guest'-table? -> create user schedule */
    if (window.scheduleWrapper.getElementsByTagName('table').length == 0)
    {
        createUsersSchedule();
    }

    if (!browserSupportsDate())
    {
        window.dateField.value = today.getPresentationFormat();

        /** calendar.js */
        if (typeof initCalendar === 'function')
        {
            initCalendar();
        }
    }

    if (window.isMobile)
    {
        showDay();
    }
}

/**
 * gets the users schedule, write it into a HTML table and add it to schedules selection
 */
function createUsersSchedule()
{
    var schedule = new Schedule('user');

    schedule.lessons = getUsersSchedule();
    schedule.title = text.MY_SCHEDULE;
    schedule.task = '&task=getUsersSchedule';
    schedule.table = createScheduleTable(schedule);
    insertTableHead(schedule.table);
    setGridTime(schedule.table);
    addScheduleToSelection(schedule);
    computeTableHeight();

    window.scheduleObjects.addSchedule(schedule);
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
 * goes one day for- or backward in the schedules and takes the date out of the input field with 'date' as id
 *
 * @param nextDate boolean goes forward by default, backward with false
 * @param dayStep boolean indicates the step the date takes
 */
function changeDate(nextDate, dayStep)
{
    var increaseDate = (typeof nextDate === 'undefined') ? true : nextDate,
        day = (typeof dayStep === 'undefined') ? true : dayStep, scheduleDate = getDateFieldsDateObject();

    if (increaseDate)
    {
        if (day)
        {
            scheduleDate.setDate(scheduleDate.getDate() + 1);
        }
        else
        {
            scheduleDate.setMonth(scheduleDate.getMonth() + 1);
        }

        /** jump over sunday */
        if (scheduleDate.getDay() == 0)
        {
            scheduleDate.setDate(scheduleDate.getDate() + 1);
        }
    }
    /** decrease date */
    else
    {
        if (day)
        {
            scheduleDate.setDate(scheduleDate.getDate() - 1);
        }
        else
        {
            scheduleDate.setMonth(scheduleDate.getMonth() - 1);
        }

        /** jump over sunday */
        if (scheduleDate.getDay() == 0)
        {
            scheduleDate.setDate(scheduleDate.getDate() - 1);
        }
    }

    window.dateField.valueAsDate = scheduleDate;

    // for browsers which doesn't update the value with the valueAsDate property for type=date
    if (!browserSupportsDate())
    {
        window.dateField.value = scheduleDate.getPresentationFormat();
    }
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
 * makes the selected day and the time column visible only
 *
 * @param visibleDay number 1 = monday, 2 = tuesday...
 */
function showDay(visibleDay)
{
    var vDay = (typeof visibleDay === 'undefined') ? window.dateField.valueAsDate.getDay(): visibleDay,
        schedules = window.scheduleWrapper.getElementsByClassName('scheduler'), rows, heads, cells;

    for (var schedule = 0; schedule < schedules.length; ++schedule)
    {
        /** for browsers, which can not collapse cols or colgroups */
        rows = schedules[schedule].getElementsByTagName('tr');
        for (var row = 0; row < rows.length; ++row)
        {
            heads = rows[row].getElementsByTagName('th');
            for (var head = 1; head < heads.length; ++head)
            {
                if (head == vDay)
                {
                    heads[head].style.display = "table-cell";
                }
                else
                {
                    heads[head].style.display = "none";
                }
            }
            cells = rows[row].getElementsByTagName('td');
            for (var cell = 1; cell < cells.length; ++cell)
            {
                if (cell == vDay)
                {
                    cells[cell].style.display = "table-cell";
                }
                else
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
    var grid = JSON.parse(getSelectedValues('grid'));

    // TODO: prüfen ob break vorhanden sein soll (anhand json data?)

    scheduleObjects.schedules.forEach(
        function (schedule)
        {
            setGridDays(schedule.table, grid);
            setGridTime(schedule.table, grid);
            resetSchedule(schedule.table);
            insertLessons(schedule.table, schedule.lessons);
        }
    );

    computeTableHeight();
}

/**
 * here the table head changes to the grids specified weekdays with start day and end day
 *
 * @param table DOM element table
 * @param timeGrid object with grid data
 */
function setGridDays(table, timeGrid)
{
    var grid = (typeof timeGrid === 'undefined') ? JSON.parse(getSelectedValues('grid')) : timeGrid,
        head = table.getElementsByTagName('thead')[0], headItems = head.getElementsByTagName('th'),
        headerDate = window.dateField.valueAsDate, day = headerDate.getDay(),
        currentDay = grid.startDay, endDay = grid.endDay;

    /** set date to monday of the same week */
    if (day == 0)
    {
        headerDate.setDate(headerDate.getDate() - 6);
    }
    else
    {
        /** sunday is 0, so we add a one for monday */
        headerDate.setDate(headerDate.getDate() - day + 1);
    }

    /** show TIME header on the left side ? */
    headItems[0].style.display = grid.hasOwnProperty('periods') ? '' : 'none';

    /** fill thead with days of week */
    for (var thElement = 1; thElement < headItems.length; ++thElement)
    {
        if (thElement == currentDay && currentDay <= endDay)
        {
            headItems[thElement].innerHTML = weekdays[currentDay - 1] + ' (' + headerDate.getPresentationFormat() + ')';
            headerDate.setDate(headerDate.getDate() + 1);
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
 * @param table   table-DOM-Element
 * @param timeGrid grid with start- and end times
 */
function setGridTime(table, timeGrid)
{
    var grid = (typeof timeGrid === 'undefined') ? JSON.parse(getSelectedValues('grid')) : timeGrid,
        rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr'),
        hasPeriods = grid.hasOwnProperty('periods'),
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
}

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
        category = ajaxSelection.responseURL.match(/&task=get(\w+)s/);
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
    var task = '', id = getSelectedValues(resource), schedule, field;

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
    task += '&date=' + getDateFieldString();
    task += window.isMobile ? '&oneDay=true' : '';

    schedule = scheduleObjects.getScheduleById(resource + id);
    if (schedule)
    {
        updateSchedule(id);
        return;
    }

    schedule = new Schedule(resource + id);
    schedule.task = task;
    field = document.getElementById(resource);
    schedule.title = field.options[field.selectedIndex].text;
    scheduleObjects.addSchedule(schedule);

    /** global variable for catching responds in other functions */
    ajaxLessons = new XMLHttpRequest();
    ajaxLessons.open('GET', url + task, true);
    ajaxLessons.onreadystatechange = updateLessons;
    ajaxLessons.send(null);
}

/**
 * Creates a new schedule with lessons out of the Ajax request by selecting a schedule
 */
function updateLessons()
{
    var schedule;

    if (ajaxLessons.readyState == 4 && ajaxLessons.status == 200)
    {
        schedule = scheduleObjects.getScheduleByResponse(ajaxLessons.responseURL);

        if (!schedule)
        {
            return;
        }

        schedule.lessons = JSON.parse(ajaxLessons.responseText);
        schedule.table = createScheduleTable(schedule);

        insertTableHead(schedule.table);
        setGridTime(schedule.table);
        insertLessons(schedule.table, schedule.lessons);
        addScheduleToSelection(schedule);
        computeTableHeight();
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

            for (var firstDay = 0; firstDay < weekEnd; ++firstDay)
            {
                row.insertCell(-1);
            }
        }
    }
    else
    {
        row = tbody.insertRow(-1);

        for (var weekStart = 0; weekStart < weekEnd; ++weekStart)
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
    var lesson, ownTimeSpan, nameSpan, moduleSpan, personSpan, saveMenu, saveSemester, savePeriod, saveInstance,
        saveButton, saveIcon, deleteButton, deleteIcon;

    lesson = document.createElement('div');
    lesson.id = lessonData.id + '-' + lessonData.schedule_date + '-' + lessonData.startTime;
    lesson.className = 'lesson';

    if (lessonData.lessonDelta)
    {
        lesson.className += ' old';
    }

    if (lessonData.startTime && lessonData.endTime)
    {
        ownTimeSpan = document.createElement('span');
        ownTimeSpan.className = 'own-time';
        ownTimeSpan.innerHTML =
            lessonData.startTime.match(/^(\d{2}:\d{2})/)[1] + ' - ' +
            lessonData.endTime.match(/^(\d{2}:\d{2})/)[1];
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

    /** one save menu for each lesson */
    saveMenu = document.createElement('div');
    saveMenu.className = 'save-menu';

    saveSemester = document.createElement('button');
    saveSemester.className = 'save-semester';
    saveSemester.innerHTML = text.SAVE_SEMESTER;
    saveSemester.addEventListener('click', function ()
    {
        saveLesson(text.CONFIG_SAVE_SEMESTER, lessonData);
        saveMenu.style.display = 'none';
    });
    savePeriod = document.createElement('button');
    savePeriod.className = 'save-period';
    savePeriod.innerHTML = text.SAVE_PERIOD;
    savePeriod.addEventListener('click', function ()
    {
        saveLesson(text.CONFIG_SAVE_PERIOD, lessonData);
        saveMenu.style.display = 'none';
    });
    saveInstance = document.createElement('button');
    saveInstance.className = 'save-instance';
    saveInstance.innerHTML = text.SAVE_INSTANCE;
    saveInstance.addEventListener('click', function ()
    {
        saveLesson(text.CONFIG_SAVE_INSTANCE, lessonData);
        saveMenu.style.display = 'none';
    });

    saveMenu.appendChild(saveSemester);
    saveMenu.appendChild(savePeriod);
    saveMenu.appendChild(saveInstance);

    saveButton = document.createElement('button');
    saveButton.className = 'add-lesson';
    saveIcon = document.createElement('span');
    saveIcon.className = 'icon-plus';
    saveButton.appendChild(saveIcon);
    saveButton.addEventListener('click', function ()
    {
        saveMenu.style.display = 'block';
    });

    deleteButton = document.createElement('button');
    deleteButton.className = 'delete-lesson';
    deleteIcon = document.createElement('span');
    deleteIcon.className = 'icon-delete';
    deleteButton.appendChild(deleteIcon);
    deleteButton.addEventListener('click', function ()
    {
        //TODO: delete lesson
    });

    lesson.appendChild(saveButton);
    lesson.appendChild(deleteButton);
    lesson.appendChild(saveMenu);

    return lesson;
}

/**
 * Insert table head and side cells with time data
 *
 * @param table HTMLTableElement
 */
function insertTableHead(table)
{
    var thead = table.createTHead(), tr = thead.insertRow(0), weekend = 7, headerDate = getDateFieldsDateObject(),
        th, thText;

    /** set date to monday */
    headerDate.setDate(headerDate.getDate() - headerDate.getDay());

    for (var headIndex = 0; headIndex < weekend; ++headIndex)
    {
        th = document.createElement('th');
        thText = weekdays[headIndex - 1] + ' (' + headerDate.getPresentationFormat() + ')';
        th.innerHTML = (headIndex == 0) ? text.TIME : thText;
        tr.appendChild(th);
        headerDate.setDate(headerDate.getDate() + 1);
    }
}

/**
 * inserts lessons into a schedule table
 *
 * @param table HTMLTableElement | object
 * @param lessons array[objects] - JSON data
 */
function insertLessons(table, lessons)
{
    var lessonsInDay, headCells = table.getElementsByTagName('tr')[0].getElementsByTagName('th');

    for (var cellNumber = 1; cellNumber < headCells.length; ++cellNumber)
    {
        lessonsInDay = lessons.filter(
            function (lesson)
            {
                var lessonDay = new Date(lesson.schedule_date).getPresentationFormat();
                return lessonDay == window.datePattern.exec(headCells[cellNumber].innerHTML)[0];
            }
        );

        lessonsInDay.sort(
            function (a, b)
            {
                return a.startTime > b.startTime;
            }
        );

        fillColumn(table, cellNumber, lessonsInDay);
    }
}

/**
 * fills the specified column with all lessons of the array which matches the time on the left side
 *
 * @param table HTMLTableElement
 * @param colNumber int
 * @param lessons array[object]
 */
function fillColumn(table, colNumber, lessons)
{
    var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr'), timeCell,
        cells = [], times = [], lessonIndex = 0, cellLessons, lessonElement, blockStart, blockEnd;

    /** collect td elements and times */
    for (var rowIndex = 0; rowIndex < rows.length; ++rowIndex)
    {
        cells.push(rows[rowIndex].getElementsByTagName('td')[colNumber]);
        timeCell = rows[rowIndex].getElementsByTagName('td')[0];
        if (timeCell.style.display != 'none')
        {
            times.push(timeCell.innerHTML);
        }
    }

    /** no times on the left side - every lesson appears in the first row */
    if (times.length == 0)
    {
        lessons.forEach(
            function (lesson)
            {
                ++lessonIndex;
                lessonElement = createLesson(lesson);
                lessonElement.getElementsByClassName('own-time')[0].style.display = 'block';
                cells[0].appendChild(lessonElement);
            }
        )
    }
    /** insert lessons in cells */
    else
    {
        for (var cellIndex = 0; cellIndex < cells.length; ++cellIndex)
        {
            blockStart = times.getStartTime(cellIndex);
            blockEnd = times.getEndTime(cellIndex);

            cellLessons = lessons.filter(
                function (lesson)
                {
                    var lessonStartTime = lesson.startTime.match(/^(\d{2}:\d{2})/)[1];
                    return lessonStartTime >= blockStart && lessonStartTime < blockEnd;
                }
            );

            cellLessons.forEach(
                function (lesson)
                {
                    var lessonElement = createLesson(lesson),
                        lessonStartTime = lesson.startTime.match(/^(\d{2}:\d{2})/)[1],
                        lessonEndTime = lesson.endTime.match(/^(\d{2}:\d{2})/)[1],
                        nextBlock = times[cellIndex + 1] !== undefined,
                        lessonNextBlock;

                    /** lesson fits inside block but has slightly other times */
                    if (lessonStartTime != blockStart || lessonEndTime != blockEnd)
                    {
                        lessonElement.getElementsByClassName('own-time')[0].style.display = 'block';
                    }

                    /** lesson fits into next block too */
                    if (nextBlock && lessonEndTime >= times.getStartTime(cellIndex + 1))
                    {
                        lessonNextBlock = createLesson(lesson);
                        lessonNextBlock.getElementsByClassName('own-time')[0].style.display = 'block';
                        cells[cellIndex + 1].appendChild(lessonNextBlock);
                    }

                    cells[cellIndex].appendChild(lessonElement);
                }
            );
        }
    }
}

/**
 * Sends an Ajax request for the active schedule, to update it.
 * @param id   string
 */
function updateSchedule(id)
{
    var updateTask = '', schedule, dateString = getDateFieldString(),
        mobileTask = window.isMobile ? '&oneDay=true' : '';

    /** update all schedules */
    if (id == undefined || typeof id !== 'string')
    {
        scheduleObjects.schedules.forEach(
            function (schedule)
            {
                updateTask = schedule.task.replace(/(date=)\d{4}\-\d{2}\-\d{2}/, "$1" + dateString);
                updateTask += mobileTask;

                /** synchronous request handling or some schedules do not get their update */
                ajaxSchedule = new XMLHttpRequest();
                ajaxSchedule.open('GET', url + updateTask, false);
                ajaxSchedule.onreadystatechange = insertUpdatedScheduleData;
                ajaxSchedule.send(null);
            }
        );
    }
    /** update the given schedule only */
    else
    {
        schedule = window.scheduleObjects.getScheduleById(id);
        if (!schedule)
        {
            return;
        }

        updateTask = schedule.task.replace(/(date=)\d{4}\-\d{2}\-\d{2}/, "$1" + dateString);
        updateTask += mobileTask;

        /** global variable for catching responds in other functions */
        ajaxSchedule = new XMLHttpRequest();
        ajaxSchedule.open('GET', url + updateTask, true);
        ajaxSchedule.onreadystatechange = insertUpdatedScheduleData;
        ajaxSchedule.send(null);
    }
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
        schedule = scheduleObjects.getScheduleByResponse(ajaxSchedule.responseURL);

        if (!schedule)
        {
            return;
        }

        resetSchedule(schedule.table);
        setGridDays(schedule.table);
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
 * returns the current date field value as a string connected by minus.
 *
 * @returns string
 */
function getDateFieldString()
{
    if (browserSupportsDate())
    {
        return window.dateField.valueAsDate.getWireFormat();
    }

    return window.dateField.value.replace(/(\d{2})\.(\d{2})\.(\d{4})/, "$3" + "-" + "$2" + "-" + "$1");
}

/**
 * returns a Date object by the current date field value
 *
 * @returns Date
 */
function getDateFieldsDateObject()
{
    var parts;

    if (browserSupportsDate())
    {
        return window.dateField.valueAsDate;
    }

    parts = window.dateField.value.split('.', 3);
    /** 12:00:00 o'clock for timezone offset */
    return new Date(parseInt(parts[2], 10), parseInt(parts[1] - 1, 10), parseInt(parts[0], 10), 12, 0, 0);
}

/**
 * sets the global variable datePattern out of the components specification.
 */
function setDatePattern()
{
    var pattern = text.dateFormat;

    pattern = pattern.replace(/d/, "\\d{2}");
    pattern = pattern.replace(/j/, "\\d{1,2}");
    pattern = pattern.replace(/m/, "\\d{2}");
    pattern = pattern.replace(/n/, "\\d{1,2}");
    pattern = pattern.replace(/y/, "\\d{2}");
    pattern = pattern.replace(/Y/, "\\d{4}");
    /** escape bindings like dots */
    pattern = pattern.replace(/\./g, "\\.");
    pattern = pattern.replace(/\\/g, "\\");

    window.datePattern = new RegExp(pattern);
}

/**
 * save lesson in users personal schedule
 * choose between lessons of whole semester (1), just this daytime (2) or only the selected instance of a lesson (3).
 *
 * @param taskNumber number
 * @param lessonData object
 */
function saveLesson(taskNumber, lessonData)
{
    var config = (typeof taskNumber == 'undefined') ? '1' : taskNumber,
        task = '&task=saveLesson&config=' + config;

    if (typeof lessonData != 'object')
    {
        return;
    }

    task += "&lessonID=" + lessonData.id;
    task += "&date=" + lessonData.schedule_date;
    task += "&time=" + lessonData.startTime;

    ajaxSave = new XMLHttpRequest();
    ajaxSave.open('GET', url + task, true);
    ajaxSave.onreadystatechange = lessonSaved;
    ajaxSave.send(null);
}

/**
 * replaces the save button of a saved lesson with a delete button
 */
function lessonSaved()
{
    if (ajaxSave.readyState == 4 && ajaxSave.status == 200)
    {
        var id = ajaxSave.responseText, lesson, addButton, deleteButton;

        /** change save button of saved lesson to delete button */
        lesson = document.getElementById(id);
        addButton = lesson.getElementsByClassName('add-lesson')[0];
        addButton.style.display = 'none';
        deleteButton = lesson.getElementsByClassName('delete-lesson')[0];
        deleteButton.style.display = 'block';
    }
}

/**
 * send an Ajax request to get users personal schedule
 */
function getUsersSchedule()
{
    var task = '&task=getUsersSchedule';

    ajaxUser = new XMLHttpRequest();
    ajaxUser.open('GET', url + task, true);
    ajaxUser.onreadystatechange = updateUsersSchedule;
    ajaxUser.send(null);
}

/**
 * handles Ajax request for the users schedule and display it in its table
 */
function updateUsersSchedule()
{
    var lessons, schedule;

    if (ajaxUser.readyState == 4 && ajaxUser.status == 200)
    {
        lessons = JSON.parse(ajaxUser.responseText);
        schedule = scheduleObjects.getScheduleById('user');

        if (!schedule)
        {
            return;
        }

        schedule.lessons = lessons;
        resetSchedule(schedule.table);
        setGridDays(schedule.table);
        insertLessons(schedule.table, lessons);
    }
}