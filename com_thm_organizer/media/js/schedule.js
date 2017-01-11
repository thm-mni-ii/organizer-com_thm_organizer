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
    ajaxSelection = null, ajaxLessons = null, ajaxSchedule = null, ajaxSave = null,
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
     * @return Schedule | boolean
     */
    this.getScheduleById = function (id)
    {
        for (var scheduleIndex = 0; scheduleIndex < this.schedules.length; ++scheduleIndex)
        {
            if (this.schedules[scheduleIndex].id == id)
            {
                return this.schedules[scheduleIndex];
            }
        }

        return false;
    };

    /**
     * gets the Schedule object which belongs to the given response url from an Ajax request
     * @param responseUrl  string
     * @return Schedule | boolean
     */
    this.getScheduleByRequest = function (responseUrl)
    {
        var id = responseUrl.match(/&(\w+)IDs=(\d+)/), scheduleID = (id == null) ? 'user' : id[1] + id[2];

        for (var scheduleIndex = 0; scheduleIndex < this.schedules.length; ++scheduleIndex)
        {
            if (this.schedules[scheduleIndex].id == scheduleID)
            {
                return this.schedules[scheduleIndex];
            }
        }

        return false;
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
    var date = variables.dateFormat,
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

/**
 * adds event listeners and initialise (user) schedule and date input form field
 */
jQuery(document).ready(function ()
{
    var startX, startY;

    initSchedule();
    computeTableHeight();
    setDatePattern();
    changePositionOfDateInput();

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

        getFormRequest(chosenCategory);
    });

    jQuery('#program').chosen().change(function ()
    {
        onlyShowFormInput(['program-input', 'pool-input']);
        getFormRequest('pool');
    });

    jQuery('#roomtype').chosen().change(function ()
    {
        onlyShowFormInput(['room-type-input', 'room-input']);
        getFormRequest('room');
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
     * Change Tab-Behaviour of menu-bar, so all tabs can be closed
     */
    jQuery(".tabs-toggle").on('click', function ()
    {
        changeTabBehaviour(jQuery(this));
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
    window.url += '&departmentID=' + variables.departmentID;
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

    if (variables.userID !== '0')
    {
        createUsersSchedule();
        //change the active tab
        jQuery('#tab-selected-schedules').parent('li').addClass("active");
        jQuery('#selected-schedules').addClass("active");
        jQuery('#tab-schedule-form').parent('li').removeClass("active");
        jQuery('#schedule-form').removeClass("active")
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

    schedule.title = text.MY_SCHEDULE;
    schedule.task = '&task=getUserSchedule';
    schedule.task += '&date=' + getDateFieldString();

    schedule.table = createScheduleTable(schedule);
    insertTableHead(schedule.table);
    setGridTime(schedule.table);
    addScheduleToSelection(schedule);
    window.scheduleObjects.addSchedule(schedule);
    schedule.lessons = updateSchedule('user');
    computeTableHeight();
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
    var vDay = (typeof visibleDay === 'undefined') ? window.dateField.valueAsDate.getDay() : visibleDay,
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
                    jQuery(heads[head]).addClass("activeColumn");
                }
                else
                {
                    jQuery(heads[head]).removeClass("activeColumn");
                }
            }
            cells = rows[row].getElementsByTagName('td');
            for (var cell = 1; cell < cells.length; ++cell)
            {
                if (cell == vDay)
                {
                    jQuery(cells[cell]).addClass("activeColumn");
                }
                else
                {
                    jQuery(cells[cell]).removeClass("activeColumn");
                }
            }
        }
    }
}

/**
 * TODO: prüfen ob break vorhanden sein soll (anhand json data?)
 * onclick of a grid selection sets the selected grid on every schedule
 */
function setGridByClick()
{
    scheduleObjects.schedules.forEach(
        function (schedule)
        {
            setGridDays(schedule.table);
            setGridTime(schedule.table);
            resetSchedule(schedule.table);
            insertLessons(schedule);
        }
    );

    computeTableHeight();
}

/**
 * here the table head changes to the grids specified weekdays with start day and end day
 *
 * @param table DOM element table
 */
function setGridDays(table)
{
    var grid = JSON.parse(getSelectedValues('grid')), currentDay = grid.startDay, endDay = grid.endDay,
        head = table.getElementsByTagName('thead')[0], headItems = head.getElementsByTagName('th'),
        headerDate = window.dateField.valueAsDate, day = headerDate.getDay();

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
function getFormRequest(resource)
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
    var values, category, fieldID, formField, option;

    if (ajaxSelection.readyState == 4 && ajaxSelection.status == 200)
    {
        values = JSON.parse(ajaxSelection.responseText);
        category = ajaxSelection.responseURL.match(/&task=get(\w+)s/);
        fieldID = category[1].toLowerCase();
        formField = document.getElementById(fieldID);
        removeChildren(formField);

        for (var value in values)
        {
            /** prevent using prototype variables */
            if (values.hasOwnProperty(value))
            {
                if (value.name !== undefined)
                {
                    option = document.createElement('option');
                    option.setAttribute('value', values[value].id);
                    option.innerHTML = values[value].name;
                    formField.appendChild(option);
                }
                /** needed - otherwise select field is empty */
                else
                {
                    option = document.createElement('option');
                    option.setAttribute('value', values[value]);
                    option.innerHTML = value;
                    formField.appendChild(option);
                }
            }
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
    var task, IDs = getSelectedValues(resource), schedule, field;

    if (IDs.match(/^(\d+[,]?)+$/) == null)
    {
        return;
    }

    task = "&task=getLessons";
    task += "&" + resource + "IDs=" + IDs;
    task += '&date=' + getDateFieldString();
    task += window.isMobile ? '&oneDay=true' : '';

    schedule = scheduleObjects.getScheduleById(resource + IDs);
    if (schedule)
    {
        updateSchedule(resource + IDs);
        return;
    }

    schedule = new Schedule(resource + IDs);
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
        schedule = scheduleObjects.getScheduleByRequest(ajaxLessons.responseURL);

        if (!schedule)
        {
            return;
        }

        schedule.lessons = JSON.parse(ajaxLessons.responseText);
        schedule.table = createScheduleTable(schedule);

        insertTableHead(schedule.table);
        setGridTime(schedule.table);
        insertLessons(schedule);
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

    option.innerHTML = "<span class='title'>" + schedule.title + "</span>";
    if (schedule.id != 'user')
    {
        option.innerHTML += "<button onclick='removeScheduleFromSelection()' class='removeOption'>" +
            "<span class='icon-remove'></span></button>";
    }
    option.value = schedule.id;
    option.selected = 'selected';
    document.getElementById('schedules').appendChild(option);

    /** updating chosen.js */
    jQuery('#schedules').chosen('destroy').chosen();
}

/**
 * remove an entry from the dropdown field for selecting a schedule
 * works just with chosen, don't work in mobile, because chosen isn't be used there
 */
function removeScheduleFromSelection()
{
    var x = document.getElementById("schedules"), scheduleInput = document.getElementById(jQuery('#schedules').val());

    x.remove(x.selectedIndex);

    /** updating chosen.js */
    jQuery('#schedules').chosen('destroy').chosen();

    if (scheduleInput)
    {
        scheduleInput.checked = 'checked';
    }

    //TODO: else{} if no schedule can be select (list is empty) , the guest-schedule-table has to be shown
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
        input, div, table, tbody, row, weekEnd = 7, grid;

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
    if (variables.defaultTimes.hasOwnProperty('periods'))
    {
        for (var periods in variables.defaultTimes.periods)
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
 * Creates a lesson which means a div element filled by data
 *
 * @param data          Object      lesson data
 * @param ownTime       boolean     show own time
 * @param usersSchedule boolean     defines if the lessons are for the users schedule (no save menu needed)
 * 
 * @returns Array|boolean           HTMLDivElements in an array or false in case of wrong input
 */
function createLesson(data, ownTime, usersSchedule)
{
    var lessons, subjectData, lessonElement, ownTimeSpan, nameSpan, moduleSpan, teacherSpan, roomSpan;

    if (typeof data == 'undefined' || !data.hasOwnProperty("subjects"))
    {
        return false;
    }

    ownTime = typeof ownTime == 'undefined' ? false : ownTime;
    usersSchedule = typeof usersSchedule == 'undefined' ? false : usersSchedule;
    lessons = [];

    for (var subject in data.subjects)
    {
        if (!data.subjects.hasOwnProperty(subject))
        {
            return false;
        }
        subjectData = data.subjects[subject];

        lessonElement = document.createElement('div');
        lessonElement.className = 'lesson';

        /** data attributes instead of classes for finding the lesson later */
        lessonElement.dataset.ccmID = data.ccmID;

        /** delta = 'removed' or 'new' or 'changed' ? add class like 'lesson-new' */
        if (data.lessonDelta !== '')
        {
            lessonElement.className += ' lesson-' + data.lessonDelta;
        }
        if (data.subjectDelta !== '')
        {
            lessonElement.className += ' subject-' + data.subjectDelta;
        }
        if (data.calendarDelta !== '')
        {
            lessonElement.className += ' calendar-' + data.calendarDelta;
        }
        if (data.poolDelta !== '')
        {
            lessonElement.className += ' pool-' + data.poolDelta;
        }

        if (ownTime && data.startTime && data.endTime)
        {
            ownTimeSpan = document.createElement('span');
            ownTimeSpan.className = 'own-time';
            ownTimeSpan.innerHTML =
                data.startTime.match(/^(\d{2}:\d{2})/)[1] + ' - ' + data.endTime.match(/^(\d{2}:\d{2})/)[1];
            lessonElement.appendChild(ownTimeSpan);
        }

        if (subjectData.shortName)
        {
            nameSpan = document.createElement('span');
            nameSpan.className = 'name';
            nameSpan.innerHTML = subjectData.shortName;
            lessonElement.appendChild(nameSpan);
        }

        if (subjectData.subjectNo)
        {
            moduleSpan = document.createElement('span');
            moduleSpan.className = 'module';
            moduleSpan.innerHTML = subjectData.subjectNo;
            lessonElement.appendChild(moduleSpan);
        }

        if (subjectData.teachers)
        {
            for (var teacherID in subjectData.teachers)
            {
                if (subjectData.teachers.hasOwnProperty(teacherID))
                {
                    teacherSpan = document.createElement('span');
                    teacherSpan.className = 'teacher';
                    teacherSpan.innerHTML = subjectData.teachers[teacherID];
                    lessonElement.appendChild(teacherSpan);
                }
            }
        }

        if (subjectData.rooms)
        {
            for (var roomID in subjectData.rooms)
            {
                if (subjectData.rooms.hasOwnProperty(roomID))
                {
                    roomSpan = document.createElement('span');
                    roomSpan.className = 'room';
                    roomSpan.innerHTML = subjectData.rooms[roomID];
                    lessonElement.appendChild(roomSpan);
                }
            }
        }

        if (variables.userID == '0')
        {
            lessonElement.className += ' no-saving';
        }
        else
        {
            /** outsource creating of menu to prevent closures of eventListeners */
            addLessonMenu(lessonElement);
            addLessonMenu(lessonElement, false);

            /** makes delete button visible only */
            if (usersSchedule || isSavedByUser(lessonElement))
            {
                lessonElement.className += ' added';
            }

            /**
             * right click on lessons show save/delete menu
             */
            lessonElement.addEventListener('contextmenu', function (event)
            {
                if (this.className.match(/added/))
                {
                    this.getElementsByClassName('delete')[0].style.display = 'block';
                }
                else
                {
                    this.getElementsByClassName('save')[0].style.display = 'block';
                }

                event.preventDefault();
            });
        }

        lessons.push(lessonElement);
    }

    return lessons;
}

/**
 * creates html elements for saving/deleting a lesson to/from the users schedule
 * @param lessonElement String
 * @param saveMenu default = true. returns a menu to delete the lesson by false
 */
function addLessonMenu(lessonElement, saveMenu)
{
    var saving = (typeof saveMenu == 'undefined') ? true : saveMenu, ccmID = lessonElement.dataset.ccmID,
        menu, semesterMode, periodMode, instanceMode, singleActionButton, buttonIcon, closeMenuButton;

    menu = document.createElement('div');
    menu.className = 'lesson-menu';
    menu.className += saving ? ' save' : ' delete';

    closeMenuButton = document.createElement('button');
    closeMenuButton.className = 'icon-cancel';
    closeMenuButton.addEventListener('click', function ()
    {
        menu.style.display = 'none';
    });

    semesterMode = document.createElement('button');
    semesterMode.innerHTML = saving ? text.SAVE_SEMESTER : text.DELETE_SEMESTER;
    semesterMode.addEventListener('click', function ()
    {
        handleLesson(variables.SAVE_MODE_SEMESTER, ccmID, saving);
        menu.style.display = 'none';
    });

    periodMode = document.createElement('button');
    periodMode.innerHTML = saving ? text.SAVE_PERIOD : text.DELETE_PERIOD;
    periodMode.addEventListener('click', function ()
    {
        handleLesson(variables.SAVE_MODE_PERIOD, ccmID, saving);
        menu.style.display = 'none';
    });

    instanceMode = document.createElement('button');
    instanceMode.innerHTML = saving ? text.SAVE_INSTANCE : text.DELETE_INSTANCE;
    instanceMode.addEventListener('click', function ()
    {
        handleLesson(variables.SAVE_MODE_INSTANCE, ccmID, saving);
        menu.style.display = 'none';
    });

    menu.appendChild(closeMenuButton);
    menu.appendChild(semesterMode);
    menu.appendChild(periodMode);
    menu.appendChild(instanceMode);

    singleActionButton = document.createElement('button');
    singleActionButton.className = saving ? 'add-lesson' : 'delete-lesson';
    buttonIcon = document.createElement('span');
    buttonIcon.className = saving ? 'icon-plus' : 'icon-delete';
    singleActionButton.appendChild(buttonIcon);
    singleActionButton.addEventListener('click', function ()
    {
        handleLesson(variables.SAVE_MODE_SEMESTER, ccmID, saving);
    });

    lessonElement.appendChild(singleActionButton);
    lessonElement.appendChild(menu);
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
 * inserts lessons into a schedule
 *
 * @param schedule Schedule object with table, task...
 */
function insertLessons(schedule)
{
    var colNumber = 1, rows = schedule.table.getElementsByTagName('tbody')[0].getElementsByTagName('tr'), block, lesson,
        times = JSON.parse(getSelectedValues('grid')), tableStartTime, tableEndTime, blockTimes, lessonElements,
        blockIndex, blockStart, blockEnd, cell, nextCellExist, nextCell, lessonsNextBlock,
        isUsersSchedule = (schedule.id == 'user'), lessons = schedule.lessons;

    for (var date in lessons)
    {
        if (!lessons.hasOwnProperty(date))
        {
            continue;
        }

        /** no times on the left side - every lesson appears in the first row */
        if (!times.periods)
        {
            for (block in lessons[date])
            {
                if (!lessons[date].hasOwnProperty(block))
                {
                    continue;
                }
                for (lesson in lessons[date][block])
                {
                    if (!lessons[date][block].hasOwnProperty(lesson))
                    {
                        continue;
                    }

                    lessonElements = createLesson(lessons[date][block][lesson], true);
                    lessonElements.forEach(function (element)
                    {
                        rows[0].getElementsByTagName('td')[colNumber].appendChild(element);
                    });
                }
            }
        }
        /** insert lessons in cells */
        else
        {
            blockIndex = 0;
            for (block in lessons[date])
            {
                if (!lessons[date].hasOwnProperty(block))
                {
                    continue;
                }

                /** periods start at 1, html td-elements at 0 */
                tableStartTime = times.periods[blockIndex + 1].startTime;
                tableEndTime = times.periods[blockIndex + 1].endTime;
                blockTimes = block.match(/^(\d{4})-(\d{4})$/);
                blockStart = blockTimes[1];
                blockEnd = blockTimes[2];

                /** block does not fit? go to next block */
                while (tableStartTime < blockStart && tableEndTime < blockStart)
                {
                    ++blockIndex;
                    tableStartTime = times.periods[blockIndex + 1].startTime;
                    tableEndTime = times.periods[blockIndex + 1].endTime;
                }

                for (lesson in lessons[date][block])
                {
                    if (!lessons[date][block].hasOwnProperty(lesson))
                    {
                        continue;
                    }
                    cell = rows[blockIndex].getElementsByTagName('td')[colNumber];
                    nextCellExist = rows[blockIndex + 1] !== undefined;

                    /** time matches */
                    if (tableStartTime == blockStart && tableEndTime == blockEnd)
                    {
                        lessonElements = createLesson(lessons[date][block][lesson], false, isUsersSchedule);
                        lessonElements.forEach(function (element)
                        {
                            cell.appendChild(element);
                        });
                    }
                    /** lesson fits inside block but has slightly other times */
                    if (tableStartTime <= blockStart && tableEndTime != blockEnd)
                    {
                        lessonElements = createLesson(lessons[date][block][lesson], true, isUsersSchedule);
                        lessonElements.forEach(function (element)
                        {
                            cell.appendChild(element);
                        });
                    }
                    /** lesson fits into next block too, so add a copy to this */
                    if (nextCellExist && blockEnd >= times.periods[blockIndex + 2].startTime)
                    {
                        nextCell = rows[blockIndex + 1].getElementsByTagName('td')[colNumber];
                        lessonsNextBlock = createLesson(lessons[date][block][lesson], true, isUsersSchedule);
                        lessonsNextBlock.forEach(function (element)
                        {
                            nextCell.appendChild(element);
                        });
                    }
                }
                ++blockIndex;
            }
        }
        ++colNumber;
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
    var schedule;

    if (ajaxSchedule.readyState == 4 && ajaxSchedule.status == 200)
    {
        schedule = scheduleObjects.getScheduleByRequest(ajaxSchedule.responseURL);

        if (!schedule)
        {
            return;
        }

        schedule.lessons = JSON.parse(ajaxSchedule.responseText);
        resetSchedule(schedule.table);
        setGridDays(schedule.table);
        insertLessons(schedule);
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
    var pattern = variables.dateFormat;

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
 * @param ccmID String calendar_configuration_map ID
 * @param save boolean indicate to save or to delete the lesson
 */
function handleLesson(taskNumber, ccmID, save)
{
    var mode = (typeof taskNumber == 'undefined') ? '1' : taskNumber,
        saving = (typeof save == 'undefined') ? true : save,
        task = '&task= ' + (saving ? '&task=saveLesson' : '&task=deleteLesson');

    if (typeof ccmID == 'undefined')
    {
        return false;
    }

    task += "&saveMode=" + mode + "&ccmID=" + ccmID;
    ajaxSave = new XMLHttpRequest();
    ajaxSave.open('GET', url + task, true);
    ajaxSave.onreadystatechange = lessonHandled;
    ajaxSave.send(null);
}

/**
 * replaces the save button of a saved lesson with a delete button and reverse
 */
function lessonHandled()
{
    var savedLessons, lessons;

    if (ajaxSave.readyState == 4 && ajaxSave.status == 200)
    {
        savedLessons = JSON.parse(ajaxSave.responseText);

        window.scheduleObjects.schedules.forEach(function (schedule)
        {
            lessons = schedule.table.getElementsByClassName('lesson');
            for (var lessonIndex = 0; lessonIndex < lessons.length; ++lessonIndex)
            {
                if (savedLessons.includes(lessons[lessonIndex].dataset.ccmID))
                {
                    if (lessons[lessonIndex].className === 'lesson')
                    {
                        lessons[lessonIndex].className += ' added';
                    }
                    else
                    {
                        lessons[lessonIndex].className = 'lesson';
                    }
                }
            }
        });

        updateSchedule('user');
    }
}

/**
 * checks for a lesson if it is already saved in the users schedule
 * @param lesson HTMLDivElement
 * @return boolean
 */
function isSavedByUser(lesson)
{
    var usersSchedule = window.scheduleObjects.getScheduleById('user'), lessons;

    if (typeof lesson == 'undefined' || !usersSchedule)
    {
        return false;
    }

    lessons = usersSchedule.table.getElementsByClassName('lesson');
    for (var lessonIndex = 0; lessonIndex < lessons.length; ++lessonIndex)
    {
        if (lessons[lessonIndex].dataset.ccmID == lesson.dataset.ccmID)
        {
            return true;
        }
    }

    return false;
}

/**
 * Change tab-behaviour of tabs in menu-bar, so all tabs can be closed
 *
 * @param clickedTab Object
 */
function changeTabBehaviour(clickedTab)
{
    var tabId = clickedTab.attr("data-id");

    if (clickedTab.parent('li').hasClass("active"))
    {
        clickedTab.parent('li').toggleClass("inactive", "");
        jQuery('#' + tabId).toggleClass("inactive", "");
    }
    else
    {
        jQuery(".tabs-tab").removeClass("inactive");
        jQuery(".tab-panel").removeClass("inactive");
    }
}

/**
 * change position of the date-input, depending of screen-width
 */
function changePositionOfDateInput()
{
    var mq = window.matchMedia("(max-width: 677px)");
    if (window.isMobile)
    {
        jQuery(".date-input").insertAfter(".menu-bar");
    }

    mq.addListener(function ()
    {
        if (mq.matches)
        {
            jQuery(".date-input").insertAfter(".menu-bar");
        }

        else
        {
            jQuery(".date-input").appendTo(".date-input-list-item");
        }
    });
}