/* To prevent JSHint warning for Joomla object: */
/* globals Joomla */

jQuery(document).ready(function () {
    'use strict';
    window.scheduleApp = new ScheduleApp(Joomla.getOptions('variables', {}));
});

/**
 * Object that builds schedule tables, an interactive calendar and a form which defines loaded schedules
 * @param {Object} variables - contains website configurations
 * @param {string} variables.ajaxBase - basic url for ajax requests
 * @param {string} variables.auth - token to authenticate user
 * @param {string} variables.dateFormat - configured format of date for this website (e.g. d.m.Y)
 * @param {string} variables.defaultGrid - JSON which contains the default schedule grid
 * @param {number} variables.departmentID - ID of selected department
 * @param {string} variables.delta - amount of days deleted/moved events should get displayed
 * @param {string} variables.displayName - indicates whether name of page should be displayed
 * @param {string} variables.exportBase - basic url for exporting schedules
 * @param {Object.<number, Object>} variables.grids - all schedule grids with days and times
 * @param {number} variables.INSTANCE_MODE - mode for saving/deleting single event instances
 * @param {boolean} variables.isMobile - checks type of device
 * @param {string} variables.menuID - active menu id (used as session key)
 * @param {number} variables.BLOCK_MODE - mode for saving/deleting all event instances for a single dow/block
 * @param {boolean} variables.userID - indicates whether an user is logged in
 * @param {number} variables.SEMESTER_MODE - mode for saving/deleting all event instances
 * @param {number} variables.showGroups - whether groups are allowed to show
 * @param {string} variables.subjectDetailBase - basic url for subject item
 * @param {string} variables.username - name of currently logged in user
 */
