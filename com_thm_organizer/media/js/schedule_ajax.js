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
 * Ajax properties
 */
var ajaxGrid = null, ajaxProgram = null, ajaxPool = null, ajaxRooms = null, ajaxTeachers = null, ajaxLessons = null,
    url = 'index.php?option=com_thm_organizer&view=schedule_ajax&format=raw', weekdays = [], startTimes = [],
    headTitles = ['Zeit', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'], selection = 'pools',
    times = ['08:00 - 09:30', '09:50 - 11:20', '11:30 - 13:00', '14:00 - 15:30', '15:45 - 17:15', '17:30 - 19:00'];

/**
 * instantiates the scheduler element and the weekdays
 */
jQuery(document).ready(function ()
{
    var firstScheduler = document.getElementsByClassName('scheduler')[0],
        headDays = firstScheduler.getElementsByTagName('thead')[0].getElementsByTagName('th');

    for (var index = 1; index < headDays.length; ++index)
    {
        weekdays.push(headDays[index].innerHTML);
    }

    /** to fill several form fields with 'all' departments as default selection */
    getPrograms();
    getRooms();
    getTeachers();

    /**
     * Event listener for filling data into form fields by Ajax
     */
    document.getElementById('time-menu-item').addEventListener('click', function ()
    {
        getGrids();
    });

    jQuery('#department').chosen().change(function ()
    {
        getPrograms();
        resetPools();
        getRooms();
        getTeachers();
    });

    jQuery('#program').chosen().change(function ()
    {
        getPools();
        getTeachers();
    });

    jQuery('#pool').chosen().change(function ()
    {
        /** global variable for updateLesson-functions to find the right schedule title */
        window.selection = 'pool';
        getLessons();
    });

    jQuery('#teacher').chosen().change(function ()
    {
        window.selection = 'teacher';
        getLessonsByTeacher();
    });

    jQuery('#room').chosen().change(function ()
    {
        window.selection = 'room';
        getLessonsByRoom();
    });

    document.getElementById('date').addEventListener('change', function ()
    {
        console.log('hallu?');
    });
});

/**
 * create an XMLHTTPRequest object, get the saved grids
 */
function getGrids()
{
    /** global variable 'ajaxGrid' for access from other functions */
    ajaxGrid = new XMLHttpRequest();
    ajaxGrid.open('GET', url + '&task=getGrids', true);
    ajaxGrid.onreadystatechange = updateGridList;
    ajaxGrid.send(null);
}

/**
 * get the grids through Ajax and add them as buttons with event handlers to the time-selection menu
 */
function updateGridList()
{
    var grids, gridList, button, grid, listItem;

    if (ajaxGrid.readyState == 4 && ajaxGrid.status == 200)
    {
        grids = JSON.parse(ajaxGrid.responseText);
        gridList = document.getElementById('time-selection').getElementsByTagName('ul')[0];

        removeChildren(gridList);

        for (var index = 0; index < grids.length; ++index)
        {
            button = document.createElement('button');
            grid = JSON.parse(grids[index].grid);
            listItem = document.createElement("li");

            button.setAttribute('type', 'button');
            button.setAttribute('id', grids[index].name_de);
            button.setAttribute('name', grids[index].name_de);
            button.innerHTML = grids[index].name_de;

            /**
             * anonymous functions necessary to bind a variable in the EventListener function while for-loops
             * @see http://stackoverflow.com/a/28633276/6355472
             */
            button.addEventListener('click', (function (grid)
                {
                    return function ()
                    {
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
    var schedules = document.getElementsByClassName('scheduler'), head, rows;
    for (var scheduleIndex = 0; scheduleIndex < schedules.length; ++scheduleIndex)
    {
        head = schedules[scheduleIndex].getElementsByTagName('thead')[0];
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
    var headItems = head.getElementsByTagName('th'), currentDay = grid.startDay, endDay = grid.endDay;

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
 * @param rows   tr-DOM-Elements of table
 * @param grid   grid with start- and end times
 */
function setGridTime(rows, grid)
{
    var hasPeriods = grid.hasOwnProperty('periods'), ownElements = document.getElementsByClassName('own-time'),
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

/**
 * Ajax request for getting programs
 */
function getPrograms()
{
    var chosenDepartment = document.getElementById('department').value,
        task = '&task=getPrograms&departmentID=' + chosenDepartment;

    /** global variable for catching responds in other functions */
    ajaxProgram = new XMLHttpRequest();
    ajaxProgram.open('GET', url + task, true);
    ajaxProgram.onreadystatechange = updateProgram;
    ajaxProgram.send(null);
}

/**
 * function to update the programs form field by Ajax data
 */
function updateProgram()
{
    var programs, programSelection = document.getElementById('program'), nextName = '', optionWithVersion = false,
        option;

    if (ajaxProgram.readyState == 4 && ajaxProgram.status == 200)
    {
        programs = JSON.parse(ajaxProgram.responseText);
        removeChildren(programSelection);

        for (var index = 0; index < programs.length; ++index)
        {
            option = document.createElement('option');
            option.setAttribute('value', programs[index].id);
            option.innerHTML = programs[index].name;

            /** check for the need of a version number in case of two equally program names (name ordering required) */
            nextName = (programs.length > index + 1) ? programs[index + 1].name : '';
            if (optionWithVersion)
            {
                option.innerHTML += ' ' + programs[index].version;
                optionWithVersion = false;
            }
            else if (nextName == programs[index].name)
            {
                option.innerHTML += ' ' + programs[index].version;
                optionWithVersion = true;
            }
            else
            {
                optionWithVersion = false;
            }

            programSelection.appendChild(option);
        }

        programSelection.disabled = false;
        jQuery('#program').chosen('destroy').chosen();
    }
}

/**
 * removes all children elements of the pool form field
 */
function resetPools()
{
    var poolField = document.getElementById('pool');
    removeChildren(poolField);
    poolField.setAttribute('disabled', 'disabled');
    jQuery('#pool').chosen('destroy').chosen();
}

/**
 * Ajax request for getting pools from selected programs
 */
function getPools()
{
    var programs = '', chosenDepartment = document.getElementById('department').value, task,
        chosenPrograms = document.getElementById('program').selectedOptions;

    for (var selectIndex = 0; selectIndex < chosenPrograms.length; ++selectIndex)
    {
        programs += chosenPrograms[selectIndex].value;

        if (chosenPrograms[selectIndex + 1] !== undefined)
        {
            programs += ",";
        }
    }

    task = '&task=getPools&departmentID=' + chosenDepartment + '&programIDs=' + programs;

    /** global variable for catching responds in other functions */
    ajaxPool = new XMLHttpRequest();
    ajaxPool.open('GET', url + task, true);
    ajaxPool.onreadystatechange = updatePool;
    ajaxPool.send(null);
}

/**
 * function to update the pools form field by Ajax data
 */
function updatePool()
{
    var pools, option, poolSelection = document.getElementById('pool');

    if (ajaxPool.readyState == 4 && ajaxPool.status == 200)
    {
        pools = JSON.parse(ajaxPool.responseText);

        removeChildren(poolSelection);

        for (var index = 0; index < pools.length; ++index)
        {
            option = document.createElement('option');
            option.setAttribute('value', pools[index].id);
            option.innerHTML = pools[index].name;
            poolSelection.appendChild(option);
        }

        poolSelection.disabled = false;
        jQuery('#pool').chosen('destroy').chosen();
    }
}

/**
 * Ajax request for getting rooms. optional from selected departments
 */
function getRooms()
{
    var programs = '', chosenDepartment = document.getElementById('department').value, task,
        chosenPrograms = document.getElementById('program').selectedOptions;

    for (var selectIndex = 0; selectIndex < chosenPrograms.length; ++selectIndex)
    {
        programs += chosenPrograms[selectIndex].value;

        if (chosenPrograms[selectIndex + 1] !== undefined)
        {
            programs += ",";
        }
    }

    task = '&task=getRooms&departmentID=' + chosenDepartment + '&programIDs=' + programs;

    /** global variable for catching responds in other functions */
    ajaxRooms = new XMLHttpRequest();
    ajaxRooms.open('GET', url + task, true);
    ajaxRooms.onreadystatechange = updateRooms;
    ajaxRooms.send(null);
}

/**
 * function to update the rooms form field by Ajax data
 */
function updateRooms()
{
    var rooms, option, roomSelection = document.getElementById('room');

    if (ajaxRooms.readyState == 4 && ajaxRooms.status == 200)
    {
        rooms = JSON.parse(ajaxRooms.responseText);

        removeChildren(roomSelection);

        // add empty option to show the placeholder
        roomSelection.appendChild(document.createElement('option'));

        for (var index = 0; index < rooms.length; ++index)
        {
            option = document.createElement('option');
            option.setAttribute('value', rooms[index].id);
            option.innerHTML = rooms[index].name;
            roomSelection.appendChild(option);
        }

        roomSelection.disabled = false;
        jQuery('#room').chosen('destroy').chosen();
    }
}

/**
 * Gets lessons by Ajax with the chosen room, department and date
 */
function getLessonsByRoom()
{
    var chosenDate = document.getElementById('date').valueAsDate,
        chosenRoom = document.getElementById('room').value,
        task = '&task=getLessonsByRoom' +
            '&roomID=' + chosenRoom +
            '&date=' + new Date(chosenDate + " UTC").toISOString().slice(0, 10);

    /** global variable for catching responds in other functions */
    ajaxLessons = new XMLHttpRequest();
    ajaxLessons.open('GET', url + task, true);
    ajaxLessons.onreadystatechange = updateLessons;
    ajaxLessons.send(null);
}

/**
 * Ajax request for getting teachers. optional from selected departments
 */
function getTeachers()
{
    var programs = '', task, chosenDepartment = document.getElementById('department').value,
        chosenPrograms = document.getElementById('program').selectedOptions;

    for (var selectIndex = 0; selectIndex < chosenPrograms.length; ++selectIndex)
    {
        programs += chosenPrograms[selectIndex].value;

        if (chosenPrograms[selectIndex + 1] !== undefined)
        {
            programs += ",";
        }
    }

    task = '&task=getTeachers&departmentID=' + chosenDepartment + '&programIDs=' + programs;

    /** global variable for catching responds in other functions */
    ajaxTeachers = new XMLHttpRequest();
    ajaxTeachers.open('GET', url + task, true);
    ajaxTeachers.onreadystatechange = updateTeachers;
    ajaxTeachers.send(null);
}

/**
 * function to update the teachers form field by Ajax data
 */
function updateTeachers()
{
    var teachers, option, teacherSelection = document.getElementById('teacher');

    if (ajaxTeachers.readyState == 4 && ajaxTeachers.status == 200)
    {
        teachers = JSON.parse(ajaxTeachers.responseText);

        removeChildren(teacherSelection);

        // add empty option to show the placeholder
        teacherSelection.appendChild(document.createElement('option'));

        for (var index = 0; index < teachers.length; ++index)
        {
            option = document.createElement('option');
            option.setAttribute('value', teachers[index].id);
            option.innerHTML = teachers[index].name;
            teacherSelection.appendChild(option);
        }

        teacherSelection.disabled = false;
        jQuery('#teacher').chosen('destroy').chosen();
    }
}

/**
 * Gets lessons by Ajax with the chosen teacher, department and date
 */
function getLessonsByTeacher()
{
    var chosenDepartment = document.getElementById('department').value,
        chosenDate = document.getElementById('date').valueAsDate,
        chosenTeacher = document.getElementById('teacher').value,
        task = '&task=getLessonsByTeacher' +
            '&departmentID=' + chosenDepartment +
            '&teacherID=' + chosenTeacher +
            '&date=' + new Date(chosenDate + " UTC").toISOString().slice(0, 10);

    /** global variable for catching responds in other functions */
    ajaxLessons = new XMLHttpRequest();
    ajaxLessons.open('GET', url + task, true);
    ajaxLessons.onreadystatechange = updateLessons;
    ajaxLessons.send(null);
}

/**
 * removes all children elements of one given parent element
 *
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

/**
 * Ajax request for getting lessons. Department- and poolID required.
 */
function getLessons()
{
    /** for the task: date formatted to yyyy-mm-dd inclusive TimeZoneOffset
     * @see http://stackoverflow.com/a/11172083/6355472
     */
    var chosenDepartment = document.getElementById('department').value,
        chosenPools = document.getElementById('pool').selectedOptions,
        chosenDate = document.getElementById('date').valueAsDate,
        task, poolIDs = '';

    for (var selectIndex = 0; selectIndex < chosenPools.length; ++selectIndex)
    {
        poolIDs += chosenPools[selectIndex].value;

        if (chosenPools[selectIndex + 1] !== undefined)
        {
            poolIDs += ",";
        }
    }

    if (poolIDs.search(/^\d+$/) != -1)
    {
        task = '&task=getLessonsByPools' +
            '&departmentID=' + chosenDepartment +
            '&poolIDs=' + poolIDs +
            '&date=' + new Date(chosenDate + " UTC").toISOString().slice(0, 10);

        /** global variable for catching responds in other functions */
        ajaxLessons = new XMLHttpRequest();
        ajaxLessons.open('GET', url + task, true);
        ajaxLessons.onreadystatechange = updateLessons;
        ajaxLessons.send(null);
    }
}

/**
 * Creates a new schedule with lessons out of the Ajax request by selecting pools
 */
function updateLessons()
{
    var scheduleTitle = '', lessons, schedule,
        selection = document.getElementById(window.selection).selectedOptions;

    if (ajaxLessons.readyState == 4 && ajaxLessons.status == 200)
    {
        console.log(ajaxLessons.responseText);
        lessons = JSON.parse(ajaxLessons.responseText);

        for (var option = 0; option < selection.length; ++option)
        {
            scheduleTitle += selection[option].innerHTML;
            if (option != selection.length - 1)
            {
                scheduleTitle += ' - ';
            }
        }

        schedule = createScheduleTable(scheduleTitle);
        fillTimes(schedule);
        insertLessons(schedule, lessons);
    }
}

/**
 * Creates a table DOM-element with an input and label for selecting it and a caption with the given title.
 * Also it gets appended to the scheduleWrapper already.
 *
 * @param title  string
 * @returns HTMLTableElement
 */
function createScheduleTable(title)
{
    var scheduleSelection = document.getElementById('schedule-selection'),
        list = scheduleSelection.getElementsByTagName('ul')[0],
        scheduleWrapper = document.getElementById('scheduleWrapper'),
        li, input, label, div, schedule, caption, tbody, row;

    /** create input field for selecting this schedule */
    input = document.createElement('input');
    input.className = 'scheduler-input';
    input.type = 'radio';
    input.setAttribute('id', title);
    input.setAttribute('name', 'schedules');
    input.setAttribute('checked', 'checked');
    scheduleWrapper.appendChild(input);

    label = document.createElement('label');
    label.setAttribute('for', title);
    label.setAttribute('tabindex', '0');
    label.innerHTML = title;
    li = document.createElement('li');
    li.appendChild(label);
    list.appendChild(li);

    /** making a new schedule table */
    div = document.createElement('div');
    div.setAttribute('id', title + '-schedule');
    div.setAttribute('class', 'scheduler');
    schedule = document.createElement('table');
    div.appendChild(schedule);
    scheduleWrapper.appendChild(div);

    /** schedule title */
    caption = schedule.createCaption();
    caption.innerHTML = title;
    schedule.appendChild(caption);

    tbody = document.createElement('tbody');
    schedule.appendChild(tbody);

    /** filled with rows and cells (by -1 for last position) */
    times.forEach(function ()
    {
        row = tbody.insertRow(-1);

        weekdays.forEach(function ()
        {
            row.insertCell(-1);
        });
    });

    return schedule;
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
 * @param schedule HTMLTableElement
 */
function fillTimes(schedule)
{
    var thead, tr, th;

    /** head with days of the week */
    thead = schedule.createTHead();
    tr = thead.insertRow(0);
    for (var h = 0; h < 6; ++h)
    {
        th = document.createElement('th');
        th.innerHTML = headTitles[h];
        tr.appendChild(th);
    }

    /** schedule body with times */
    times.forEach(function (element, index)
    {
        var trTimes = schedule.tBodies[0].rows[index], tdTimes = trTimes.insertCell(0), timeSpan;
        timeSpan = document.createElement('span');
        timeSpan.className = 'time-semester';

        tdTimes.innerHTML = element.substring(0, 5) + "<br> - <br>" + element.substring(7);

        /** for placing the lessons */
        startTimes.push(element.substring(0, 5));
    });
}

/**
 * inserts lessons into a schedule table
 *
 * @param schedule  HTMLTableElement
 * @param lessons   JSON data
 */
function insertLessons(schedule, lessons)
{
    var lessonElement, time, nextTime, lessonsTime, timeSplits, day;

    for (var l = 0; l < lessons.length; ++l)
    {
        lessonElement = createLesson(lessons[l]);
        for (var s = 0; s < startTimes.length; ++s)
        {
            /** @see http://stackoverflow.com/a/6212346/6355472 */
            time = Date.parse(lessons[l].schedule_date + ' ' + startTimes[s]);
            nextTime = Date.parse(lessons[l].schedule_date + ' ' + startTimes[s + 1]);
            lessonsTime = Date.parse(lessons[l].schedule_date + ' ' + lessons[l].startTime);

            if (lessonsTime >= time)
            {
                if (isNaN(nextTime) || lessonsTime < nextTime)
                {
                    timeSplits = lessons[l].schedule_date.split(/[-]/);
                    day = new Date(timeSplits[0], timeSplits[1] - 1, timeSplits[2]).getDay();

                    if (schedule.rows[s + 1] !== undefined)
                    {
                        schedule.rows[s + 1].cells[day].appendChild(lessonElement);
                    }
                    break;
                }
            }
        }
    }
    computeTableHeight();
}