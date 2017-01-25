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

var scheduleWrapper, isMobile, dateField, weekdays, Schedule, ScheduleTable, LessonMenu, Schedules, scheduleObjects,
    datePattern, ajaxSelection = null, scheduleRequests = [], ajaxSave = null,
    ajaxUrl = "index.php?option=com_thm_organizer&view=schedule_ajax&format=raw";

/**
 * Schedule 'class' for saving params and update the scheduleTable
 *
 * @param resource string name of (e.g. form) resource
 * @param IDs string makes together with resource the schedule ID
 * @param optionalTitle string optional title for directly linked schedules (e.g. teacher or room)
 */
Schedule = function (resource, IDs, optionalTitle)
{
    this.resource = resource; // For ScheduleTable usage
    this.id = resource === "user" ? resource
        : IDs ? resource + IDs
        : resource + getSelectedValues(resource, "-");
    this.scheduleTable = new ScheduleTable(this);
    this.title = "";
    this.task = "";
    this.ajaxRequest = new XMLHttpRequest();
    this.lessons = [];

    /**
     * constructor-like function to init table, task and title
     */
    this.create = function ()
    {
        this.scheduleTable.create();
        this.setTitle();
        this.setTask();
    };

    /**
     * Sets Ajax url for updating lessons
     */
    this.setTask = function ()
    {
        this.task = "&departmentIDs=" + variables.departmentID;

        if (resource === "user")
        {
            this.task += "&task=getUserSchedule";
        }
        else
        {
            this.task += "&task=getLessons";
            this.task += "&" + resource + "IDs=" + (IDs ? IDs.replace(/-/g, ",") : getSelectedValues(resource));
        }

        this.task += "&date=" + getDateFieldString() + (window.isMobile ? "&oneDay=true" : "");
    };

    /**
     * Sets title that depends on the selected schedule
     */
    this.setTitle = function ()
    {
        var resourceField = document.getElementById(resource), categoryField = document.getElementById("program");

        if (optionalTitle)
        {
            this.title = optionalTitle;
            return;
        }

        if (resource === "user")
        {
            this.title = text.MY_SCHEDULE;
            return;
        }

        // Get pre-selected value like "Informatik Master"
        if (resource === "pool")
        {
            for (var catIndex = 0; catIndex < categoryField.selectedOptions.length; ++catIndex)
            {
                this.title += categoryField.selectedOptions[catIndex].text;
                if (categoryField.selectedOptions.length > catIndex + 1)
                {
                    this.title += " - ";
                }
            }

            this.title += " - ";
        }

        // Get resource selection like "1. Semester" or "A20.1.1"
        for (var resIndex = 0; resIndex < resourceField.selectedOptions.length; ++resIndex)
        {
            this.title += resourceField.selectedOptions[resIndex].text;
            if (resourceField.selectedOptions.length > resIndex + 1)
            {
                this.title += " - ";
            }
        }
    };

    /**
     * Sends an Ajax request with the actual date to update the schedule
     */
    this.requestUpdate = function ()
    {
        this.task = this.task.replace(/(date=)\d{4}\-\d{2}\-\d{2}/, "$1" + getDateFieldString());
        this.ajaxRequest.open("GET", ajaxUrl + this.task, true);
        this.ajaxRequest.onreadystatechange = insertLessonResponse;
        this.ajaxRequest.send(null);
        window.scheduleRequests.push(this.ajaxRequest);
    };

    /**
     * Sets new lessons and updates table with them
     *
     * @param newLessons array
     */
    this.setLessons = function (newLessons)
    {
        this.lessons = newLessons;
        this.scheduleTable.update(newLessons);
    };

    /**
     * updates table with already given lessons, e.g. for changing time grids
     */
    this.updateTable = function ()
    {
        this.scheduleTable.update(this.lessons, true);
    };
};

/**
 * Class for the HTMLTableElement of a schedule
 *
 * @param schedule Schedule object
 */