const ScheduleApp = function (variables) {
    'use strict';

    const app = this,
        ajaxSave = new XMLHttpRequest(),
        futureDateButton = document.getElementById('future-date'),
        nextDateSelection = document.getElementById('next-date-selection'),
        noEvents = document.getElementById('no-events'),
        pastDateButton = document.getElementById('past-date'),
        regFifo = document.getElementById('reg-fifo'),
        regManual = document.getElementById('reg-manual'),
        scheduleWrapper = document.getElementById('scheduleWrapper'),
        weekdays = [
            Joomla.JText._('MON'),
            Joomla.JText._('TUE'),
            Joomla.JText._('WED'),
            Joomla.JText._('THU'),
            Joomla.JText._('FRI'),
            Joomla.JText._('SAT'),
            Joomla.JText._('SUN')
        ],
        /**
         * RegExp for date format, specified by website configuration
         */
        datePattern = (function () {
            let pattern = variables.dateFormat;

            pattern = pattern.replace(/d/, '([0-9]{2})');
            pattern = pattern.replace(/j/, '([0-9]{1,2})');
            pattern = pattern.replace(/m/, '([0-9]{2})');
            pattern = pattern.replace(/n/, '([0-9]{1,2})');
            pattern = pattern.replace(/y/, '([0-9]{2})');
            pattern = pattern.replace(/Y/, '([0-9]{4})');
            // Escape bindings like dots
            pattern = pattern.replace(/[/]/g, '\\/');
            pattern = pattern.replace(/[.]/g, '\\.');

            return new RegExp(pattern);
        })();
    // Get initialised in constructor
    let calendar, form, instanceMenu, scheduleObjects;

    /**
     * Calendar class for a date input field with HTMLTableElement as calendar.
     * By choosing a date, schedules are updated.
     */
    function Calendar()
    {
        const calendarDiv = document.getElementById('calendar'),
            month = document.getElementById('display-month'),
            months = [
                Joomla.JText._('JANUARY'),
                Joomla.JText._('FEBRUARY'),
                Joomla.JText._('MARCH'),
                Joomla.JText._('APRIL'),
                Joomla.JText._('MAY'),
                Joomla.JText._('JUNE'),
                Joomla.JText._('JULY'),
                Joomla.JText._('AUGUST'),
                Joomla.JText._('SEPTEMBER'),
                Joomla.JText._('OCTOBER'),
                Joomla.JText._('NOVEMBER'),
                Joomla.JText._('DECEMBER')
            ],
            table = document.getElementById('calendar-table'),
            that = this,
            year = document.getElementById('display-year');
        let activeDate = new Date(),
            // Helper for inner functions
            calendarIsVisible = false;

        /**
         * Display calendar controls like changing to previous month.
         */
        function showControls()
        {
            const dateControls = document.getElementsByClassName('date-input')[0].getElementsByClassName('controls');
            let controlIndex;

            for (controlIndex = 0; controlIndex < dateControls.length; ++controlIndex)
            {
                dateControls[controlIndex].style.display = 'inline';
            }
        }

        /**
         * Displays month and year in calendar table head
         */
        function setUpCalendarHead()
        {
            month.innerHTML = months[activeDate.getMonth()];
            year.innerHTML = activeDate.getFullYear().toString();
        }

        /**
         * Deletes the rows of the calendar table for refreshing.
         */
        function resetTable()
        {
            const tableBody = table.getElementsByTagName('tbody')[0],
                rowLength = table.getElementsByTagName('tr').length;
            let rowIndex;

            for (rowIndex = 0; rowIndex < rowLength; ++rowIndex)
            {
                // "-1" represents the last row
                tableBody.deleteRow(-1);
            }
        }

        /**
         * Calendar table gets filled with days of the month, chosen by the given date
         * Inspired by https://wiki.selfhtml.org/wiki/JavaScript/Anwendung_und_Praxis/Monatskalender
         */
        function fillCalendar()
        {
            const generalMonth = new Date(activeDate.getFullYear(), activeDate.getMonth(), 1),
                month = activeDate.getMonth() + 1,
                months30days = [4, 6, 9, 11],
                tableBody = table.getElementsByTagName('tbody')[0],
                weekdayStart = generalMonth.getDay() || 7,
                year = activeDate.getFullYear();
            let cellIndex, days = 31, day = 1, rowCount, rowIndex;

            // Compute count of days
            if (months30days.indexOf(month) !== -1)
            {
                days = 30;
            }

            if (month === 2)
            {
                days = (year % 4 === 0) ? 29 : 28;
            }

            // Append rows to table
            rowCount = Math.min(Math.ceil((days + generalMonth.getDay() - 1) / 7), 6);

            for (rowIndex = 0; rowIndex <= rowCount; ++rowIndex)
            {
                const row = tableBody.insertRow(rowIndex);

                for (cellIndex = 0; cellIndex <= 6; ++cellIndex)
                {
                    const cell = row.insertCell(cellIndex);

                    if ((rowIndex === 0 && cellIndex < weekdayStart - 1) || day > days)
                    {
                        cell.innerHTML = ' ';
                    }
                    else
                    {
                        addInsertDateButton(new Date(year, month - 1, day), cell);
                        ++day;
                    }
                }
            }
        }

        /**
         * Appends one button to a table cell which inserts given date
         * @param {Date} date
         * @param {HTMLElement} cell
         */
        function addInsertDateButton(date, cell)
        {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'day';
            button.innerHTML = date.getDate().toString();
            button.addEventListener('click', function () {
                that.insertDate(date);
            }, false);
            cell.appendChild(button);
        }

        /**
         * Increase or decrease displayed month in calendar table.
         * @param {boolean} increaseMonth
         */
        this.changeCalendarMonth = function (increaseMonth) {
            if (increaseMonth)
            {
                // Day 1 for preventing get Feb 31
                activeDate.setMonth(activeDate.getMonth() + 1, 1);
            }
            else
            {
                activeDate.setMonth(activeDate.getMonth() - 1, 1);
            }

            this.setUpCalendar();
        };

        /**
         * Changes the current (date field) date and updates schedules
         * @param {boolean} increase - increase or decrease date
         * @param {string} step - how big the change step is ('day'|'week'|'month')
         */
        this.changeSelectedDate = function (increase, step) {
            changeDate(increase, step);
            app.updateSchedule();

            if (calendarIsVisible)
            {
                this.setUpCalendar();
            }
        };

        /**
         * Hides or shows the calendar, depending on its previous status.
         */
        this.showCalendar = function () {
            calendarDiv.style.visibility = (calendarIsVisible) ? 'hidden' : 'visible';
            calendarIsVisible = !calendarIsVisible;

            if (calendarIsVisible)
            {
                this.setUpCalendar();
            }
        };

        /**
         * Hides the calendar.
         */
        this.hideCalendar = function () {
            calendarDiv.style.visibility = 'hidden';
            calendarIsVisible = false;
        };

        /**
         * The date chosen in the calendar table gets set in the date field
         * @param {Date} [date]
         */
        this.insertDate = function (date) {
            activeDate = (typeof date === 'undefined') ? new Date() : date;
            app.dateField.value = activeDate.getPresentationFormat();
            window.sessionStorage.setItem('scheduleDate', activeDate.toJSON());
            this.hideCalendar();
            app.updateSchedule();
        };

        /**
         * Builds the calendar (table), depending on a given date or the date field.
         */
        this.setUpCalendar = function () {
            resetTable();
            setUpCalendarHead();
            fillCalendar();
        };

        /**
         * Getter for visibility of this calendar
         * @returns {boolean}
         */
        this.isVisible = function () {
            return calendarIsVisible;
        };

        /**
         * This function is called immediately after creating a new Calendar.
         * Sets eventListeners for HTML-elements and variables.
         */
        (function () {
            that.activeDate = getDateFieldsDateObject();
            showControls();
            app.dateField.addEventListener('change', that.setUpCalendar);
            document.getElementById('today').addEventListener('click', function () {
                that.insertDate();
                that.setUpCalendar();
            });
        })();
    }

    /**
     * Schedule 'class' for saving params and update the scheduleTable
     * @param {string} source - name of source (e.g. form-input)
     * @param {string} [IDs] - makes together with source the schedule ID and defines the task
     * @param {string} [optionalTitle] - optional title for directly linked schedules (e.g. person or room)
     */
    function Schedule(source, IDs, optionalTitle)
    {
        const ajaxRequest = new XMLHttpRequest(),
            id = (source === 'user' ? source : IDs ? source + IDs : source + getSelectedValues(source, '-')),
            resource = source,
            resourceIDs = IDs ? IDs : source === 'user' ? null : getSelectedValues(source, '-'),
            that = this;
        let events = [],
            /**
             * @var ScheduleTable
             */
            table,

            /**
             * Sets Ajax url for updating events
             */
            ajaxUrl = (function () {
                let url = getAjaxUrl();

                url += '&view=instances';
                url += '&date=' + getDateFieldString() + (variables.isMobile ? '&interval=day' : '');
                url += '&delta=' + variables.delta;

                if (resource === 'user')
                {
                    url += '&mySchedule=' + (resource === 'user' ? '1' : '0');
                }
                else
                {
                    url += '&' + resource + 'IDs=' + resourceIDs;
                }

                return url;
            })(),

            /**
             * Sets title that depends on the selected schedule
             */
            title = (function () {
                const resourceField = document.getElementById(resource),
                    categoryField = document.getElementById('category'),
                    selection = [];

                if (optionalTitle)
                {
                    return optionalTitle;
                }

                if (resource === 'user')
                {
                    return Joomla.JText._('ORGANIZER_MY_SCHEDULE');
                }

                // Get pre-selected value like 'Informatik Master'
                if (resource === 'group' && categoryField.selectedIndex !== -1)
                {
                    (function () {
                        const options = categoryField.options;
                        let index;

                        for (index = 0; index < options.length; ++index)
                        {
                            if (options[index].selected)
                            {
                                selection.push(options[index].text);
                                return;
                            }
                        }
                    })();
                }

                // Get resource selection like '1. Semester' or 'A20.1.1'
                if (resourceField && resourceField.selectedIndex !== -1)
                {
                    (function () {
                        const options = resourceField.options;
                        let index;

                        for (index = 0; index < options.length; ++index)
                        {
                            if (options[index].selected)
                            {
                                selection.push(options[index].text);
                                return;
                            }
                        }
                    })();
                }

                if (selection.length > 0)
                {
                    return selection.join(' - ');
                }

                return variables.displayName || '';
            }());

        /**
         * Sends an Ajax request with the actual date to update the schedule
         * @param {boolean} [updateOthers=false]
         */
        this.requestUpdate = function (updateOthers) {
            ajaxUrl = ajaxUrl.replace(/(date=)\d{4}-\d{2}-\d{2}/, '$1' + getDateFieldString());
            ajaxRequest.open('GET', ajaxUrl, true);

            ajaxRequest.onreadystatechange = function () {
                if (ajaxRequest.readyState === 4 && ajaxRequest.status === 200)
                {
                    /**
                     * @param {Object} response
                     * @param {Date} response.pastDate
                     * @param {Date} response.futureDate
                     */
                    const response = JSON.parse(ajaxRequest.responseText);
                    events = response;
                    table.update(response);
                    that.popUp();

                    if (id === getSelectedScheduleID())
                    {
                        if (response.pastDate || response.futureDate)
                        {
                            openNextDateQuestion(response);
                        }
                        else if (response.pastDate === null && response.futureDate === null)
                        {
                            noEvents.style.display = 'block';
                        }
                    }

                    // Updates other schedule tables after this one, because of dependencies like 'occupied' events
                    if (updateOthers)
                    {
                        scheduleObjects.schedules.forEach(function (schedule) {
                            if (schedule.getId() !== id)
                            {
                                schedule.updateTable();
                            }
                        });
                    }
                }
            };

            ajaxRequest.send();
        };

        /**
         * Updates table with already given events, e.g. for changing time grids
         */
        this.updateTable = function () {
            table.update();
            that.popUp();
        };

        /**
         * Creates a pop-up like div with a copy of schedule table, which is movable by user
         * @param {boolean} [create] - create new pop-up element
         */
        this.popUp = function (create) {
            let cancelBtn, floatDiv = document.getElementById(id + '-pop-up'), titleElement;

            if (floatDiv)
            {
                floatDiv.removeChild(floatDiv.lastChild);
                jQuery(table.getTableElement()).clone(true).appendTo(jQuery(floatDiv));
            }
            else if (create)
            {
                floatDiv = document.createElement('div');
                floatDiv.id = id + '-pop-up';
                floatDiv.className = 'pop-up schedule-table';
                floatDiv.style.zIndex = getHighestZIndexForClass('.pop-up.schedule-table');
                floatDiv.draggable = true;
                /**
                 * @param {Event} event
                 * @param {DataTransfer|Object} event.dataTransfer gives data to the drop event
                 */
                floatDiv.addEventListener('dragstart', function (event) {
                    const data = {'id': event.target.id, 'x': event.pageX, 'y': event.pageY};

                    // Only 'text' for IE
                    event.dataTransfer.setData('text', JSON.stringify(data));
                    event.dropEffect = 'move';
                });
                floatDiv.addEventListener('click', function () {
                    this.style.zIndex = getHighestZIndexForClass('.pop-up.schedule-table');
                });

                cancelBtn = document.createElement('button');
                cancelBtn.className = 'icon-cancel';
                cancelBtn.addEventListener('click', function () {
                    this.parentElement.style.display = 'none';
                });
                floatDiv.appendChild(cancelBtn);

                titleElement = document.createElement('h3');
                titleElement.innerHTML = title;
                floatDiv.appendChild(titleElement);

                document.getElementsByClassName('organizer')[0].appendChild(floatDiv);
                jQuery(table.getTableElement()).clone(true).appendTo(jQuery(floatDiv));
            }

            if (create)
            {
                floatDiv.style.display = 'block';
            }
        };

        /**
         * Getter for id of schedule
         * @returns {string}
         */
        this.getId = function () {
            return id;
        };

        /**
         * Getter for resource of schedule
         * @returns {string}
         */
        this.getResource = function () {
            return resource;
        };

        /**
         * Getter for the IDs of the resource
         * @returns {string}
         */
        this.getResourceIDs = function () {
            return resourceIDs;
        };

        /**
         * Getter for title of schedule
         * @returns {string}
         */
        this.getTitle = function () {
            return title;
        };

        /**
         * Getter for the ScheduleTable related with this schedule
         * @returns {ScheduleTable}
         */
        this.getTable = function () {
            return table;
        };

        /**
         * Constructor-like function
         */
        (function () {
            table = new ScheduleTable(that);
            that.requestUpdate();
            scheduleObjects.addSchedule(that);
            addScheduleToSelection(that);
        })();
    }

    /**
     * Class for the HTMLTableElement of a schedule
     * @param {Schedule} schedule
     */
    function ScheduleTable(schedule)
    {
        const table = document.createElement('table'), SPEAKER = 4, SUPERVISOR = 3, TEACHER = 1, TUTOR = 2,
            isUserSchedule = schedule.getId() === 'user',
            weekend = 7;
        let defaultGrid = null,
            instanceElements = [],
            instances = {},
            /**
             * @param {number} timeGrid.endDay - 1 for monday etc.
             * @param {number} timeGrid.startDay - 2 for tuesday etc.
             */
            timeGrid = getSelectedTimeGrid(),
            timeGridID = getSelectedValues('grid'),
            useDefaultGrid = true,
            visibleDay = getDateFieldsDateObject().getDay();

        /**
         * Method to a add a button to the item output.
         *
         * @param item the display item
         * @param instance the instance to display
         * @param action the action which the button should perform
         */
        function addButton(item, instance, action)
        {
            const container = document.createElement('div'),
                button = document.createElement('button'),
                questionButton = document.createElement('button');

            button.className = action == 'save' ? 'icon-plus' : 'icon-delete';
            button.addEventListener('click', function () {
                handleEvent(item.dataset.instanceID, variables.BLOCK_MODE, true);
            });
            questionButton.className = 'icon-question';
            questionButton.addEventListener('click', function () {
                instanceMenu.getMenu(item, instance, action);
            });
            container.className = action == 'save' ? 'add-instance' : 'delete-instance';
            container.appendChild(button);
            container.appendChild(questionButton);
            item.appendChild(container);
        }

        /**
         * Adds DOM-elements pertaining to the comment to the item
         *
         * @param item the div in which the instance item will be displayed
         * @param object instance the instance data
         */
        function addComment(item, instance)
        {
            if (instance.comment)
            {
                const commentDiv = document.createElement('div');
                commentDiv.innerHTML = instance.comment;
                commentDiv.className = 'comment-container';
                item.appendChild(commentDiv);
            }
        }

        /**
         * Adds an eventListener to the given element, which triggers a eventRequest with further params
         * @param {HTMLElement} element
         * @param {string} resource
         * @param {string|int} id
         * @param {string} title
         */
        function addInternalLinkEvent(element, resource, id, title)
        {
            element.addEventListener('click', function () {
                sendEventRequest(resource, id, title);
            });
        }

        /**
         * Adds DOM-elements with subject name and eventListener directing to subject item
         * @param item the div in which the instance item will be displayed
         * @param object instance the instance data
         */
        function addIdentifiers(item, instance)
        {
            const indentifiersDiv = document.createElement('div'),
                subjectItemLink = function () {
                    window.open(variables.subjectItemBase.replace(/&id=\d+/, '&id=' + instance.subjectID), '_blank');
                };

            let nameElement, numberElement;

            if (instance.subjectID)
            {
                nameElement = document.createElement('a');
                nameElement.addEventListener('click', subjectItemLink);
            }
            else
            {
                nameElement = document.createElement('span');
            }

            nameElement.className = 'name';
            nameElement.innerHTML = instance.name;
            nameElement.innerHTML += instance.methodCode ? ' - ' + instance.methodCode : '';

            // Append whitespace to slashes for better word break
            nameElement.innerHTML = nameElement.innerHTML.replace(/(\S)\/(\S)/g, '$1 / $2');
            indentifiersDiv.appendChild(nameElement);

            if (instance.subjectNo)
            {
                if (instance.subjectID)
                {
                    numberElement = document.createElement('a');
                    numberElement.addEventListener('click', subjectItemLink);
                }
                else
                {
                    numberElement = document.createElement('span');
                }

                numberElement.className = 'module';
                numberElement.innerHTML = instance.subjectNo;
                indentifiersDiv.appendChild(numberElement);
            }

            item.appendChild(indentifiersDiv);
        }

        /**
         * Adds context menu to given instance item.
         *
         * @param {HTMLElement} item - the html element which will display the instance
         * @param {Object} instance - the instance to be displayed
         */
        function addMenu(item, instance)
        {
            if (variables.userID)
            {
                item.addEventListener('contextmenu', function (item) {
                    if (!item.classList.contains('instance-removed'))
                    {
                        item.preventDefault();
                        instanceMenu.getMenu(item, instance, 'save');
                    }

                    if (item.classList.contains('added'))
                    {
                        item.preventDefault();
                        instanceMenu.getMenu(item, instance, 'delete');
                    }
                });
            }
        }

        /**
         * Adds the persons and dependent resources of a role collection to the instance item.
         * @param container the container used for resource output
         * @param collection the aggregated persons of the given role for the instance
         */
        function addSortedResources(container, collection)
        {
            if (Object.keys(collection).length)
            {
                const keys = Object.keys(collection).sort();
                let key;

                for (key in keys)
                {
                    container.appendChild(collection[keys[key]]);
                }
            }
        }

        /**
         * Adds DOM-elements pertaining to groups, persons and rooms to the item.
         *
         * @param item the div in which the instance item will be displayed
         * @param object instance the instance data
         */
        function addResources(item, instance)
        {
            const resource = schedule.getResource(),
                updateItem = !(item.classList.contains('instance-new') || item.classList.contains('instance-removed'));
            let addGroups = false,
                aggregateGroups = true,
                aggregateRooms = true,
                initial = true,
                group,
                groupsDiv,
                groupID,
                groupIDs,
                groupKey,
                groupName,
                groups = {},
                groupStatus,
                person,
                personDiv,
                personID,
                personIDs,
                personKey,
                personName,
                personSpan,
                personStatus,
                addRooms = false,
                room,
                roomsDiv,
                roomID,
                roomIDs,
                roomKey,
                roomName,
                rooms = {},
                roomStatus,
                speakers = [],
                supervisors = [],
                teachers = [],
                tutors = [];

            if (resource === 'group')
            {
                addGroupResources(item, instance);
            }

            /*filterResources(instance);

            // Prefilter prototype properties
            personIDs = Object.keys(instance.resources);
            for (personKey in personIDs)
            {
                personID = personIDs[personKey];

                // Irrelevant person
                if (resource === 'person' && !resourceIDs.includes(personID))
                {
                    delete instance.resources.personID;
                    continue;
                }

                person = instance.resources[personID];

                if (person.groups)
                {
                    groupIDs = Object.keys(person.groups);
                    for (groupKey in groupIDs)
                    {
                        groupID = groupIDs[groupKey];

                        // Irrelevant group
                        if (resource === 'group' && !resourceIDs.includes(groupID))
                        {
                            delete instance.resources[personID].groups[groupID];
                            continue;
                        }

                        group = person.groups[groupID];
                        groupName = group.fullName;
                        groupStatus = group.status;

                        if (resource === 'group')
                        {
                            if (groupStatus && updateItem)
                            {
                                item.classList.add('instance-' + groupStatus);
                            }
                        }
                        else
                        {
                            addSubordinateResource(groups, groupID, group.code, groupName, groupStatus, 'group');
                            addGroups = true;
                        }
                    }
                }

                // No relevant groups
                if (resource === 'group' && !Object.keys(instance.resources[personID].groups).length)
                {
                    delete instance.resources.personID;
                    continue;
                }

                personDiv = document.createElement('div');
                personDiv.className = 'persons';
                personName = person.person;
                personStatus = person.status || '';

                if (resource === 'person')
                {
                    if (personStatus && updateItem)
                    {
                        item.classList.add('instance-' + personStatus);
                    }
                }
                else
                {
                    personSpan = document.createElement('span');
                    personSpan.classList.add('person');

                    if (personStatus)
                    {
                        personSpan.classList.add(personStatus);
                    }

                    personSpan.innerHTML = personCount > 1 ? person.role + ': ' + personName : personName;
                    personDiv.appendChild(personSpan);
                }

                if (addGroups)
                {
                    groupsDiv = document.createElement('div');
                    addSortedResources(groupsDiv, groups);
                    personDiv.appendChild(groupsDiv);
                }

                if (person.rooms)
                {
                    roomIDs = Object.keys(person.rooms);
                    for (roomKey in roomIDs)
                    {
                        roomID = roomIDs[roomKey];
                        room = person.rooms[roomID];
                        roomName = room.room;
                        roomStatus = room.status;

                        if (resource === 'room')
                        {
                            if (roomStatus && updateItem)
                            {
                                item.classList.add('instance-' + roomStatus);
                            }
                        }
                        else
                        {
                            addSubordinateResource(rooms, roomID, roomName, roomName, roomStatus, 'room');
                            addRooms = true;
                        }
                    }
                }
                else
                {
                    item.classList.add('in-planning');
                }

                if (addRooms)
                {
                    roomsDiv = document.createElement('div');
                    addSortedResources(roomsDiv, rooms);
                    personDiv.appendChild(roomsDiv);
                }

                if (person.roleID == SPEAKER)
                {
                    speakers[personName] = personDiv;
                }
                else if (person.roleID == SUPERVISOR)
                {
                    supervisors[personName] = personDiv;
                }
                else if (person.roleID == TEACHER)
                {
                    teachers[personName] = personDiv;
                }
                else if (person.roleID == TUTOR)
                {
                    tutors[personName] = personDiv;
                }

                initial = false;
            }

            addSortedResources(item, speakers);
            addSortedResources(item, teachers);
            addSortedResources(item, tutors);
            addSortedResources(item, supervisors);*/
        }

        function addGroupResources(item, instance)
        {
            const personIDs = Object.keys(instance.resources),
                resourceIDs = schedule.getResourceIDs(),
                updateItem = !(item.classList.contains('instance-new') || item.classList.contains('instance-removed'));
            let aggregateRooms = true, count,
                groupIDs, groupKey, groupID, groupStatus,
                initial = true, initialRooms = true, instanceGroupStatus, instanceRooms = {},
                person, personDiv, personDivs, personKey, personID, personName, persons = [], personSpan,
                personRelevant, personStatus,
                roleDiv, roleID, roleIDs, roleKey, roleName, rolePersons = [],
                roomID, roomIDs, roomKey, roomName, roomNames, rooms, roomsDiv, roomStatus;

            for (personKey in personIDs)
            {
                personID = personIDs[personKey];
                person = instance.resources[personID];
                if (!person.groups)
                {
                    // Person is not associated with any groups and is therefore irrelevant.
                    continue;
                }

                personRelevant = false;
                groupIDs = Object.keys(person.groups);
                for (groupKey in groupIDs)
                {
                    groupID = groupIDs[groupKey];
                    if (!resourceIDs.includes(groupID))
                    {
                        continue;
                    }

                    personRelevant = true;
                    groupStatus = person.groups[groupID].status;

                    // Determines whether all person <-> group associations have the same status.
                    if (initial)
                    {
                        instanceGroupStatus = groupStatus;
                        initial = false;
                    }
                    else if (instanceGroupStatus !== false && instanceGroupStatus !== groupStatus)
                    {
                        instanceGroupStatus = false;
                    }
                }

                // Person is not associated with the group sought.
                if (!personRelevant)
                {
                    continue;
                }

                // Relevance has been determined.
                personDiv = document.createElement('div');
                personDiv.className = 'persons';
                personName = person.person;
                roleID = person.roleID;
                personSpan = document.createElement('span');
                personSpan.classList.add('person');
                personStatus = groupStatus === 'removed' || person.status === 'removed' ? 'removed' : person.status || '';
                personSpan.innerHTML = personName;

                if (personStatus)
                {
                    personSpan.classList.add(personStatus);
                }

                if (!rolePersons[roleID])
                {
                    rolePersons[roleID] = {}
                }
                rolePersons[roleID][personName] = personSpan;
                personDiv.appendChild(personSpan);

                // If the person has become irrelevant, so too have their associated rooms.
                if (personStatus !== 'removed' && person.rooms)
                {
                    rooms = {};
                    roomIDs = Object.keys(person.rooms);
                    for (roomKey in roomIDs)
                    {
                        roomID = roomIDs[roomKey];
                        roomName = person.rooms[roomID].room;
                        roomStatus = person.rooms[roomID].status;
                        addSubordinateResource(rooms, roomID, roomName, roomName, roomStatus, 'room');
                    }

                    if (Object.keys(rooms))
                    {
                        roomsDiv = document.createElement('div');
                        addSortedResources(roomsDiv, rooms);
                        personDiv.appendChild(roomsDiv);
                    }

                    if (initialRooms)
                    {
                        instanceRooms = rooms;
                        initialRooms = false;
                    }
                    else if (instanceRooms !== false && JSON.stringify(instanceRooms) !== JSON.stringify(rooms))
                    {
                        aggregateRooms = false;
                        instanceRooms = false;
                    }
                }
            }

            if (aggregateRooms)
            {
                personDiv = document.createElement('div');
                personDiv.className = 'persons';
                roleIDs = Object.keys(rolePersons);
                for (roleKey in roleIDs)
                {
                    roleID = roleIDs[roleKey];
                    count = Object.keys(rolePersons[roleID]).length;
                    roleDiv = document.createElement('div');
                    if (roleID === SPEAKER)
                    {
                        roleName = count > 1 ?
                            Joomla.JText._('ORGANIZER_SPEAKERS') : Joomla.JText._('ORGANIZER_SPEAKER');
                        roleDiv.classList.add('speakers');
                    }
                    else if (roleID === TUTOR)
                    {
                        roleName = count > 1 ?
                            Joomla.JText._('ORGANIZER_TUTORS') : Joomla.JText._('ORGANIZER_TUTOR');
                        roleDiv.classList.add('tutors');
                    }
                    else if (roleID === SUPERVISOR)
                    {
                        roleName = count > 1 ?
                            Joomla.JText._('ORGANIZER_SUPERVISORS') : Joomla.JText._('ORGANIZER_SUPERVISOR');
                        roleDiv.classList.add('supervisors');
                    }
                    else
                    {
                        roleName = count > 1 ?
                            Joomla.JText._('ORGANIZER_TEACHERS') : Joomla.JText._('ORGANIZER_TEACHER');
                        roleDiv.classList.add('teachers');
                    }
                    roleDiv.innerHTML = roleName + ': ' + Object.keys(rolePersons[roleID]).sort().join(', ');
                    personDiv.appendChild(roleDiv);
                }

                if (Object.keys(instanceRooms).length)
                {
                    roomsDiv = document.createElement('div');
                    roomsDiv.classList.add('rooms');
                    roomNames = Object.keys(instanceRooms).sort();
                    for (roomName in roomNames)
                    {
                        roomsDiv.append(instanceRooms[roomNames[roomName]]);
                        roomsDiv.innerHTML += ', ';
                    }

                    // Remove the last separators.
                    roomsDiv.innerHTML = roomsDiv.innerHTML.substr(0, roomsDiv.innerHTML.length - 2);
                    personDiv.appendChild(roomsDiv);
                }
                item.appendChild(personDiv);
            }
        }

        /**
         * Adds the display of subordinate resources to a collection for later sorting before appending them to the
         * container.
         *
         * @param collection the collection to which the subordinate resource displays will be added
         * @param id the id of the subordinate resource
         * @param name the name of the subordinate resource
         * @param status the status of the subordinate resource
         * @param type the type of subordinate resource
         */
        function addSubordinateResource(collection, id, name, fullName, status, type)
        {
            const link = document.createElement('a'),
                span = document.createElement('span');
            link.innerHTML = name;

            addInternalLinkEvent(link, type, id, fullName);

            span.classList.add(type);
            span.appendChild(link);

            if (status)
            {
                span.classList.add(status);
            }

            collection[name] = span;
        }

        /**
         * Creates an event which means a div element filled by data
         * @param {Object} instance - event data
         * @param {string} instance.ccmID - id of calendar configuration mapping
         * @param {string} instance.calendarDelta - changes of calendar date/time
         * @param {string} instance.comment - some comment for the event
         * @param {string} instance.eventDelta - changes of events
         * @param {string} instance.method - method (e.g. lecture) of a event
         * @param {boolean} instance.regType - 0 for fifo, 1 for manual
         * @param {Object} instance.subjects - subjects of a event
         * @param {string} instance.startTime - instance start time
         * @param {string} instance.endTime - instance end time
         * @param {boolean} [ownTime=false] - show own time
         * @returns {HTMLDivElement[]|boolean} HTMLDivElements in an array or false in case of wrong input
         */
        function createItem(instance, ownTime)
        {
            const instanceGroups = [],
                instanceRooms = [],
                item = document.createElement('div'),
                openScheduleID = schedule.getId(),
                resource = schedule.getResource(),
                speakers = [],
                supervisors = [],
                teachers = [],
                tutors = [];
            let propertyName;

            item.classList.add('instance');
            item.dataset.instanceID = instance.instanceID;
            item.dataset.registrationType = instance.registrationType;

            if (instance.unitStatus || instance.status)
            {
                if (instance.unitStatus === 'removed' || instance.status === 'removed')
                {
                    item.classList.add('instance-removed');
                }
                else if (instance.unitStatus === 'new' || instance.status === 'new')
                {
                    item.classList.add('instance-removed');
                }
            }

            // Show deviant time first
            ownTime = typeof ownTime === 'undefined' ? false : ownTime;
            if (ownTime && instance.startTime && instance.endTime)
            {
                const ownTimeSpan = document.createElement('span');
                ownTimeSpan.className = 'own-time';
                ownTimeSpan.innerHTML = instance.startTime + ' - ' + instance.endTime;
                item.appendChild(ownTimeSpan);
            }

            addIdentifiers(item, instance);
            addComment(item, instance);
            addResources(item, instance);

            if (instance.full)
            {
                item.classList.add('full');
            }

            addMenu(item, instance);
            if (variables.userID)
            {
                addMenu(item, instance);
                addButton(item, instance, 'delete');
                addButton(item, instance, 'save');

                // Makes delete button visible only
                /*if (isUserSchedule || isSavedByUser(item))
                {
                    item.classList.add('added');
                }*/
            }
            else
            {
                item.classList.add('no-saving');
            }

            return item;
        }

        /**
         * Creates a table DOM-element with an input and label for selecting it and a caption with the given title.
         * It gets appended to the scheduleWrapper.
         */
        function createScheduleElement()
        {
            const input = document.createElement('input'),
                div = document.createElement('div'),
                body = document.createElement('tbody'),
                initGrid = timeGrid.hasOwnProperty('periods') ? timeGrid : JSON.parse(variables.defaultGrid),
                rowCount = Object.keys(initGrid.periods).length;
            let firstDay, rowIndex;

            // Create input field for selecting this schedule
            input.className = 'schedule-input';
            input.type = 'radio';
            input.setAttribute('id', schedule.getId() + '-input');
            input.setAttribute('name', 'schedules');
            input.setAttribute('checked', 'checked');
            scheduleWrapper.appendChild(input);

            // Create a new schedule table
            div.setAttribute('id', schedule.getId() + '-schedule');
            div.setAttribute('class', 'schedule-table');
            div.appendChild(table);
            scheduleWrapper.appendChild(div);

            table.appendChild(body);

            for (rowIndex = 0; rowIndex < rowCount; ++rowIndex)
            {
                // Filled with rows and cells (with -1 for last position)
                const row = body.insertRow(-1);

                for (firstDay = 0; firstDay < weekend; ++firstDay)
                {
                    row.insertCell(-1);
                }
            }
        }

        /**
         * Sets default gridID of schedule, select it in grid form field and returns it
         * @return {Object}
         */
        function getDefaultGrid()
        {
            if (!defaultGrid)
            {
                // Function returns first found gridID
                const defaultGridID = (function () {
                    let day, event, time;

                    for (day in instances)
                    {
                        if (!instances.hasOwnProperty(day))
                        {
                            continue;
                        }

                        for (time in instances[day])
                        {
                            if (!instances[day].hasOwnProperty(time))
                            {
                                continue;
                            }

                            for (event in instances[day][time])
                            {
                                if (instances[day][time].hasOwnProperty(event) && instances[day][time][event].gridID)
                                {
                                    return instances[day][time][event].gridID;
                                }
                            }
                        }
                    }
                })();

                if (defaultGridID)
                {
                    setGrid(defaultGridID);
                    defaultGrid = JSON.parse(variables.grids[defaultGridID].grid);
                }
            }

            return defaultGrid || timeGrid;
        }

        /**
         *
         * @param string iEndTime the instance end time
         * @param string iStartTime the instance start time
         * @return {[]} the blocks relevant to the display of the instance
         */
        function getRelevantBlocks(iEndTime, iStartTime)
        {
            const blocks = [];
            let block, blockIndex, contains, ends, equals, overlaps, starts;

            for (blockIndex in timeGrid.periods)
            {
                // This is being resolved to table rows which start numbering with 0.
                block = timeGrid.periods[blockIndex];

                // Irrelevant and the search is over
                if (block.startTime > iEndTime)
                {
                    break;
                }

                // Irrelevant and nothing was found yet
                if (block.endTime < iStartTime)
                {
                    continue;
                }

                contains = block.endTime >= iEndTime && block.startTime <= iStartTime;
                ends = block.endTime > iEndTime && block.startTime < iEndTime;
                equals = block.endTime === iEndTime && block.startTime === iStartTime;
                if (contains || ends || equals)
                {
                    blocks.push(blockIndex - 1);
                    break;
                }

                overlaps = block.endTime < iEndTime && block.startTime > iStartTime;
                starts = block.endTime > iStartTime && block.startTime < iStartTime;
                if (overlaps || starts)
                {
                    blocks.push(blockIndex - 1);
                    continue;
                }
            }

            return blocks;
        }

        /** Filters resources irrelevant to the filter criteria
         *
         * @param instance the instance whose resources are to be filtered
         */
        function filterResources(instance)
        {

            const resource = schedule.getResource(),
                resourceIDs = schedule.getResourceIDs();

            let groupID, person, personID, roomID;

            for (personID in instance.resources)
            {
                for (groupID in person.groups)
                {
                    if (!person.groups.hasOwnProperty(groupID))
                    {
                        continue;
                    }
                }

                for (roomID in person.rooms)
                {
                    if (!person.rooms.hasOwnProperty(roomID))
                    {
                        continue;
                    }

                    if (resource === 'room' && !resourceIDs.includes(roomID))
                    {
                        delete instance.resources.personID.rooms.roomID;
                        continue;
                    }
                }

                if (resource === 'room' && !Object.keys(instance.resources[personID].rooms).length)
                {
                    delete instance.resources.personID;
                    continue;
                }
            }
        }

        /**
         * Inserts instances into a schedule
         * @param {Object} instances
         */
        function insertInstances(instances)
        {
            const instanceIndexes = Object.keys(instances),
                rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let block, blockIndex, cell, iDoW, iEndTime, index, instance,
                iStartTime, item, relevantBlocks, showOwnTime;

            /*
            // this needs to go somewhere where every cell is iterated
            if (variables.userID && !isUserSchedule && isOccupiedByUserEvent(rowIndex, dayNo))
            {
                jQuery(cell).addClass('occupied');
            }*/

            for (index in instanceIndexes)
            {
                instance = instances[instanceIndexes[index]];
                iDoW = new Date(instance.date).getDay();

                if (timeGrid.periods)
                {
                    iEndTime = instance.endTime.replace(':', '');
                    iStartTime = instance.startTime.replace(':', '');
                    relevantBlocks = getRelevantBlocks(iEndTime, iStartTime);
                    showOwnTime = relevantBlocks.length > 1;
                }
                else
                {
                    // There is no column displaying block times so the index is one less.
                    iDoW = iDoW - 1
                    relevantBlocks = [0];
                    showOwnTime = true;
                }

                item = createItem(instance, showOwnTime);

                for (blockIndex in relevantBlocks)
                {
                    block = relevantBlocks[blockIndex];
                    cell = rows[block].getElementsByTagName('td')[iDoW];
                    cell.appendChild(item);
                    cell.classList.add('instances');
                }
            }
        }

        /**
         * Insert table head and side cells with time data
         */
        function insertTableHead()
        {
            const headerDate = getDateFieldsDateObject(), tr = table.createTHead().insertRow(0);
            let headIndex;

            // Set date to monday
            headerDate.setDate(headerDate.getDate() - headerDate.getDay());

            for (headIndex = 0; headIndex < weekend; ++headIndex)
            {
                const th = document.createElement('th');

                th.innerHTML = (headIndex === 0) ? Joomla.JText._('ORGANIZER_TIME') : weekdays[headIndex - 1] +
                    ' (' + headerDate.getPresentationFormat(true) + ')';

                if (headIndex === visibleDay)
                {
                    th.classList.add('activeColumn');
                }
                tr.appendChild(th);
                headerDate.setDate(headerDate.getDate() + 1);
            }
        }

        /**
         * Checks for a block if the user has events in it already
         * @param {number} rowIndex
         * @param {number} colIndex
         * @return {boolean}
         */
        function isOccupiedByUserEvent(rowIndex, colIndex)
        {
            const userScheduleTable = scheduleObjects.userSchedule.getTable().getTableElement(),
                rows = userScheduleTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr'),
                row = rows[rowIndex],
                cell = row ? row.getElementsByTagName('td')[colIndex] : false;

            return cell && cell.classList.contains('events');
        }

        /**
         * Checks for a event if it is already saved in the users schedule
         * @param {HTMLElement} event
         * @return {boolean}
         */
        function isSavedByUser(event)
        {
            let eventIndex, events;

            if (!event || !scheduleObjects.userSchedule)
            {
                return false;
            }

            events = scheduleObjects.userSchedule.getTable().getEvents();
            for (eventIndex = 0; eventIndex < events.length; ++eventIndex)
            {
                if (events[eventIndex].dataset.ccmID === event.dataset.ccmID)
                {
                    return true;
                }
            }

            return false;
        }

        /**
         * Removes all events and rebuild table structure on time grid
         */
        function resetTable()
        {
            const newBody = document.createElement('tbody'), oldBody = table.getElementsByTagName('tbody')[0],
                columnCount = timeGrid.endDay, rowCount = timeGrid.periods ? Object.keys(timeGrid.periods).length : 1;
            let columnIndex, rowIndex;

            // Build table on time grid filled with rows and cells (with -1 for last position)
            for (rowIndex = 0; rowIndex < rowCount; ++rowIndex)
            {
                const row = newBody.insertRow(-1), type = timeGrid.periods && timeGrid.periods[rowIndex + 1].type;

                if (type)
                {
                    row.classList.add(type);
                }

                for (columnIndex = 0; columnIndex <= columnCount; ++columnIndex)
                {
                    row.insertCell(-1);
                }
            }

            table.replaceChild(newBody, oldBody);
        }

        /**
         * Sets only the selected day column visible for mobile devices
         */
        function setActiveColumn()
        {
            const rows = table.getElementsByTagName('tr');
            let thIndex, ths, tdIndex, tds, row;

            for (row = 0; row < rows.length; ++row)
            {
                ths = rows[row].getElementsByTagName('th');
                for (thIndex = 1; thIndex < ths.length; ++thIndex)
                {
                    if (thIndex === visibleDay)
                    {
                        ths[thIndex].classList.add('activeColumn');
                    }
                    else
                    {
                        ths[thIndex].classList.remove('activeColumn');
                    }
                }

                tds = rows[row].getElementsByTagName('td');
                for (tdIndex = 1; tdIndex < tds.length; ++tdIndex)
                {
                    if (tdIndex === visibleDay)
                    {
                        tds[tdIndex].classList.add('activeColumn');
                    }
                    else
                    {
                        tds[tdIndex].classList.remove('activeColumn');
                    }
                }
            }
        }

        /**
         * Here the table head changes to the grids specified weekdays with start day and end day
         */
        function setGridDays()
        {
            const headItems = table.getElementsByTagName('thead')[0].getElementsByTagName('th'),
                headerDate = getDateFieldsDateObject(),
                day = headerDate.getDay();
            let currentDay = parseInt(timeGrid.startDay), thElement;

            // Set date to monday of the coming week
            if (day === 0)
            {
                headerDate.setDate(headerDate.getDate() + 1);
            }
            else
            {
                // Sunday is 0, so we add a one for monday
                headerDate.setDate(headerDate.getDate() - day + 1);
            }

            // Show TIME header on the left side ?
            headItems[0].style.display = timeGrid.hasOwnProperty('periods') ? '' : 'none';

            // Fill tHead with days of week
            for (thElement = 1; thElement < headItems.length; ++thElement)
            {
                if (thElement === currentDay && currentDay <= timeGrid.endDay)
                {
                    headItems[thElement].innerHTML = weekdays[currentDay - 1] +
                        ' (' + headerDate.getPresentationFormat(true) + ')';
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
         * Sets the chosen times of the grid in the schedules tables
         */
        function setGridTime()
        {
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let endTime, period = 1, row, startTime;

            // No periods -> no times
            if (!timeGrid.periods)
            {
                return;
            }

            for (row = 0; row < rows.length; ++row)
            {
                const gap = endTime ? timeGrid.periods[period].startTime - timeGrid.periods[period - 1].endTime : 0,
                    timeCell = rows[row].getElementsByTagName('td')[0];

                // Indicate bigger breaks between blocks (more than 30 minutes)
                if (gap >= 100)
                {
                    rows[row - 1].classList.add('long-break-after');
                }

                startTime = timeGrid.periods[period].startTime.replace(/(\d{2})(\d{2})/, '$1:$2');
                endTime = timeGrid.periods[period].endTime.replace(/(\d{2})(\d{2})/, '$1:$2');
                timeCell.innerHTML = startTime + '<br> - <br>' + endTime;

                ++period;
            }
        }

        /**
         * Getter for HTMLDivElements which represents the events of this table
         * @returns {Array}
         */
        this.getEvents = function () {
            return instanceElements;
        };

        /**
         * Getter for current time grid ID of this table
         * @returns {int}
         */
        this.getGridID = function () {
            return timeGridID;
        };

        /**
         * Getter for the HTMLTableElement
         * @returns {Element}
         */
        this.getTableElement = function () {
            return table;
        };

        /**
         * Removes the HTMLTableElement itself and the related HTMLInputElement
         */
        this.remove = function () {
            // input element
            scheduleWrapper.removeChild(document.getElementById(schedule.getId() + '-input'));
            // table element
            scheduleWrapper.removeChild(document.getElementById(schedule.getId() + '-schedule'));
        };

        /**
         * Updates the table with the actual selected time grid and given events.
         * @param {Object} [newInstances] - all events of a schedule
         */
        this.update = function (newInstances) {
            instances = newInstances || instances;
            visibleDay = getDateFieldsDateObject().getDay();

            if (useDefaultGrid)
            {
                timeGrid = getDefaultGrid();
            }

            resetTable();
            setGridDays();
            setGridTime();

            if (!(instances.pastDate || instances.futureDate))
            {
                insertInstances(instances);
            }

            if (variables.isMobile)
            {
                setActiveColumn();
            }
        };

        /**
         * Change grid to selected grid instead of the default one
         */
        this.updateGrid = function () {
            useDefaultGrid = false;
            timeGrid = getSelectedTimeGrid();
            timeGridID = getSelectedValues('grid');
            this.update();
        };

        /**
         * Constructor-like function to build the HTMLTableElement
         */
        (function () {
            createScheduleElement();
            insertTableHead();
            setGridTime();
        }());
    }

    /**
     * Creates an event menu for saving and deleting an event, which opens by right clicking on it
     */
    function InstanceMenu()
    {
        const instanceMenu = document.getElementsByClassName('instance-menu')[0],
            deleteInstanceMode = document.getElementById('delete-mode-instance'),
            deleteMenu = instanceMenu.getElementsByClassName('delete')[0],
            deletePeriodMode = document.getElementById('delete-mode-period'),
            deleteSemesterMode = document.getElementById('delete-mode-semester'),
            descriptionSpan = instanceMenu.getElementsByClassName('description')[0],
            moduleSpan = instanceMenu.getElementsByClassName('module')[0],
            personsDiv = instanceMenu.getElementsByClassName('persons')[0],
            groupsDiv = instanceMenu.getElementsByClassName('groups')[0],
            roomsDiv = instanceMenu.getElementsByClassName('rooms')[0],
            saveInstanceMode = document.getElementById('save-mode-instance'),
            saveMenu = instanceMenu.getElementsByClassName('save')[0],
            savePeriodMode = document.getElementById('save-mode-period'),
            saveSemesterMode = document.getElementById('save-mode-semester'),
            subjectSpan = instanceMenu.getElementsByClassName('subject')[0];
        let currentInstanceID = '0';

        /**
         * Resets HTMLDivElements
         */
        function resetElements()
        {
            removeChildren(personsDiv);
            removeChildren(roomsDiv);
            removeChildren(groupsDiv);
        }

        /**
         * Inserts data of active event
         * @param {Object} instance - event data like subject name, persons, locations...
         * @param {string} instance.name - name of event subject
         * @param {string} instance.subjectNo - number of subject
         * @param {Object} instance.groups - all groups
         * @param {Object} instance.groupDeltas - changed groups
         * @param {Object} instance.rooms - all rooms
         * @param {Object} instance.roomDeltas - changed rooms
         * @param {Object} instance.persons - all persons
         * @param {Object} instance.personDeltas - changed persons
         */
        function setInstanceData(instance)
        {
            let groupID, roomID, personID;

            resetElements();
            subjectSpan.innerHTML = instance.name;

            if (instance.subjectNo === '')
            {
                moduleSpan.style.display = 'none';
            }
            else
            {
                moduleSpan.style.display = 'inline-block';
                moduleSpan.innerHTML = instance.subjectNo;
            }

            descriptionSpan.innerHTML = instanceMenu.parentNode.getElementsByClassName('comment-container')[0] ?
                instanceMenu.parentNode.getElementsByClassName('comment-container')[0].innerText : '';

            for (personID in instance.persons)
            {
                if (instance.persons.hasOwnProperty(personID) && instance.personDeltas[personID] !== 'removed')
                {
                    const personSpan = document.createElement('span');
                    personSpan.innerHTML = instance.persons[personID];
                    personsDiv.appendChild(personSpan);
                }
            }

            for (roomID in instance.rooms)
            {
                if (instance.rooms.hasOwnProperty(roomID) && instance.roomDeltas[roomID] !== 'removed')
                {
                    const roomSpan = document.createElement('span');
                    roomSpan.innerHTML = instance.rooms[roomID];
                    roomsDiv.appendChild(roomSpan);
                }
            }

            for (groupID in instance.groups)
            {
                if (instance.groups.hasOwnProperty(groupID))
                {
                    const groupSpan = document.createElement('span');
                    groupSpan.innerHTML = instance.groups[groupID].untisID;
                    groupsDiv.appendChild(groupSpan);
                }
            }
        }

        /**
         * Adds eventListeners to html elements
         */
        (function () {
            saveSemesterMode.addEventListener('click', function () {
                handleEvent(currentInstanceID, variables.SEMESTER_MODE, true);
                saveMenu.parentNode.style.display = 'none';
            });
            savePeriodMode.addEventListener('click', function () {
                handleEvent(currentInstanceID, variables.BLOCK_MODE, true);
                saveMenu.parentNode.style.display = 'none';
            });
            saveInstanceMode.addEventListener('click', function () {
                handleEvent(currentInstanceID, variables.INSTANCE_MODE, true);
                saveMenu.parentNode.style.display = 'none';
            });
            deleteSemesterMode.addEventListener('click', function () {
                handleEvent(currentInstanceID, variables.SEMESTER_MODE, false);
                deleteMenu.parentNode.style.display = 'none';
            });
            deletePeriodMode.addEventListener('click', function () {
                handleEvent(currentInstanceID, variables.BLOCK_MODE, false);
                deleteMenu.parentNode.style.display = 'none';
            });
            deleteInstanceMode.addEventListener('click', function () {
                handleEvent(currentInstanceID, variables.INSTANCE_MODE, false);
                deleteMenu.parentNode.style.display = 'none';
            });
        }());

        this.getMenu = function (item, instance, action) {
            currentInstanceID = item.dataset.instanceID;
            if (action == 'save')
            {
                saveMenu.style.display = 'block';
                deleteMenu.style.display = 'none';
            }
            else
            {
                saveMenu.style.display = 'none';
                deleteMenu.style.display = 'block';
            }
            instanceMenu.style.display = 'block';
            item.appendChild(instanceMenu);
            setInstanceData(instance);
        };
    }

    /**
     * Container for all schedule objects
     * Including functions to get the right schedule by id or response url.
     */
    function Schedules()
    {
        /**
         * @type {Schedule[]}
         */
        this.schedules = [];

        /**
         * The one and only schedule owned by the currently logged in user
         * @type {Schedule}
         */
        this.userSchedule = null;

        /**
         * Adds a schedule to the list and set it into session storage
         * @param {Schedule} schedule
         */
        this.addSchedule = function (schedule) {
            let sessionSchedules = JSON.parse(window.sessionStorage.getItem('schedules'));
            const scheduleObject = {
                title: schedule.getTitle(),
                resource: schedule.getResource(),
                IDs: schedule.getResourceIDs()
            };

            // No user schedules in session. When someone is logged in, the schedule gets loaded anyway.
            if (schedule.getId() !== 'user')
            {
                if (!sessionSchedules)
                {
                    sessionSchedules = {};
                }

                sessionSchedules[schedule.getId()] = scheduleObject;
                window.sessionStorage.setItem('schedules', JSON.stringify(sessionSchedules));
            }
            else
            {
                this.userSchedule = schedule;
            }

            this.schedules.push(schedule);
        };

        /**
         * Removes a schedule and all related HTML elements
         * @param {Schedule} schedule - the object or id of schedule
         */
        this.removeSchedule = function (schedule) {
            const sessionSchedules = JSON.parse(window.sessionStorage.getItem('schedules'));

            delete sessionSchedules[schedule.getId()];
            window.sessionStorage.setItem('schedules', JSON.stringify(sessionSchedules));

            if (schedule.getTable())
            {
                schedule.getTable().remove();
                this.schedules.splice(this.schedules.indexOf(schedule), 1);
            }
        };

        /**
         * Gets the Schedule object which belongs to the given id
         * @param {string} id
         * @return {Schedule|boolean}
         */
        this.getScheduleById = function (id) {
            let scheduleIndex;

            for (scheduleIndex = 0; scheduleIndex < this.schedules.length; ++scheduleIndex)
            {
                if (this.schedules[scheduleIndex].getId() === id)
                {
                    return this.schedules[scheduleIndex];
                }
            }

            return false;
        };

        /**
         * Updates user schedule and refresh other schedules
         */
        this.updateUserSchedule = function () {
            this.userSchedule.requestUpdate(true);
        };

        /**
         * Returns the currently selected schedule
         * @returns {Schedule|boolean}
         */
        this.getActiveSchedule = function () {
            return this.getScheduleById(getSelectedScheduleID());
        }
    }

    /**
     * Form of selecting a schedule
     */
    function ScheduleForm()
    {
        const fieldsToShow = {},
            config = {
                'name': '',
                'values': []
            },
            fields = {
                'category': document.getElementById('category'),
                'department': document.getElementById('department'),
                'group': document.getElementById('group'),
                'person': document.getElementById('person'),
                'room': document.getElementById('room'),
                'roomtype': document.getElementById('roomtype'),
                'type': document.getElementById('type')
            },
            placeholder = {
                'category': Joomla.JText._('ORGANIZER_SELECT_CATEGORY'),
                'group': Joomla.JText._('ORGANIZER_SELECT_GROUP'),
                'person': Joomla.JText._('ORGANIZER_SELECT_PERSON'),
                'room': Joomla.JText._('ORGANIZER_SELECT_ROOM'),
                'roomtype': Joomla.JText._('ORGANIZER_SELECT_ROOMTYPE')
            },
            wrappers = {
                'category': document.getElementById('category-input'),
                'department': document.getElementById('department-input'),
                'group': document.getElementById('group-input'),
                'person': document.getElementById('person-input'),
                'room': document.getElementById('room-input'),
                'roomtype': document.getElementById('roomtype-input'),
                'type': document.getElementById('type-input')
            },
            sessionFields = JSON.parse(window.sessionStorage.getItem('scheduleForm')) || {},
            sessionDepartments = JSON.parse(window.sessionStorage.getItem('scheduleDepartment')) || {};

        /**
         * Get ajax url for selecting a form field
         * @param {HTMLSelectElement} field - selected field
         * @param {string} [values] - optional values to specify task
         */
        function getOptionsUrl(field, values)
        {
            const previousField = document.querySelector('[data-next=' + field.id + ']');
            let resource, url = getAjaxUrl();

            resource = field.dataset.input === 'static' ? jQuery(field).val() : field.id;
            url += '&view=' + resource.replace(/([A-Z])/g, '_$&').toLowerCase() + '_options';

            if (previousField)
            {
                url += '&' + previousField.id + 'IDs=' + (values ? values : getSelectedValues(previousField.id));
            }

            return url;
        }

        /**
         * Set an option with placeholder text after removing all options
         * @param {HTMLSelectElement} field
         */
        function setPlaceholder(field)
        {
            removeChildren(field);

            if (placeholder[field.id])
            {
                const option = document.createElement('option');

                option.setAttribute('value', '');
                option.setAttribute('disabled', 'disabled');
                option.setAttribute('selected', 'selected');
                option.innerHTML = placeholder[field.id];
                field.appendChild(option);
            }
        }

        /**
         * Add an event handler for all schedule form selection elements
         * @param {HTMLSelectElement} field
         */
        function addSelectEventListener(field)
        {
            // No Chosen-library available
            if (variables.isMobile)
            {
                fields[field.id].addEventListener('change', handleField);
            }
            else
            {
                jQuery('#' + field.id).chosen().change(
                    function () {
                        handleField(field.id);
                    }
                );
                // Chosen.js events fires multiple times, so we use native EventListener
                wrappers[field.id].querySelector('.chzn-results').addEventListener('click',
                    function () {
                        handleField(field.id);
                    }
                );
            }
        }

        /**
         * Show given field and its 'parents' (like roomtype to room) and hide rest
         * @param {string} name - id of field
         */
        function showField(name)
        {
            const selectedValue = fields[name].dataset.input === 'static' ? getSelectedValues(name) : '';
            let id;

            // Go through all ScheduleForm fields and show/hide them, when they are related to given field
            for (id in fields)
            {
                if (fields.hasOwnProperty(id))
                {
                    const field = fields[id];

                    if (fieldsToShow[id.toLowerCase()] && (
                        // Show as param given field
                        id === name ||
                        // Show static fields like category
                        field.dataset.input === 'static' ||
                        // Show previous field
                        field.dataset.next === name ||
                        // Show static fields and their selection
                        id === selectedValue
                    )
                    )
                    {
                        jQuery(wrappers[id]).show();
                    }
                    else
                    {
                        jQuery(wrappers[id]).hide();
                    }
                }
            }
        }

        /**
         * Set session data to save form state, provided that the field does not fire a new schedule (events)
         * @param {HTMLSelectElement} field - will be set into session storage
         */
        function setSession(field)
        {
            if (field.dataset.next !== 'event')
            {
                const session = {};

                session.name = field.id;
                session.value = getSelectedValues(field.id);

                if (field.id === 'department')
                {
                    sessionDepartments[variables.menuID] = session;
                    window.sessionStorage.setItem('scheduleDepartment', JSON.stringify(sessionDepartments));
                }
                else
                {
                    sessionFields[variables.menuID] = session;
                    window.sessionStorage.setItem('scheduleForm', JSON.stringify(sessionFields));
                }
            }
        }

        /**
         * Loads field which is set in session
         * @return boolean - success indicator
         */
        function loadSession()
        {
            const department = sessionDepartments[variables.menuID], session = sessionFields[variables.menuID];

            if (department)
            {
                jQuery('#department').val(department.value).chosen('destroy').chosen();
            }

            if (session)
            {
                // Prevent overwriting configuration values
                if (session.name === config.name)
                {
                    sendFormRequest(session.name, session.value, config.values);
                }
                else if (fields[session.name].dataset.input === 'static')
                {
                    jQuery(fields[session.name]).val(session.value).chosen('destroy').chosen();

                    // Update static selected field like program
                    if (fields[session.value])
                    {
                        sendFormRequest(session.value);
                    }
                }
                else
                {
                    sendFormRequest(session.name, session.value);
                }

                return true;
            }

            return false;
        }

        /**
         * Sends Ajax request for given field and handles the incoming values
         * @param {string} name - name/id of field to fill with options
         * @param {string} [selectedValue] - value to select immediately
         * @param {string[]} [onlyValues] - array with values that are designated to add
         */
        function sendFormRequest(name, selectedValue, onlyValues)
        {
            const ajax = new XMLHttpRequest(), field = fields[name];

            ajax.open('GET', getOptionsUrl(field, selectedValue), true);
            ajax.onreadystatechange = function () {
                let option, optionCount, response, key;

                if (ajax.readyState === 4 && ajax.status === 200)
                {
                    response = JSON.parse(ajax.responseText);
                    optionCount = onlyValues ? onlyValues.length : Object.keys(response).length;
                    setPlaceholder(field);

                    for (key in response)
                    {
                        if (response.hasOwnProperty(key) && (!onlyValues || onlyValues.includes(response[key])))
                        {
                            option = document.createElement('option');
                            option.value = response[key].value;
                            option.innerHTML = response[key].text;
                            option.selected = (optionCount === 1 || option.value === selectedValue);
                            field.appendChild(option);
                        }
                    }

                    if (optionCount === 1 || selectedValue)
                    {
                        if (field.dataset.next === 'event')
                        {
                            sendEventRequest(field.id);
                        }
                        else
                        {
                            sendFormRequest(field.dataset.next);
                        }
                    }

                    jQuery(field).chosen('destroy').chosen();
                    // Because of Chosen update, options loose their eventListener after changes
                    addSelectEventListener(field);
                    showField(field.id);
                }
            };
            ajax.send();
        }

        /**
         * Request for events or the next field will be send, depending on fields data-set
         * @param {Event|string} field - the triggered event or id of field
         */
        function handleField(field)
        {
            const element = fields[field] || fields[field.target.id];

            // Do not target placeholder
            if (element.selectedIndex !== 0)
            {
                if (element.dataset.next === 'event')
                {
                    sendEventRequest(element.id);
                    return;
                }

                if (element.dataset.input === 'static')
                {
                    sendFormRequest(getSelectedValues(element.id));
                }
                else
                {
                    sendFormRequest(element.dataset.next);
                }

                setSession(element);
            }
        }

        /**
         * Forms first field gets handled, inclusive setting session params and displaying fields
         */
        function handleFirstField()
        {
            let firstField, name;

            // Subjects do not have a select field, so the necessary information is simulated here
            if (config.name === 'subject' || config.name === 'event')
            {
                firstField = {'id': config.name, 'dataset': {'next': 'event'}};
            }
            else
            {
                firstField = fields[config.name] || fields.type;
            }

            name = firstField.id;

            if (config.name)
            {
                if (firstField.dataset.next === 'event')
                {
                    config.values.forEach(function (value) {
                        const ajaxRequest = new XMLHttpRequest(),
                            titleURL = getAjaxUrl() + '&view=' + name + 's&task=getName&id=' + value;

                        // Gets title per Ajax for each schedule before it gets created
                        ajaxRequest.open('GET', titleURL, true);
                        ajaxRequest.onreadystatechange = function () {
                            let title;

                            if (ajaxRequest.readyState === 4 && ajaxRequest.status === 200)
                            {
                                title = ajaxRequest.responseText.replace(/"+/g, '');
                                sendEventRequest(name, value, title);
                            }
                        };
                        ajaxRequest.send();
                    });

                    disableTabs('tab-selected-schedule');
                }
                else
                {
                    sendFormRequest(name, '', config.values);
                    disableTabs();
                }
            }
            else
            {
                // First field is static (type)
                sendFormRequest(getSelectedValues(name));
                disableTabs();
            }
        }

        /**
         * Reloads the next visible and flexible field of the form (for updating departmentID)
         */
        function updateNextVisibleField()
        {
            const toUpdate = {'next': '', 'event': ''};
            let name;

            for (name in fields)
            {
                if (fields.hasOwnProperty(name))
                {
                    const field = fields[name],
                        wrapper = jQuery(wrappers[name]);

                    if (wrapper.css('display') !== 'none' && field.dataset.input !== 'static')
                    {
                        if (field.dataset.next === 'event')
                        {
                            toUpdate.event = field.id;
                        }
                        else
                        {
                            toUpdate.next = field.id;
                        }
                    }
                }
            }

            // Non event-fields have priority, but in some cases there are only event-fields (person)
            sendFormRequest(toUpdate.next || toUpdate.event);
        }

        /**
         * Collects configuration from backend and url params
         */
        function collectConfig()
        {
            let valueIndex, variable;

            for (variable in variables)
            {
                if (!variables.hasOwnProperty(variable))
                {
                    continue;
                }

                const idMatch = /^(\w+)*IDs$/.exec(variable);
                let fieldID, showMatch = /^show(\w+)s$/i.exec(variable);

                if (idMatch)
                {
                    const values = variables[variable];
                    config.name = idMatch[1].toLowerCase();

                    // Convert values to strings, to compare them later with Ajax response
                    if (jQuery.isArray(values))
                    {
                        for (valueIndex = 0; valueIndex < values.length; ++valueIndex)
                        {
                            config.values.push('' + values[valueIndex]);
                        }
                    }
                    else
                    {
                        config.values.push('' + values);
                    }
                }

                if (showMatch)
                {
                    fieldID = showMatch[1].toLowerCase();
                    fieldID = fieldID === 'categorie' ? 'category' : fieldID;
                    fieldsToShow[fieldID] = variables[variable];
                }
            }

            // No configured field => type has to be visible
            fieldsToShow.type = !config.name;
        }

        /**
         * Build the form by collecting backend configurations and handles the first field of schedule form
         */
        (function () {
            collectConfig();

            if (!loadSession())
            {
                handleFirstField();
            }

            jQuery('#type').chosen().change(function () {
                sendFormRequest(getSelectedValues(this.id));
                setSession(this);
            });
            jQuery('#department').chosen().change(function () {
                updateNextVisibleField();
                setSession(this);
            });
        })();
    }

    /**
     * Get the general ajax url
     * @returns {string}
     */
    function getAjaxUrl()
    {
        return variables.ajaxBase + variables.departmentID || getSelectedValues('department') || 0;
    }

    /**
     * Loads schedules from session storage
     */
    function loadSessionSchedules()
    {
        const schedules = JSON.parse(window.sessionStorage.getItem('schedules'));

        if (schedules && Object.keys(schedules).length > 0)
        {
            let id;

            for (id in schedules)
            {
                if (schedules.hasOwnProperty(id) && !scheduleObjects.getScheduleById(id))
                {
                    new Schedule(schedules[id].resource, schedules[id].IDs, schedules[id].title);
                }
            }

            if (scheduleObjects.schedules.length > 0)
            {
                switchToScheduleListTab();
            }

            showSchedule(jQuery('#selected-schedules').find('.selected-schedule').last().attr('id'));
        }
    }

    /**
     * Selects the given grid id in grid form field
     * @param {string} id - grid id to set as selected
     */
    function setGrid(id)
    {
        jQuery('#grid').val(id).chosen('destroy').chosen();
    }

    /**
     * Starts an Ajax request to get events for the selected resource
     * @param {string} resource
     * @param {string} [id]
     * @param {string} [title]
     */
    function sendEventRequest(resource, id, title)
    {
        const IDs = id || getSelectedValues(resource, '-');
        let schedule = scheduleObjects.getScheduleById(resource + IDs);

        if (schedule)
        {
            schedule.requestUpdate();
        }
        else
        {
            schedule = new Schedule(resource, IDs, title);
        }

        switchToScheduleListTab();
        showSchedule(schedule.getId());
    }

    /**
     * Opens div which asks user to jump to the last or next available date
     * @param {Object} dates - dates to jump to next event in schedule
     * @param {string} dates.futureDate - next date in the future
     * @param {string} dates.pastDate - next date in the past
     */
    function openNextDateQuestion(dates)
    {
        const pastDate = dates.pastDate ? new Date(dates.pastDate) : null,
            futureDate = dates.futureDate ? new Date(dates.futureDate) : null;

        nextDateSelection.style.display = 'block';

        if (pastDate)
        {
            pastDateButton.innerHTML = pastDateButton.innerHTML.replace(datePattern, pastDate.getPresentationFormat());
            pastDateButton.dataset.date = dates.pastDate;
            jQuery(pastDateButton).show();
        }
        else
        {
            jQuery(pastDateButton).hide();
        }

        if (futureDate)
        {
            futureDateButton.innerHTML = futureDateButton.innerHTML.replace(datePattern, futureDate.getPresentationFormat());
            futureDateButton.dataset.date = dates.futureDate;
            jQuery(futureDateButton).show();
        }
        else
        {
            jQuery(futureDateButton).hide();
        }
    }

    /**
     * Save event in users personal schedule
     * Choose between events of whole semester (1),
     * just this daytime (2)
     * or only the selected instance of a event (3).
     * @param {string} instanceID - calendar_configuration_map ID
     * @param {number} [taskNumber=1]
     * @param {boolean} [save=true] - indicate to save or to delete the event
     */
    function handleEvent(instanceID, taskNumber, save)
    {
        const saving = (typeof save === 'undefined') ? true : save;
        let actionURL = getAjaxUrl();

        actionURL += '&view=schedules&mode=' + (taskNumber || '1') + '&instanceID=' + instanceID + '&task=';
        actionURL += saving ? 'saveUserLesson' : 'deleteUserLesson'
        ajaxSave.open('GET', actionURL, true);
        ajaxSave.onreadystatechange = function () {
            if (ajaxSave.readyState === 4 && ajaxSave.status === 200)
            {
                const handledEvents = JSON.parse(ajaxSave.responseText);

                scheduleObjects.schedules.forEach(function (schedule) {
                    const eventElements = schedule.getTable().getEvents();
                    let fifo = false, eventIndex, manual = false;

                    for (eventIndex = 0; eventIndex < eventElements.length; ++eventIndex)
                    {
                        const eventElement = eventElements[eventIndex];

                        if (handledEvents.includes(eventElement.dataset.ccmID))
                        {
                            if (saving)
                            {
                                eventElement.classList.add('added');

                                if (eventElement.dataset.regType === 0)
                                {
                                    fifo = true;
                                }
                                else if (eventElement.dataset.regType === 1)
                                {
                                    manual = true;
                                }
                            }
                            else
                            {
                                eventElement.classList.remove('added');

                                // So the element is invisible immediately and not as late as updating this schedule
                                if (schedule === scheduleObjects.userSchedule)
                                {
                                    jQuery(eventElement).hide();
                                }
                            }
                        }
                    }

                    if (fifo)
                    {
                        regFifo.style.display = 'block';
                    }
                    else if (manual)
                    {
                        regManual.style.display = 'block';
                    }
                });

                scheduleObjects.updateUserSchedule();
            }
        };
        ajaxSave.send();
    }

    /**
     * Create a new entry in the drop-down field for selecting a schedule
     * @param {Schedule} schedule
     */
    function addScheduleToSelection(schedule)
    {
        const selectedItem = document.createElement('div'),
            selectedTitle = document.createElement('button'),
            showButton = document.createElement('button');

        selectedItem.id = schedule.getId();
        selectedItem.className = 'selected-schedule';
        jQuery('#selected-schedules').append(selectedItem);

        selectedTitle.className = 'title';
        selectedTitle.innerHTML = schedule.getTitle();
        selectedTitle.addEventListener('click', function () {
            showSchedule(schedule.getId());
        });
        selectedItem.appendChild(selectedTitle);

        showButton.className = 'show-schedule';
        showButton.innerHTML = '<span class="icon-eye-close"></span>';
        showButton.addEventListener('click', function () {
            showSchedule(schedule.getId());
        });
        selectedItem.appendChild(showButton);

        if (!variables.isMobile)
        {
            const popUpButton = document.createElement('button');

            popUpButton.className = 'pop-up-schedule';
            popUpButton.innerHTML = '<span class="icon-move"></span>';
            popUpButton.addEventListener('click', function () {
                schedule.popUp(true);
            });
            selectedItem.appendChild(popUpButton);
        }

        if (schedule.getId() !== 'user')
        {
            const removeButton = document.createElement('button');

            removeButton.className = 'remove-schedule';
            removeButton.innerHTML = '<span class="icon-remove"></span>';
            removeButton.addEventListener('click', function () {
                removeScheduleFromSelection(selectedItem, schedule);
            });
            selectedItem.appendChild(removeButton);
        }

        showSchedule(schedule.getId());
    }

    /**
     * Shows schedule with given ID
     * @param {string} scheduleID
     */
    function showSchedule(scheduleID)
    {
        const scheduleElements = jQuery('.schedule-input'), schedule = scheduleObjects.getScheduleById(scheduleID);
        let schedulesIndex;

        for (schedulesIndex = 0; schedulesIndex < scheduleElements.length; ++schedulesIndex)
        {
            if (scheduleElements[schedulesIndex].id === scheduleID + '-input')
            {
                scheduleElements[schedulesIndex].checked = 'checked';
                jQuery('.selected-schedule').removeClass('shown');
                jQuery('#' + scheduleID).addClass('shown');

                // Set grid of schedule as selected in form field to make changing it easier (except default schedule)
                if (schedule)
                {
                    setGrid(scheduleObjects.getScheduleById(scheduleID).getTable().getGridID());
                }
            }
        }

        disableTabs();
    }

    /**
     * Gets ID of now selected schedule in #selected-schedules HTMLDivElement.
     * Returns false in case no schedule was found.
     * @returns {string|boolean}
     */
    function getSelectedScheduleID()
    {
        const selectedSchedule = document.getElementById('selected-schedules').getElementsByClassName('shown')[0];

        return selectedSchedule ? selectedSchedule.id : false;
    }

    /**
     * Remove an entry from the drop-down field for selecting a schedule
     * @param {HTMLElement} scheduleSelectionElement - remove this element
     * @param {Schedule} schedule - remove this object
     */
    function removeScheduleFromSelection(scheduleSelectionElement, schedule)
    {
        scheduleSelectionElement.parentNode.removeChild(scheduleSelectionElement);
        scheduleObjects.removeSchedule(schedule);

        if (scheduleObjects.schedules.length === 0)
        {
            showSchedule('default');
            switchToFormTab();
        }
        else
        {
            showSchedule(jQuery('#selected-schedules').find('.selected-schedule').last().attr('id'));
        }
    }

    /**
     * Removes all children elements of one given parent element
     * @param {HTMLElement} element - parent element
     */
    function removeChildren(element)
    {
        const children = element.children, maxIndex = children.length - 1;
        let index;

        for (index = maxIndex; index >= 0; --index)
        {
            element.removeChild(children[index]);
        }
    }

    /**
     * Gets the concatenated and selected values of one multiple form field
     * @param {string} fieldID
     * @param {string} [separator=","]
     * @returns {string|boolean}
     */
    function getSelectedValues(fieldID, separator)
    {
        const field = document.getElementById(fieldID),
            options = field ? field.options : undefined,
            result = [];

        if (field && field.selectedIndex > -1)
        {
            let index;

            for (index = 0; index < options.length; ++index)
            {
                if (options[index].selected)
                {
                    result.push(options[index].value);
                }
            }

            return result.join(separator || ',');
        }

        return false;
    }

    /**
     * Goes one day for- or backward in the schedules and takes the date out of the input field with 'date' as id
     * @param {boolean} increase - goes forward with true or backward with false
     * @param {string} [step="week"] - defines how big the step is as "day", "week" or "month"
     */
    function changeDate(increase, step)
    {
        const newDate = getDateFieldsDateObject(),
            stepString = step || 'week',
            stepInt = stepString === 'week' ? 7 : 1;

        if (increase)
        {
            if (step === 'month')
            {
                newDate.setMonth(newDate.getMonth() + stepInt);
            }
            else
            {
                newDate.setDate(newDate.getDate() + stepInt);
            }

            // Jump over sunday
            if (newDate.getDay() === 0)
            {
                newDate.setDate(newDate.getDate() + 1);
            }
        }
        else
        {
            // Decrease date
            if (step === 'month')
            {
                newDate.setMonth(newDate.getMonth() - stepInt);
            }
            else
            {
                newDate.setDate(newDate.getDate() - stepInt);
            }

            // Jump over sunday
            if (newDate.getDay() === 0)
            {
                newDate.setDate(newDate.getDate() - 1);
            }
        }

        app.dateField.value = newDate.getPresentationFormat();
        window.sessionStorage.setItem('scheduleDate', newDate.toJSON());
    }

    /**
     * Returns the current date field value as a string connected by minus.
     * @returns {string}
     */
    function getDateFieldString()
    {
        const date = getDateFieldsDateObject(), day = date.getDate(), month = date.getMonth() + 1;

        return date.getFullYear() + '-' + (month < 10 ? '0' + month : month) + '-' + (day < 10 ? '0' + day : day);
    }

    /**
     * Returns a Date object by the current date field value, parsing the configured date format
     * @returns {Date}
     */
    function getDateFieldsDateObject()
    {
        const matches = app.dateField.value.match(datePattern), firstChar = variables.dateFormat.charAt(0);

        if (matches)
        {
            // Year comes first
            if (firstChar === 'y' || firstChar === 'Y')
            {
                // 12:00:00 o'clock for timezone offset
                return new Date(parseInt(matches[1], 10), parseInt(matches[2] - 1, 10), parseInt(matches[3], 10), 12, 0, 0);
            }
            else
            {
                return new Date(parseInt(matches[3], 10), parseInt(matches[2] - 1, 10), parseInt(matches[1], 10), 12, 0, 0);
            }
        }

        return new Date();
    }

    /**
     * Returns the time grid which is selected in form input
     * @returns {Object}
     */
    function getSelectedTimeGrid()
    {
        return JSON.parse(variables.grids[getSelectedValues('grid')].grid);
    }

    /**
     * Change tab-behaviour of tabs in menu-bar, so all tabs can be closed
     * @param {Object} clickedTab - jQuery object of tab
     */
    function changeTabBehaviour(clickedTab)
    {
        if (clickedTab.parent('li').hasClass('active'))
        {
            clickedTab.parent('li').toggleClass('inactive', '');
            jQuery('#' + clickedTab.attr('data-id')).toggleClass('inactive', '');
        }
        else
        {
            jQuery('.tabs-tab').removeClass('inactive');
            jQuery('.tab-panel').removeClass('inactive');
        }
    }

    /**
     * Activates tab with a list of selected schedules
     */
    function switchToScheduleListTab()
    {
        const selectedSchedulesTab = jQuery('#tab-selected-schedules');

        if (!selectedSchedulesTab.parent('li').hasClass('disabled-tab'))
        {
            selectedSchedulesTab.parent('li').addClass('active');
            jQuery('#selected-schedules').addClass('active');
        }

        jQuery('#tab-schedule-form').parent('li').removeClass('active');
        jQuery('#schedule-form').removeClass('active');
    }

    /**
     * Activates tab with a form for selecting a new schedule
     */
    function switchToFormTab()
    {
        const formTab = jQuery('#tab-schedule-form');

        if (!formTab.parent('li').hasClass('disabled-tab'))
        {
            formTab.parent('li').addClass('active');
            jQuery('#schedule-form').addClass('active');
        }

        jQuery('#tab-selected-schedules').parent('li').removeClass('active');
        jQuery('#selected-schedules').removeClass('active');
    }

    /**
     * Change position of the date-input, depending of screen-width
     */
    function changePositionOfDateInput()
    {
        const mq = window.matchMedia('(max-width: 677px)');

        if (variables.isMobile)
        {
            jQuery('.date-input').insertAfter('.menu-bar');
            //jQuery('.check-input').insertAfter('.date-input');
        }

        mq.addListener(function () {
            if (mq.matches)
            {
                jQuery('.date-input').insertAfter('.menu-bar');
                //jQuery('.check-input').insertAfter('.date-input');
            }
            else
            {
                jQuery('.date-input').insertAfter(jQuery('.tabs-tab').eq(-2));
                //jQuery('.check-input').insertAfter('.date-input');
            }
        });
    }

    /**
     * Disable tabs, when only the default-schedule-table is shown
     * @param {string} [tabID] - optional to disable all tabs except this
     */
    function disableTabs(tabID)
    {
        const scheduleInput = jQuery('.schedule-input'),
            tabsToDisable = [
                jQuery('#tab-selected-schedules'),
                jQuery('#tab-time-selection'),
                jQuery('#tab-exports')
            ];
        let i;

        if (tabID)
        {
            const allTabs = jQuery('.tabs-toggle');

            for (i = 0; i < allTabs.length; ++i)
            {
                if (tabID !== allTabs[i].id)
                {
                    allTabs[i].dataset.toggle = '';
                    allTabs[i].parentElement.classList.add('disabled-tab');
                }
            }
        }
        else if (scheduleInput.length === 1 && scheduleInput.is('#default-input'))
        {
            // No schedule selected - disable all but schedule form
            for (i = 0; i < tabsToDisable.length; ++i)
            {
                tabsToDisable[i].attr('data-toggle', '');
                tabsToDisable[i].parent('li').addClass('disabled-tab');
            }
        }
        else
        {
            // Activates all tabs
            for (i = 0; i < tabsToDisable.length; ++i)
            {
                tabsToDisable[i].attr('data-toggle', 'tab');
                tabsToDisable[i].parent('li').removeClass('disabled-tab');
            }
        }
    }

    /**
     * EventHandler for moving schedule pop-ups over the page
     * @param {Event} event
     * @param {DataTransfer|Object} event.dataTransfer - drag data store
     */
    function handleDragOver(event)
    {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    }

    /**
     * EventHandler for dropping schedule pop-ups
     * @param {Event} event
     * @param {DataTransfer|Object} event.dataTransfer - drag data store
     */
    function handleDrops(event)
    {
        const data = JSON.parse(event.dataTransfer.getData('text')),
            element = document.getElementById(data.id),
            left = window.getComputedStyle(element).getPropertyValue('left'),
            top = window.getComputedStyle(element).getPropertyValue('top');
        let matchLeft, matchTop, oldLeft, oldTop;

        event.preventDefault();

        // Get the old style values without unit (e.g. "px")
        matchLeft = left.match(/^(-?\d+)\w*$/);
        oldLeft = matchLeft ? matchLeft[1] : 0;
        matchTop = top.match(/^(-?\d+)\w*$/);
        oldTop = matchTop ? matchTop[1] : 0;

        element.style.left = parseInt(oldLeft, 10) + parseInt(event.pageX - data.x, 10) + 'px';
        element.style.top = parseInt(oldTop, 10) + parseInt(event.pageY - data.y, 10) + 'px';

        // Last dragged schedule gets the highest z-index
        element.style.zIndex = getHighestZIndexForClass('.pop-up.schedule-table');
    }

    /**
     * Returns the highest z-index of the given class elements
     * @param {string} className
     * @returns {number}
     */
    function getHighestZIndexForClass(className)
    {
        const elements = document.querySelectorAll(className);
        let index, maxZIndex = 1;

        for (index = 0; index < elements.length; ++index)
        {
            const zIndex = parseInt(window.getComputedStyle(elements[index]).getPropertyValue('z-index'));

            maxZIndex = Math.max(zIndex, maxZIndex);
        }

        return ++maxZIndex;
    }

    /**
     * @public
     * @type {Element}
     */
    this.dateField = document.getElementById('date');

    /**
     * Sends an Ajax request to update all schedules or just the specified one.
     * @param {string} [id]
     */
    this.updateSchedule = function (id) {
        const schedule = scheduleObjects.getScheduleById(id);

        if (schedule)
        {
            schedule.requestUpdate();
        }
        else
        {
            scheduleObjects.schedules.forEach(function (schedule) {
                schedule.requestUpdate();
            });
        }
    };

    /**
     * Fired by select a time grid and adapt it to the active schedule table
     */
    this.changeGrid = function () {
        scheduleObjects.getActiveSchedule().getTable().updateGrid();
    };

    /**
     * The date field gets the selected date, schedules get updates and the selection-div-element is hidden again
     * @param {Event} event - Event that triggers this function
     */
    this.nextDateEventHandler = function (event) {
        const date = new Date(event.target.dataset.date);

        this.dateField.value = date.getPresentationFormat();
        window.sessionStorage.setItem('scheduleDate', date.toJSON());
        nextDateSelection.style.display = 'none';
        this.updateSchedule();
    };

    /**
     * Opens export window of selected schedule
     * @param {string} format
     */
    this.handleExport = function (format) {
        const exportSelection = jQuery('#export-selection'),
            schedule = getSelectedScheduleID(),
            formats = format.split('.');
        let url = variables.exportBase;

        url += '&format=' + formats[0];

        if (formats[0] === 'pdf')
        {
            url += '&gridID=' + variables.grids[getSelectedValues('grid')].id;
        }

        if (formats[1] !== undefined)
        {
            url += '&documentFormat=' + formats[1];
        }

        if (typeof variables.username !== 'undefined' && typeof variables.auth !== 'undefined')
        {
            url += '&username=' + variables.username + '&auth=' + variables.auth;
        }

        if (schedule === 'user')
        {
            url += '&myschedule=1';
        }
        else
        {
            const resourceID = schedule.match(/[0-9]+/);

            if (resourceID === null)
            {
                return;
            }

            if (schedule.search(/group/) === 0)
            {
                url += '&groupIDs=' + resourceID;
            }
            else if (schedule.search(/room/) === 0)
            {
                url += '&roomIDs=' + resourceID;
            }
            else if (schedule.search(/person/) === 0)
            {
                url += '&personIDs=' + resourceID;
            }
            else
            {
                return;
            }
        }

        if (formats[0] === 'ics')
        {
            window.prompt(Joomla.JText._('ORGANIZER_GENERATE_LINK'), url);
            exportSelection.val('placeholder');
            exportSelection.trigger('chosen:updated');
            return;
        }

        url += '&date=' + getDateFieldString();

        window.open(url);
        exportSelection.val('placeholder');
        exportSelection.trigger('chosen:updated');
    };

    /**
     * Getter for calendar
     * @return {Calendar}
     */
    this.getCalendar = function () {
        return calendar;
    };

    /**
     * Get date string in the components specified format.
     * @see http://stackoverflow.com/a/3067896/6355472
     * @param {boolean} [shortYear=false]
     * @returns {string}
     */
    Date.prototype.getPresentationFormat = function (shortYear) {
        const day = this.getDate(),
            dayLong = day < 10 ? '0' + day : day,
            // getMonth() is zero-based
            month = this.getMonth() + 1,
            monthLong = month < 10 ? '0' + month : month,
            yearLong = this.getFullYear(),
            year = yearLong.toString().substr(2, 2);
        let date = variables.dateFormat;

        // Insert day
        date = date.replace(/j/, day.toString());
        date = date.replace(/d/, dayLong);
        // Insert month
        date = date.replace(/n/, month.toString());
        date = date.replace(/m/, monthLong);

        // Insert year
        if (typeof shortYear === 'undefined' ? false : shortYear)
        {
            date = date.replace(/[yY]/, year.toString());
        }
        else
        {
            date = date.replace(/Y/, yearLong.toString());
            date = date.replace(/y/, year.toString());
        }

        return date;
    };

    /**
     * Very simple alternative for internet explorers missing method Array.includes
     */
    if (!Array.prototype.includes)
    {
        /**
         * @param {*} element
         * @returns {boolean}
         */
        Array.prototype.includes = function (element) {
            return this.indexOf(element) >= 0;
        };
    }

    /**
     * "Constructor"
     * Adds EventListener and initialise menus, schedules, tabs, calendar and form
     */
    (function () {
        const sessionDate = window.sessionStorage.getItem('scheduleDate'),
            date = sessionDate ? new Date(sessionDate) : new Date();
        let startX, startY;

        app.dateField.value = date.getPresentationFormat();
        calendar = new Calendar();
        instanceMenu = new InstanceMenu();
        scheduleObjects = new Schedules();
        form = new ScheduleForm();

        if (variables.userID && !scheduleObjects.getScheduleById('user'))
        {
            new Schedule('user');
            switchToScheduleListTab();
        }

        changePositionOfDateInput();
        loadSessionSchedules();

        /**
         * Swipe touch event handler changing the shown day and date
         * @see http://www.javascriptkit.com/javatutors/touchevents.shtml
         * @see http://www.html5rocks.com/de/mobile/touch/
         */
        scheduleWrapper.addEventListener('touchstart', function (event) {
            const touch = event.changedTouches[0];

            startX = parseInt(touch.pageX, 10);
            startY = parseInt(touch.pageY, 10);
        }, {passive: true}); // To say the browser, that we not 'prevent default' (and suppress warnings)
        scheduleWrapper.addEventListener('touchend', function (event) {
            const touch = event.changedTouches[0],
                distX = parseInt(touch.pageX, 10) - startX,
                distY = parseInt(touch.pageY, 10) - startY,
                minDist = 50;

            if (Math.abs(distX) > Math.abs(distY))
            {
                if (distX < -(minDist))
                {
                    event.stopPropagation();
                    changeDate(true, variables.isMobile ? 'day' : 'week');
                    app.updateSchedule();
                }
                else if (distX > minDist)
                {
                    event.stopPropagation();
                    changeDate(false, variables.isMobile ? 'day' : 'week');
                    app.updateSchedule();
                }
            }
        });
        jQuery('#schedules').chosen().change(function () {
            const scheduleInput = document.getElementById(jQuery('#schedules').val());

            // To show the schedule after this input field (by css)
            scheduleInput.checked = 'checked';
        });

        // Change Tab-Behaviour of menu-bar, so all tabs can be closed
        jQuery('.tabs-toggle').on('click', function (event) {
            changeTabBehaviour(jQuery(this));

            //prevent loading of tabs-url:
            event.preventDefault();
        });

        // Drag'n'drop effect for schedule pop-ups
        document.getElementById('main').addEventListener('drop', handleDrops);
        document.getElementById('main').addEventListener('dragover', handleDragOver);
    })();

    /**
     * Context-menu-popup, calendar-popup and message-popup will be closed when clicking outside this
     */
    jQuery(document).mouseup(function (e) {
        const calendarPopup = jQuery('#calendar'),
            messagePopup = jQuery('.message.pop-up'),
            popup = jQuery('.instance-menu');

        if (!popup.is(e.target) && popup.has(e.target).length === 0)
        {
            popup.hide(0);
        }

        if (!messagePopup.is(e.target) && messagePopup.has(e.target).length === 0)
        {
            messagePopup.hide(0);
        }

        if (jQuery('.controls').css('display') !== 'none')
        {
            if (calendar.isVisible() && !calendarPopup.is(e.target) && calendarPopup.has(e.target).length === 0)
            {
                calendar.hideCalendar();
            }
        }
    });
};