ScheduleTable = function (schedule)
{
    this.timeGrid = JSON.parse(getSelectedValues("grid"));
    this.visibleDay = getDateFieldsDateObject().getDay();
    this.userSchedule = schedule.id === "user";
    this.lessonElements = []; // HTMLDivElements
    this.table = document.createElement("table"); // HTMLTableElement

    /**
     * constructor-like function to build the HTMLTableElement
     */
    this.create = function ()
    {
        this.createScheduleElement();
        this.insertTableHead();
        this.setGridTime();
    };

    /**
     * updates the table with the actual selected time grid and given lessons.
     *
     * @param lessons object
     * @param newTimeGrid boolean
     */
    this.update = function (lessons, newTimeGrid)
    {
        this.visibleDay = getDateFieldsDateObject().getDay();
        this.resetTable();

        if (window.isMobile)
        {
            this.setActiveColumn();
        }

        if (newTimeGrid)
        {
            this.timeGrid = JSON.parse(getSelectedValues("grid"));
            this.setGridDays();
            this.setGridTime();
        }

        this.insertLessons(lessons);
    };

    /**
     * Creates a table DOM-element with an input and label for selecting it and a caption with the given title.
     * It gets appended to the scheduleWrapper.
     */
    this.createScheduleElement = function ()
    {
        var input, div, tbody, row, weekEnd = 7;

        // Create input field for selecting this schedule
        input = document.createElement("input");
        input.className = "schedule-input";
        input.type = "radio";
        input.setAttribute("id", schedule.id);
        input.setAttribute("name", "schedules");
        input.setAttribute("checked", "checked");
        window.scheduleWrapper.appendChild(input);

        // Create a new schedule table
        div = document.createElement("div");
        div.setAttribute("id", schedule.id + "-schedule");
        div.setAttribute("class", "schedule-table");
        div.appendChild(this.table);
        window.scheduleWrapper.appendChild(div);

        tbody = document.createElement("tbody");
        this.table.appendChild(tbody);

        // Filled with rows and cells (with -1 for last position)
        if (this.timeGrid.hasOwnProperty("periods"))
        {
            for (var periods in this.timeGrid.periods)
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
    };

    /**
     * Insert table head and side cells with time data
     */
    this.insertTableHead = function ()
    {
        var tHead = this.table.createTHead(), tr = tHead.insertRow(0), weekend = 7, th, thText,
            headerDate = getDateFieldsDateObject();

        // Set date to monday
        headerDate.setDate(headerDate.getDate() - headerDate.getDay());

        for (var headIndex = 0; headIndex < weekend; ++headIndex)
        {
            th = document.createElement("th");
            thText = weekdays[headIndex - 1] + " (" + headerDate.getPresentationFormat() + ")";
            th.innerHTML = (headIndex === 0) ? text.TIME : thText;
            if (headIndex === this.visibleDay)
            {
                jQuery(th).addClass("activeColumn");
            }
            tr.appendChild(th);
            headerDate.setDate(headerDate.getDate() + 1);
        }
    };

    /**
     * sets the chosen times of the grid in the schedules tables
     */
    this.setGridTime = function ()
    {
        var rows = this.table.getElementsByTagName("tbody")[0].getElementsByTagName("tr"),
            hasPeriods = this.timeGrid.hasOwnProperty("periods"), period = 1, timeCell, startTime, endTime;

        for (var row = 0; row < rows.length; ++row)
        {
            if (!rows[row].className.match(/break-row/))
            {
                timeCell = rows[row].getElementsByTagName("td")[0];
                if (hasPeriods)
                {
                    startTime = this.timeGrid.periods[period].startTime;
                    startTime = startTime.replace(/(\d{2})(\d{2})/, "$1:$2");
                    endTime = this.timeGrid.periods[period].endTime;
                    endTime = endTime.replace(/(\d{2})(\d{2})/, "$1:$2");
                    timeCell.style.display = '';
                    timeCell.innerHTML = startTime + "<br> - <br>" + endTime;

                    ++period;
                }
                else
                {
                    timeCell.style.display = "none";
                }
            }
        }
    };

    /**
     * here the table head changes to the grids specified weekdays with start day and end day
     */
    this.setGridDays = function ()
    {
        var currentDay = this.timeGrid.startDay, endDay = this.timeGrid.endDay,
            headerDate = window.dateField.valueAsDate, day = headerDate.getDay(),
            head = this.table.getElementsByTagName("thead")[0], headItems = head.getElementsByTagName("th");

        // Set date to monday of the same week
        if (day === 0)
        {
            headerDate.setDate(headerDate.getDate() - 6);
        }
        else
        {
            // Sunday is 0, so we add a one for monday
            headerDate.setDate(headerDate.getDate() - day + 1);
        }

        // Show TIME header on the left side ?
        headItems[0].style.display = this.timeGrid.hasOwnProperty("periods") ? '' : "none";

        // Fill thead with days of week
        for (var thElement = 1; thElement < headItems.length; ++thElement)
        {
            if (thElement === currentDay && currentDay <= endDay)
            {
                headItems[thElement].innerHTML = weekdays[currentDay - 1] + " (" + headerDate.getPresentationFormat() + ")";
                headerDate.setDate(headerDate.getDate() + 1);
                ++currentDay;
            }
            else
            {
                headItems[thElement].innerHTML = '';
            }
        }
    };

    /**
     * inserts lessons into a schedule
     *
     * @param lessons array
     */
    this.insertLessons = function (lessons)
    {
        var colNumber = window.isMobile ? this.visibleDay : 1,
            rows = this.table.getElementsByTagName("tbody")[0].getElementsByTagName("tr"),
            block, lesson, tableStartTime, tableEndTime, blockTimes, lessonElements, blockIndex, blockStart, blockEnd,
            cell, nextCell, lessonsNextBlock, showOwnTime;

        for (var date in lessons)
        {
            if (!lessons.hasOwnProperty(date))
            {
                continue;
            }

            // No times on the left side - every lesson appears in the first row
            if (!this.timeGrid.periods)
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

                        lessonElements = this.createLesson(lessons[date][block][lesson], true);
                        lessonElements.forEach(function (element)
                        {
                            cell = rows[0].getElementsByTagName("td")[colNumber];
                            cell.appendChild(element);
                        });
                    }
                }
            }
            // Insert lessons in cells
            else
            {
                blockIndex = 0;
                for (block in lessons[date])
                {
                    if (!lessons[date].hasOwnProperty(block))
                    {
                        continue;
                    }

                    // TODO: if (!rows[blockIndex].className.match(/break-row/)) row überspringen

                    // Periods start at 1, html td-elements at 0
                    tableStartTime = this.timeGrid.periods[blockIndex + 1].startTime;
                    tableEndTime = this.timeGrid.periods[blockIndex + 1].endTime;
                    blockTimes = block.match(/^(\d{4})-(\d{4})$/);
                    blockStart = blockTimes[1];
                    blockEnd = blockTimes[2];

                    // Block does not fit? go to next block
                    while (tableEndTime < blockStart)
                    {
                        ++blockIndex;
                        tableStartTime = this.timeGrid.periods[blockIndex + 1].startTime;
                        tableEndTime = this.timeGrid.periods[blockIndex + 1].endTime;
                    }

                    for (lesson in lessons[date][block])
                    {
                        if (!lessons[date][block].hasOwnProperty(lesson))
                        {
                            continue;
                        }
                        cell = rows[blockIndex].getElementsByTagName("td")[colNumber];
                        jQuery(cell).addClass("lessons");

                        // Append lesson in current table cell
                        showOwnTime = tableStartTime != blockStart || tableEndTime != blockEnd;
                        lessonElements = this.createLesson(lessons[date][block][lesson], showOwnTime);
                        lessonElements.forEach(function (element)
                        {
                            cell.appendChild(element);
                        });

                        // Lesson fits into next cell too, so add a copy to this
                        if (rows[blockIndex + 1] && blockEnd > this.timeGrid.periods[blockIndex + 2].startTime)
                        {
                            nextCell = rows[blockIndex + 1].getElementsByTagName("td")[colNumber];
                            lessonsNextBlock = this.createLesson(lessons[date][block][lesson], true);
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
    };

    /**
     * Creates a lesson which means a div element filled by data
     *
     * @param data          Object      lesson data
     * @param ownTime       boolean     show own time
     *
     * @returns Array|boolean           HTMLDivElements in an array or false in case of wrong input
     */
    this.createLesson = function (data, ownTime)
    {
        var lessons, subject, subjectData, lessonElement, ownTimeSpan, subjectSpan, moduleSpan, poolID, poolName,
            poolSpan, poolLink, teacherSpan, teacherLink, teacherID, teacherName, roomSpan, roomLink, roomID, roomName,
            saveActionButton, deleteActionButton, buttonIcon, added = false, subjectNumbers;

        if (!data || !data.hasOwnProperty("subjects"))
        {
            return false;
        }

        ownTime = typeof ownTime === "undefined" ? false : ownTime;
        lessons = [];

        for (subject in data.subjects)
        {
            if (!data.subjects.hasOwnProperty(subject))
            {
                return false;
            }
            subjectData = data.subjects[subject];

            lessonElement = document.createElement("div");
            lessonElement.className = "lesson";

            // Data attributes instead of classes for finding the lesson later
            lessonElement.dataset.ccmID = data.ccmID;

            // Delta = "removed" or "new" or "changed" ? add class like "lesson-new"
            if (data.lessonDelta)
            {
                lessonElement.className += " lesson-" + data.lessonDelta;
            }
            if (data.calendarDelta)
            {
                lessonElement.className += " calendar-" + data.calendarDelta;
            }
            if (ownTime && data.startTime && data.endTime)
            {
                ownTimeSpan = document.createElement("span");
                ownTimeSpan.className = "own-time";
                ownTimeSpan.innerHTML =
                    data.startTime.match(/^(\d{2}:\d{2})/)[1] + " - " + data.endTime.match(/^(\d{2}:\d{2})/)[1];
                lessonElement.appendChild(ownTimeSpan);
            }
            if (subjectData.shortName)
            {
                subjectSpan = document.createElement("span");
                subjectSpan.className = "name " + (data.subjectDelta ? data.subjectDelta : "");
                subjectSpan.innerHTML = subjectData.shortName;
                lessonElement.appendChild(subjectSpan);
            }
            if (subjectData.subjectNo)
            {
                // multiple spans in case of semicolon separated module number for the design
                subjectNumbers = subjectData.subjectNo.split(";");
                for (var numIndex = 0; numIndex < subjectNumbers.length; ++numIndex)
                {
                    moduleSpan = document.createElement("span");
                    moduleSpan.className = "module";
                    moduleSpan.innerHTML = subjectNumbers[numIndex];
                    lessonElement.appendChild(moduleSpan);
                }
            }
            if (schedule.resource !== "pool" && subjectData.pools)
            {
                for (poolID in subjectData.pools)
                {
                    if (subjectData.pools.hasOwnProperty(poolID))
                    {
                        poolName = subjectData.pools[poolID].fullName;
                        poolLink = document.createElement("a");
                        poolLink.innerHTML = poolName;
                        poolLink.addEventListener("click", function ()
                        {
                            sendLessonRequest('pool', poolID, poolName);
                        });
                        poolSpan = document.createElement("span");
                        poolSpan.className = "pool " + (data.poolDelta ? data.poolDelta : "");
                        poolSpan.appendChild(poolLink);
                        lessonElement.appendChild(poolSpan);
                    }
                }
            }
            if (schedule.resource !== "teacher" && subjectData.teachers)
            {
                for (teacherID in subjectData.teachers)
                {
                    if (subjectData.teachers.hasOwnProperty(teacherID))
                    {
                        teacherName = subjectData.teachers[teacherID];
                        teacherLink = document.createElement("a");
                        teacherLink.innerHTML = teacherName;
                        teacherLink.addEventListener("click", function ()
                        {
                            sendLessonRequest('teacher', teacherID, teacherName);
                        });
                        teacherSpan = document.createElement("span");
                        teacherSpan.className = "person " + (data.teacherDelta ? data.teacherDelta : ""); // TODO: teacherDelta
                        teacherSpan.appendChild(teacherLink);
                        lessonElement.appendChild(teacherSpan);
                    }
                }
            }

            if (schedule.resource !== "room" && subjectData.rooms)
            {
                for (roomID in subjectData.rooms)
                {
                    if (subjectData.rooms.hasOwnProperty(roomID))
                    {
                        roomName = subjectData.rooms[roomID];
                        roomLink = document.createElement("a");
                        roomLink.innerHTML = roomName;
                        roomLink.addEventListener("click", function ()
                        {
                            sendLessonRequest('room', roomID, roomName);
                        });
                        roomSpan = document.createElement("span");
                        roomSpan.className = "location " + (data.roomDelta ? data.roomDelta : ""); // TODO: roomDelta
                        roomSpan.appendChild(roomLink);
                        lessonElement.appendChild(roomSpan);
                    }
                }
            }

            if (variables.registered)
            {
                // Makes delete button visible only
                if (this.userSchedule || this.isSavedByUser(lessonElement))
                {
                    lessonElement.className += " added";
                    added = true;
                }

                // Right click on lessons show save/delete menu
                lessonElement.addEventListener("contextmenu", function (event)
                {
                    if (added)
                    {
                        window.lessonMenu.getDeleteMenu(this);
                    }
                    else
                    {
                        window.lessonMenu.getSaveMenu(this);
                    }

                    event.preventDefault();
                });

                // Buttons for instant saving/deleting without extra context menu
                saveActionButton = document.createElement("button");
                saveActionButton.className = "add-lesson";
                buttonIcon = document.createElement("span");
                buttonIcon.className = "icon-plus";
                saveActionButton.appendChild(buttonIcon);
                saveActionButton.addEventListener("click", function ()
                {
                    handleLesson(variables.SAVE_MODE_SEMESTER, data.ccmID, true);
                });
                lessonElement.appendChild(saveActionButton);

                deleteActionButton = document.createElement("button");
                deleteActionButton.className = "delete-lesson";
                buttonIcon = document.createElement("span");
                buttonIcon.className = "icon-delete";
                deleteActionButton.appendChild(buttonIcon);
                deleteActionButton.addEventListener("click", function ()
                {
                    handleLesson(variables.SAVE_MODE_SEMESTER, data.ccmID, false);
                });
                lessonElement.appendChild(deleteActionButton);
            }
            else
            {
                lessonElement.className += " no-saving";
            }

            this.lessonElements.push(lessonElement);
            lessons.push(lessonElement);
        }

        return lessons;
    };

    /**
     * checks for a lesson if it is already saved in the users schedule
     *
     * @param lesson HTMLDivElement
     * @return boolean
     */
    this.isSavedByUser = function (lesson)
    {
        var userSchedule = window.scheduleObjects.getScheduleById("user"), lessons;

        if (!lesson || !userSchedule)
        {
            return false;
        }

        lessons = userSchedule.scheduleTable.lessonElements;
        for (var lessonIndex = 0; lessonIndex < lessons.length; ++lessonIndex)
        {
            if (lessons[lessonIndex].dataset.ccmID === lesson.dataset.ccmID)
            {
                return true;
            }
        }

        return false;
    };

    /**
     * Removes all lessons
     */
    this.resetTable = function ()
    {
        var lessons = this.lessonElements;

        for (var index = lessons.length - 1; index >= 0; --index)
        {
            lessons[index].parentNode.className = "";
            lessons[index].parentNode.removeChild(lessons[index]);
        }

        this.lessonElements = [];
    };

    /**
     * Sets only the selected day column visible for mobile devices
     */
    this.setActiveColumn = function ()
    {
        var heads, cells, rows = this.table.getElementsByTagName("tr");

        for (var row = 0; row < rows.length; ++row)
        {
            heads = rows[row].getElementsByTagName("th");
            for (var head = 1; head < heads.length; ++head)
            {
                if (head === this.visibleDay)
                {
                    jQuery(heads[head]).addClass("activeColumn");
                }
                else
                {
                    jQuery(heads[head]).removeClass("activeColumn");
                }
            }
            cells = rows[row].getElementsByTagName("td");
            for (var cell = 1; cell < cells.length; ++cell)
            {
                if (cell === this.visibleDay)
                {
                    jQuery(cells[cell]).addClass("activeColumn");
                }
                else
                {
                    jQuery(cells[cell]).removeClass("activeColumn");
                }
            }
        }
    };

    /**
     * Removes the HTMLTableElement itself and the related HTMLInputElement
     */
    this.remove = function ()
    {
        // input element
        window.scheduleWrapper.removeChild(document.getElementById(schedule.id));
        // table element
        window.scheduleWrapper.removeChild(document.getElementById(schedule.id + "-schedule"));
    }
};

/**
 * Creates a lesson menu for saving and deleting a lesson, which opens by right clicking on it
 */
LessonMenu = function ()
{
    var that = this;
    this.currentCcmID = 0;
    this.lessonMenuElement = document.getElementsByClassName('lesson-menu')[0];
    this.saveMenu = undefined;
    this.closeSaveMenuButton = undefined;
    this.saveSemesterMode = document.getElementById('save-mode-semester');
    this.savePeriodMode = document.getElementById('save-mode-period');
    this.saveInstanceMode = document.getElementById('save-mode-instance');
    this.deleteMenu = undefined;
    this.closeDeleteMenuButton = undefined;
    this.deleteSemesterMode = document.getElementById('delete-mode-semester');
    this.deletePeriodMode = document.getElementById('delete-mode-period');
    this.deleteInstanceMode = document.getElementById('delete-mode-instance');

    /**
     * Detects HTML elements for saving/deleting a lesson to/from the users schedule and add eventListener
     */
    this.create = function ()
    {
        this.saveMenu = this.lessonMenuElement.getElementsByClassName('save')[0];
        this.closeSaveMenuButton = this.saveMenu.getElementsByClassName('icon-cancel')[0];
        this.deleteMenu = this.lessonMenuElement.getElementsByClassName('delete')[0];
        this.closeDeleteMenuButton = this.deleteMenu.getElementsByClassName('icon-cancel')[0];

        this.closeSaveMenuButton.addEventListener("click", function ()
        {
            that.saveMenu.style.display = "none";
        });
        this.saveSemesterMode.addEventListener("click", function ()
        {
            handleLesson(variables.SAVE_MODE_SEMESTER, that.currentCcmID, true);
            that.saveMenu.style.display = "none";
        });
        this.savePeriodMode.addEventListener("click", function ()
        {
            handleLesson(variables.SAVE_MODE_PERIOD, that.currentCcmID, true);
            that.saveMenu.style.display = "none";
        });
        this.saveInstanceMode.addEventListener("click", function ()
        {
            handleLesson(variables.SAVE_MODE_INSTANCE, that.currentCcmID, true);
            that.saveMenu.style.display = "none";
        });
        this.closeDeleteMenuButton.addEventListener("click", function ()
        {
            that.deleteMenu.style.display = "none";
        });
        this.deleteSemesterMode.addEventListener("click", function ()
        {
            handleLesson(variables.SAVE_MODE_SEMESTER, that.currentCcmID, false);
            that.deleteMenu.style.display = "none";
        });
        this.deletePeriodMode.addEventListener("click", function ()
        {
            handleLesson(variables.SAVE_MODE_PERIOD, that.currentCcmID, false);
            that.deleteMenu.style.display = "none";
        });
        this.deleteInstanceMode.addEventListener("click", function ()
        {
            handleLesson(variables.SAVE_MODE_INSTANCE, that.currentCcmID, false);
            that.deleteMenu.style.display = "none";
        });
    };

    /**
     * Pops up at clicked lesson and sends an ajaxRequest to save lessons ccmID
     *
     * @param lessonElement HTMLDivElement
     */
    this.getSaveMenu = function (lessonElement)
    {
        this.currentCcmID = lessonElement.dataset.ccmID;
        this.saveMenu.style.display = "block";
        this.deleteMenu.style.display = "none";
        this.lessonMenuElement.style.display = "block";
        lessonElement.appendChild(this.lessonMenuElement);
    };

    /**
     * Pops up at clicked lesson and sends an ajaxRequest to delete lessons ccmID
     *
     * @param lessonElement HTMLDivElement
     */
    this.getDeleteMenu = function (lessonElement)
    {
        this.currentCcmID = lessonElement.dataset.ccmID;
        this.saveMenu.style.display = "none";
        this.deleteMenu.style.display = "block";
        this.lessonMenuElement.style.display = "block";
        lessonElement.appendChild(this.lessonMenuElement);
    };
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
     * Removes a schedule and all related HTML elements
     * @param schedule Schedule object | string id
     */
    this.removeSchedule = function (schedule)
    {
        if (typeof schedule === "string")
        {
            schedule = this.schedules.find(
                function (obj)
                {
                    return obj.id === schedule;
                }
            );
        }

        if (schedule.scheduleTable)
        {
            schedule.scheduleTable.remove();
            this.schedules.splice(this.schedules.indexOf(schedule), 1);
        }
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
    this.getScheduleByResponse = function (responseUrl)
    {
        var matches = responseUrl.match(/&departmentIDs=\d+.*&(\w+)IDs=((\d+[,]?)*)&|$/),
            id = (!matches[0]) ? "user" : matches[1] + matches[2].replace(/,/g, "-");

        return this.getScheduleById(id);
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
        dayLong = day < 10 ? "0" + day : day,
        month = this.getMonth() + 1, // getMonth() is zero-based
        monthLong = month < 10 ? "0" + month : month,
        year = this.getYear(),
        yearLong = this.getFullYear();

    // Insert day
    date = date.replace(/j/, day.toString());
    date = date.replace(/d/, dayLong);
    // Insert month
    date = date.replace(/n/, month.toString());
    date = date.replace(/m/, monthLong);
    // Insert year
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
        mm < 10 ? "0" + mm : mm,
        dd < 10 ? "0" + dd : dd
    ].join("-"); // padding
};

/**
 * adds event listeners and initialise (user) schedule and date input form field
 */
jQuery(document).ready(function ()
{
    var startX, startY, hasDepartmentSelection = document.getElementById("department-input");

    initSchedule();
    computeTableHeight();
    setDatePattern();
    changePositionOfDateInput();

    /**
     * swipe touch event handler changing the shown day and date
     * @see http://www.javascriptkit.com/javatutors/touchevents.shtml
     * @see http://www.html5rocks.com/de/mobile/touch/
     */
    window.scheduleWrapper.addEventListener("touchstart", function (event)
    {
        var touch = event.changedTouches[0];
        startX = parseInt(touch.pageX);
        startY = parseInt(touch.pageY);
    });

    window.scheduleWrapper.addEventListener("touchend", function (event)
    {
        var touch = event.changedTouches[0], minDist = 50,
            distX = parseInt(touch.pageX) - startX,
            distY = parseInt(touch.pageY) - startY;

        if (Math.abs(distX) > Math.abs(distY))
        {
            if (distX < -(minDist))
            {
                event.stopPropagation();
                changeDate();
                updateSchedule();
            }
            if (distX > minDist)
            {
                event.stopPropagation();
                changeDate(false);
                updateSchedule();
            }
        }
    });

    jQuery("#grid").chosen().change(updateSchedule);
    jQuery("#date").change(updateSchedule);

    // Select 'programs' by website loading and load results
    sendFormRequest("program");
    if (hasDepartmentSelection)
    {
        onlyShowFormInput("department-input", false);
    }
    else
    {
        onlyShowFormInput("program-input");
    }

    // Form behaviour -> only show field, by selecting the "parent" before
    jQuery("#category").chosen().change(function ()
    {
        var chosenCategory = document.getElementById("category").value;

        switch (chosenCategory)
        {
            case "program":
                if (hasDepartmentSelection)
                {
                    onlyShowFormInput("department-input", false);
                    return;
                }
                onlyShowFormInput("program-input");
                break;
            case "roomtype":
                onlyShowFormInput("room-type-input");
                break;
            case "teacher":
                onlyShowFormInput("teacher-input");
                break;
            default:
                console.log("searching default category...");
        }

        sendFormRequest(chosenCategory);
    });

    if (hasDepartmentSelection)
    {
        jQuery("#department").chosen().change(function ()
        {
            var departments = getSelectedValues("department");
            if (!departments)
            {
                onlyShowFormInput("department-input", false);
            }
            else
            {
                variables.departmentID = departments;
                onlyShowFormInput(["department-input", "program-input"]);
                sendFormRequest("program");
            }
        });
    }

    jQuery("#program").chosen().change(function ()
    {
        if (!getSelectedValues("program") && hasDepartmentSelection)
        {
            return onlyShowFormInput(["department-input", "program-input"], false);
        }
        else if (!getSelectedValues("program"))
        {
            return onlyShowFormInput("program-input", false);
        }
        else if (hasDepartmentSelection)
        {
            onlyShowFormInput(["department-input", "program-input", "pool-input"]);
        }
        else
        {
            onlyShowFormInput(["program-input", "pool-input"]);
        }

        sendFormRequest("pool");
    });

    jQuery("#roomtype").chosen().change(function ()
    {
        if (!getSelectedValues("roomtype"))
        {
            onlyShowFormInput("room-type-input", false);
        }
        else
        {
            onlyShowFormInput(["room-type-input", "room-input"]);
            sendFormRequest("room");
        }
    });

    jQuery("#pool").chosen().change(function ()
    {
        if (getSelectedValues("pool"))
        {
            sendLessonRequest("pool");
        }
    });

    jQuery("#teacher").chosen().change(function ()
    {
        if (getSelectedValues("teacher"))
        {
            sendLessonRequest("teacher");
        }
    });

    jQuery("#room").chosen().change(function ()
    {
        if (getSelectedValues("room"))
        {
            sendLessonRequest("room");
        }
    });

    jQuery("#schedules").chosen().change(function ()
    {
        var scheduleInput = document.getElementById(jQuery("#schedules").val());

        // To show the schedule after this input field (by css)
        scheduleInput.checked = "checked";
    });

    // Change Tab-Behaviour of menu-bar, so all tabs can be closed
    jQuery(".tabs-toggle").on("click", function ()
    {
        changeTabBehaviour(jQuery(this));
    });
});

/**
 *
 */
function handleExport(format)
{
    var schedule = jQuery('#schedules').val(), url = variables.exportbase,
        formats, resourceID;

    formats = format.split('.');
    url += "&format=" + formats[0];

    if (formats[1] !== undefined)
    {
        url += "&documentFormat=" + formats[1];
    }

    if (schedule === 'user')
    {
        url += "&myschedule=1";
        if (formats[0] === 'ics')
        {
            url += "&username=" + variables.username + "&auth=" + variables.auth;
            window.prompt(text.copy, url);
            jQuery('#export-selection').val('placeholder');
            jQuery('#export-selection').trigger("chosen:updated");
            return;
        }
    }
    else
    {
        resourceID = schedule.match(/[0-9]+/);

        if (resourceID === null)
        {
            jQuery('#export-selection').val('placeholder');
            jQuery('#export-selection').trigger("chosen:updated");
            return;
        }

        if (schedule.search(/pool/) === 0)
        {
            url += "&poolIDs=" + resourceID;
        }
        else if (schedule.search(/room/) === 0)
        {
            url += "&roomIDs=" + resourceID;
        }
        else if (schedule.search(/teacher/) === 0)
        {
            url += "&teacherIDs=" + resourceID;
        }
        else
        {
            jQuery('#export-selection').val('placeholder');
            jQuery('#export-selection').trigger("chosen:updated");
            return;
        }
    }

    window.open(url);
    jQuery('#export-selection').val('placeholder');
    jQuery('#export-selection').trigger("chosen:updated");
    return;
}

/**
 * sets values for the start and shows only the actual day on mobile devices
 */
function initSchedule()
{
    var today = new Date();

    window.isMobile = window.matchMedia("(max-width: 677px)").matches;
    window.dateField = document.getElementById("date");
    window.dateField.valueAsDate = today;
    window.lessonMenu = new LessonMenu;
    window.lessonMenu.create();
    window.scheduleObjects = new Schedules;
    window.scheduleWrapper = document.getElementById("scheduleWrapper");
    window.weekdays = [
        text.MONDAY_SHORT,
        text.TUESDAY_SHORT,
        text.WEDNESDAY_SHORT,
        text.THURSDAY_SHORT,
        text.FRIDAY_SHORT,
        text.SATURDAY_SHORT,
        text.SUNDAY_SHORT
    ];

    if (variables.registered)
    {
        createUserSchedule();
        switchToScheduleListTab();
    }

    if (!browserSupportsDate())
    {
        window.dateField.value = today.getPresentationFormat();

        // calendar.js
        if (typeof initCalendar === "function")
        {
            initCalendar();
        }
    }
}

/**
 * gets the users schedule, write it into a HTML table and add it to schedules selection
 */
function createUserSchedule()
{
    var schedule = new Schedule("user");
    schedule.create();
    schedule.requestUpdate();
    addScheduleToSelection(schedule);
    window.scheduleObjects.addSchedule(schedule);
}

/**
 * starts an Ajax request to fill form fields with values
 *
 * @param resource  string
 */
function sendFormRequest(resource)
{
    var task = "&departmentIDs=" + variables.departmentID;

    switch (resource)
    {
        case "program":
            task += "&task=getPrograms";
            break;
        case "pool":
            task += "&task=getPools" + "&programIDs=" + getSelectedValues("program");
            break;
        case "roomtype":
            task += "&task=getRoomTypes";
            break;
        case "room":
            task += "&task=getRooms" + "&typeID=" + getSelectedValues("roomtype");
            break;
        case "teacher":
            task += "&task=getTeachers";
            break;
        default:
            console.log("searching default category...");
    }

    // Global variable for catching responds in other functions
    ajaxSelection = new XMLHttpRequest();
    ajaxSelection.open("GET", ajaxUrl + task, true);
    ajaxSelection.onreadystatechange = updateForm;
    ajaxSelection.send(null);
}

/**
 * updates form fields with data from Ajax requests
 */
function updateForm()
{
    var values, fieldID, formField, option, optionCount;

    if (ajaxSelection.readyState === 4 && ajaxSelection.status === 200)
    {
        values = JSON.parse(ajaxSelection.responseText);
        fieldID = ajaxSelection.responseURL.match(/&task=get(\w+)s/)[1].toLowerCase();
        formField = document.getElementById(fieldID);
        removeChildren(formField);
        optionCount = Object.keys(values).length;

        for (var value in values)
        {
            // Prevent using prototype variables
            if (values.hasOwnProperty(value))
            {
                if (value.name)
                {
                    option = document.createElement("option");
                    option.setAttribute("value", values[value].id);
                    option.innerHTML = values[value].name;
                    option.selected = optionCount === 1;
                    formField.appendChild(option);
                }
                // Needed - otherwise select field is empty
                else
                {
                    option = document.createElement("option");
                    option.setAttribute("value", values[value]);
                    option.innerHTML = value;
                    option.selected = optionCount === 1;
                    formField.appendChild(option);
                }
            }
        }

        formField.removeAttribute("disabled");
        jQuery("#" + fieldID).chosen("destroy").chosen();

        if (optionCount === 1)
        {
            sendLessonRequest(fieldID);
        }
    }
}

/**
 * Sends an Ajax request to update all schedules or just the specified one.
 *
 * @param id   string
 */
function updateSchedule(id)
{
    if (typeof id === "string")
    {
        window.scheduleObjects.getScheduleById(id).requestUpdate();
    }
    else
    {
        scheduleObjects.schedules.forEach(
            function (schedule)
            {
                // Function called by grids eventListener
                if (id && id.target && id.target.id === "grid")
                {
                    schedule.updateTable();
                }
                else
                {
                    schedule.requestUpdate();
                }
            }
        );
    }

    computeTableHeight();
}

/**
 * starts an Ajax request to get lessons for the selected resource
 *
 * @param resource  string
 * @param optionalID  string specific id instead of resource form selection
 * @param optionalTitle  string the same with optionalID
 */
function sendLessonRequest(resource, optionalID, optionalTitle)
{
    var IDs = optionalID || getSelectedValues(resource, "-"), schedule = scheduleObjects.getScheduleById(resource + IDs);

    if (schedule)
    {
        schedule.requestUpdate();
    }
    else
    {
        schedule = new Schedule(resource, IDs, optionalTitle);
        schedule.create();
        schedule.requestUpdate();
        scheduleObjects.addSchedule(schedule);
        addScheduleToSelection(schedule);
        switchToScheduleListTab();
    }
}

/**
 * Processes Ajax responses and updates the related schedules
 */
function insertLessonResponse()
{
    var ajaxRequest, schedule;

    for (var ajaxIndex = 0; ajaxIndex < window.scheduleRequests.length; ++ajaxIndex)
    {
        ajaxRequest = window.scheduleRequests[ajaxIndex];
        if (ajaxRequest.readyState === 4 && ajaxRequest.status === 200)
        {
            schedule = window.scheduleObjects.getScheduleByResponse(ajaxRequest.responseURL);
            schedule.setLessons(JSON.parse(ajaxRequest.responseText));
            window.scheduleRequests.splice(ajaxIndex, 1);
        }
    }

    computeTableHeight();
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
    var mode = (typeof taskNumber === "undefined") ? "1" : taskNumber,
        saving = (typeof save === "undefined") ? true : save,
        task = "&task= " + (saving ? "&task=saveLesson" : "&task=deleteLesson");

    if (!ccmID)
    {
        return false;
    }

    task += "&saveMode=" + mode + "&ccmID=" + ccmID;
    ajaxSave = new XMLHttpRequest();
    ajaxSave.open("GET", ajaxUrl + task, true);
    ajaxSave.onreadystatechange = lessonHandled;
    ajaxSave.send(null);
}

/**
 * replaces the save button of a saved lesson with a delete button and reverse
 */
function lessonHandled()
{
    var handledLessons, lessonElements;

    if (ajaxSave.readyState === 4 && ajaxSave.status === 200)
    {
        handledLessons = JSON.parse(ajaxSave.responseText);

        window.scheduleObjects.schedules.forEach(
            function (schedule)
            {
                lessonElements = schedule.scheduleTable.lessonElements;
                for (var lessonIndex = 0; lessonIndex < lessonElements.length; ++lessonIndex)
                {
                    if (handledLessons.includes(lessonElements[lessonIndex].dataset.ccmID))
                    {
                        if (lessonElements[lessonIndex].className === "lesson")
                        {
                            lessonElements[lessonIndex].className += " added";
                        }
                        else
                        {
                            lessonElements[lessonIndex].className = "lesson";
                        }
                    }
                }
            }
        );

        updateSchedule("user");
    }
}

/**
 * create a new entry in the dropdown field for selecting a schedule
 *
 * @param schedule Schedule object
 */
function addScheduleToSelection(schedule)
{
    var option = document.createElement("option");

    option.innerHTML = "<span class='title'>" + schedule.title + "</span>";
    if (schedule.id != "user")
    {
        option.innerHTML += "<button onclick='removeScheduleFromSelection()' class='removeOption'>" +
            "<span class='icon-remove'></span></button>";
    }
    option.value = schedule.id;
    option.selected = "selected";
    document.getElementById("schedules").appendChild(option);
    jQuery("#schedules").chosen("destroy").chosen();
}

/**
 * remove an entry from the dropdown field for selecting a schedule
 * works just with chosen, don't work in mobile, because chosen isn't be used there
 */
function removeScheduleFromSelection()
{
    var selection = document.getElementById("schedules"), scheduleName = selection[selection.selectedIndex].value,
        nextSchedule;

    window.scheduleObjects.removeSchedule(scheduleName);
    selection.remove(selection.selectedIndex);
    jQuery("#schedules").chosen("destroy").chosen();

    nextSchedule = document.getElementById(jQuery("#schedules").val());
    if (nextSchedule)
    {
        nextSchedule.checked = "checked";
    }
    else if (!variables.registered)
    {
        document.getElementById("default-schedule").checked = "checked";
    }
}

/**
 * every div with the class 'input-wrapper' gets hidden, when it is not named as param.
 *
 * @param fieldIDsToShow string|Array
 * @param disable boolean default = true
 */
function onlyShowFormInput(fieldIDsToShow, disable)
{
    var disabled = typeof disable === "undefined" ? true : disable, field, fieldElement, fieldToShow,
        form = document.getElementById("schedule-form"), fields = form.getElementsByClassName("input-wrapper");

    for (var fieldIndex = 0; fieldIndex < fields.length; ++fieldIndex)
    {
        field = fields[fieldIndex];

        if (fieldIDsToShow instanceof Array)
        {
            if (fieldIDsToShow.indexOf(field.id) === -1)
            {
                field.style.display = "";
            }
            else
            {
                field.style.display = "inline-block";
                fieldToShow = field;
            }
        }
        else
        {
            if (field.id !== fieldIDsToShow)
            {
                field.style.display = "";
            }
            else
            {
                field.style.display = "inline-block";
                fieldToShow = field;
            }
        }
    }

    fieldElement = fieldToShow.getElementsByTagName("select")[0];
    fieldElement.disabled = disabled;
    jQuery("#" + fieldElement.id).chosen("destroy").chosen();
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
 * @param separator string default = ","
 * @returns string
 */
function getSelectedValues(fieldID, separator)
{
    var sep = (!separator) ? "," : separator, chosenOptions = document.getElementById(fieldID).selectedOptions,
        selectedValues = "";

    for (var selectIndex = 0; selectIndex < chosenOptions.length; ++selectIndex)
    {
        selectedValues += chosenOptions[selectIndex].value;

        if (chosenOptions[selectIndex + 1])
        {
            selectedValues += sep;
        }
    }

    return selectedValues;
}

/**
 * tests the support of the browser for the input type=date
 * @see http://stackoverflow.com/questions/10193294/how-can-i-tell-if-a-browser-supports-input-type-date
 *
 * @returns boolean
 */
function browserSupportsDate()
{
    var input = document.createElement("input"), notValidDate = "not-valid-date";

    input.setAttribute("type", "date");
    input.setAttribute("value", notValidDate);

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
    var increaseDate = (typeof nextDate === "undefined") ? true : nextDate,
        day = (typeof dayStep === "undefined") ? true : dayStep, scheduleDate = getDateFieldsDateObject();

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

        // Jump over sunday
        if (scheduleDate.getDay() === 0)
        {
            scheduleDate.setDate(scheduleDate.getDate() + 1);
        }
    }
    // Decrease date
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

        // Jump over sunday
        if (scheduleDate.getDay() === 0)
        {
            scheduleDate.setDate(scheduleDate.getDate() - 1);
        }
    }

    window.dateField.valueAsDate = scheduleDate;

    // For browsers which doesn't update the value with the valueAsDate property for type=date
    if (!browserSupportsDate())
    {
        window.dateField.value = scheduleDate.getPresentationFormat();
    }
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
    var parts = window.dateField.value.split(".", 3);

    if (browserSupportsDate())
    {
        return window.dateField.valueAsDate;
    }

    // 12:00:00 o'clock for timezone offset
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
    // Escape bindings like dots
    pattern = pattern.replace(/\./g, "\\.");
    pattern = pattern.replace(/\\/g, "\\");

    window.datePattern = new RegExp(pattern);
}

/**
 * Change tab-behaviour of tabs in menu-bar, so all tabs can be closed
 *
 * @param clickedTab Object
 */
function changeTabBehaviour(clickedTab)
{
    var tabId = clickedTab.attr("data-id");

    if (clickedTab.parent("li").hasClass("active"))
    {
        clickedTab.parent("li").toggleClass("inactive", "");
        jQuery("#" + tabId).toggleClass("inactive", "");
    }
    else
    {
        jQuery(".tabs-tab").removeClass("inactive");
        jQuery(".tab-panel").removeClass("inactive");
    }
}

/**
 * Activates tab with a list of selected schedules
 */
function switchToScheduleListTab()
{
    jQuery("#tab-selected-schedules").parent("li").addClass("active");
    jQuery("#selected-schedules").addClass("active");
    jQuery("#tab-schedule-form").parent("li").removeClass("active");
    jQuery("#schedule-form").removeClass("active")
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

/**
 * changes the schedules div min-height as high as the maximum of all the lessons
 */
function computeTableHeight()
{
    var schedules = document.getElementsByClassName("schedule-table"),
        rows, cell, lessonsInCell, lessonCount, emptyCellCount, maxLessons = 0, emptyBlocksInMaxLessons = 0,
        headerHeight, remPerEmptyRow, minHeight, remPerLesson, calcHeight, totalRemHeight;

    // counting the lessons in horizontal order
    for (var schedule = 0; schedule < schedules.length; ++schedule)
    {
        rows = schedules[schedule].getElementsByTagName("tbody")[0].getElementsByTagName("tr");
        var rowLength = rows.length;
        var cellLength = rows[0].getElementsByTagName("td").length;

        // Monday to Fr/Sa/Su
        for (var day = 1; day < cellLength; ++day)
        {
            lessonCount = 0;
            emptyCellCount = 0;

            // Block 1 to ~6
            for (var block = 0; block < rowLength; ++block)
            {
                // To jump over break (cell length == 1)
                if (rows[block].getElementsByTagName("td").length > 1)
                {
                    cell = rows[block].getElementsByTagName("td")[day];
                    lessonsInCell = cell.getElementsByClassName("lesson").length;
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
    window.scheduleWrapper.style.minHeight = totalRemHeight + "rem";
}