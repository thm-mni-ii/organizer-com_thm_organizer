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

var ajaxSave = null, ajaxSelection = null, Calendar, calendar, dateField, datePattern,
	isMobile, nextDateSelection, jumpToNextDate = true, LessonMenu, Schedule, Schedules, scheduleObjects, ScheduleTable,
	scheduleRequests = [], scheduleWrapper, weekdays;

/**
 * Calendar class for a date input field with HTMLTableElement as calendar.
 * By choosing a date, schedules are updated.
 */
Calendar = function ()
{
	var that = this; // Helper for inner functions
	this.calendarDiv = document.getElementById('calendar');
	this.calendarIsVisible = false;
	this.activeDate = new Date();
	this.month = document.getElementById('display-month');
	this.months = [
		text.JANUARY,
		text.FEBRUARY,
		text.MARCH,
		text.APRIL,
		text.MAY,
		text.JUNE,
		text.JULY,
		text.AUGUST,
		text.SEPTEMBER,
		text.OCTOBER,
		text.NOVEMBER,
		text.DECEMBER
	];
	this.table = document.getElementById('calendar-table');
	this.year = document.getElementById('display-year');

	/**
	 * This function is called immediately after creating a new Calendar.
	 * Sets eventListeners for HTML-elements and variables.
	 */
	this.init = function ()
	{
		this.activeDate = getDateFieldsDateObject();
		this.showControls();

		window.dateField.addEventListener('change', that.setUpCalendar);
		document.getElementById('calendar-icon').addEventListener('click', that.showCalendar);
		document.getElementById('calendar-next-month').addEventListener('click', that.changeCalendarMonth);
		document.getElementById('calendar-previous-month').addEventListener('click', function ()
		{
			that.changeCalendarMonth(false);
		});
		document.getElementById('next-week').addEventListener('click', that.changeSelectedDate);
		document.getElementById('previous-week').addEventListener('click', function ()
		{
			that.changeSelectedDate(false, true);
		});
		document.getElementById('next-month').addEventListener('click', function ()
		{
			that.changeSelectedDate(true, false);
		});
		document.getElementById('previous-month').addEventListener('click', function ()
		{
			that.changeSelectedDate(false, false);
		});
		document.getElementById('today').addEventListener('click', function ()
		{
			that.insertDate();
			that.setUpCalendar();
		});
	};

	/**
	 * Increase or decrease displayed month in calendar table.
	 *
	 * @param increaseMonth boolean default = true
	 */
	this.changeCalendarMonth = function (increaseMonth)
	{
		var increase = (typeof increaseMonth === 'undefined') ? true : increaseMonth;

		if (increase)
		{
			// day 1 for preventing get Feb 31
			that.activeDate.setMonth(that.activeDate.getMonth() + 1, 1);
		}
		else
		{
			that.activeDate.setMonth(that.activeDate.getMonth() - 1, 1);
		}

		that.setUpCalendar();
	};

	/**
	 * Increase or decrease in steps of days or months in the current date in date field
	 *
	 * @param increase boolean default = true
	 * @param week boolean default = true
	 */
	this.changeSelectedDate = function (increase, week)
	{
		changeDate(increase, week);
		updateSchedule();

		if (this.calendarIsVisible)
		{
			this.setUpCalendar();
		}
	};

	/**
	 * Display calendar controls like changing to previous month.
	 */
	this.showControls = function ()
	{
		var dateControls = document.getElementsByClassName('date-input')[0].getElementsByClassName('controls');

		for (var controlIndex = 0; controlIndex < dateControls.length; ++controlIndex)
		{
			dateControls[controlIndex].style.display = 'inline';
		}
	};

	/**
	 * Hides the calendar.
	 */
	this.hideCalendar = function ()
	{
		this.calendarDiv.style.visibility = 'hidden';
		this.calendarIsVisible = false;
	};

	/**
	 * The date chosen in the calendar table gets set in the date field
	 *
	 * @param date Date object
	 */
	this.insertDate = function (date)
	{
		// that, because this = eventListener
		that.activeDate = (typeof date === 'undefined') ? new Date() : date;
		window.dateField.value = that.activeDate.getPresentationFormat();

		that.hideCalendar();
		updateSchedule();
	};

	/**
	 * Builds the calendar (table), depending on a given date or the date field.
	 */
	this.setUpCalendar = function ()
	{
		that.resetTable();
		that.setUpCalendarHead();
		that.fillCalendar();
	};

	/**
	 * Hides or shows the calendar, depending on its previous status.
	 */
	this.showCalendar = function ()
	{
		that.calendarDiv.style.visibility = (that.calendarIsVisible) ? 'hidden' : 'visible';
		that.calendarIsVisible = !that.calendarIsVisible;

		if (that.calendarIsVisible)
		{
			that.setUpCalendar();
		}
	};

	/**
	 * Displays month and year in calendar table head
	 */
	this.setUpCalendarHead = function ()
	{
		this.month.innerHTML = this.months[this.activeDate.getMonth()];
		this.year.innerHTML = this.activeDate.getFullYear().toString();
	};

	/**
	 * Deletes the rows of the calendar table for refreshing.
	 */
	this.resetTable = function ()
	{
		var tableBody = this.table.getElementsByTagName('tbody')[0],
			rowLength = this.table.getElementsByTagName('tr').length;

		for (var rowIndex = 0; rowIndex < rowLength; ++rowIndex)
		{
			// '-1' represents the last row
			tableBody.deleteRow(-1);
		}
	};

	/**
	 * Calendar table gets filled with days of the month, chosen by the given date
	 */
	this.fillCalendar = function ()
	{
		// Inspired by https://wiki.selfhtml.org/wiki/JavaScript/Anwendung_und_Praxis/Monatskalender
		var tableBody = this.table.getElementsByTagName('tbody')[0],
			rows, rowIndex, row, cell, cellIndex, months30days = [4, 6, 9, 11], days = 31, day = 1,
			generalMonth = new Date(this.activeDate.getFullYear(), this.activeDate.getMonth(), 1),
			weekdayStart = generalMonth.getDay() == 0 ? 7 : generalMonth.getDay(),
			month = this.activeDate.getMonth() + 1,
			year = this.activeDate.getFullYear();

		// Compute count of days
		if (months30days.indexOf(month) != -1)
		{
			days = 30;
		}

		if (month == 2)
		{
			days = (year % 4 == 0) ? 29 : 28;
		}

		// Append rows to table
		rows = Math.min(Math.ceil((days + generalMonth.getDay() - 1) / 7), 6);

		for (rowIndex = 0; rowIndex <= rows; rowIndex++)
		{
			row = tableBody.insertRow(rowIndex);
			for (cellIndex = 0; cellIndex <= 6; cellIndex++)
			{
				cell = row.insertCell(cellIndex);
				if ((rowIndex == 0 && cellIndex < weekdayStart - 1) || day > days)
				{
					cell.innerHTML = ' ';
				}
				else
				{
					// Closure function needed, to give individual params to eventListeners inside of a for-loop
					(function (day)
					{
						var button = document.createElement('button');
						button.type = 'button';
						button.className = 'day';
						button.innerHTML = day.toString();
						button.addEventListener('click', function ()
						{
							that.insertDate(new Date(year, month - 1, day))
						}, false);
						cell.appendChild(button);
					}(day));

					day++;
				}
			}
		}
	};
};

/**
 * Schedule 'class' for saving params and update the scheduleTable
 *
 * @param resource string name of (e.g. form) resource
 * @param IDs string makes together with resource the schedule ID
 * @param optionalTitle string optional title for directly linked schedules (e.g. teacher or room)
 */
Schedule = function (resource, IDs, optionalTitle)
{
	this.ajaxRequest = new XMLHttpRequest();
	this.id = resource === "user" ? resource
		: IDs ? resource + IDs
		: resource + getSelectedValues(resource, "-");
	this.lessons = [];
	this.programID = resource === "pool" ? getSelectedValues("program") : null;
	this.resource = resource; // For ScheduleTable usage
	this.scheduleTable = new ScheduleTable(this);
	this.title = "";
	this.task = "";

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
		this.ajaxRequest.open("GET", variables.ajaxbase + this.task, true);
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
	this.lessonElements = []; // HTMLDivElements
	this.schedule = schedule;
	this.table = document.createElement("table"); // HTMLTableElement
	this.timeGrid = JSON.parse(getSelectedValues("grid"));
	this.userSchedule = schedule.id === "user";
	this.visibleDay = getDateFieldsDateObject().getDay();

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
		this.setGridDays();

		if (window.isMobile)
		{
			this.setActiveColumn();
		}

		if (newTimeGrid)
		{
			this.timeGrid = JSON.parse(getSelectedValues("grid"));
			this.setGridTime();
		}

		handleBreakRows(this.table, this.timeGrid);
		if (!(lessons["pastDate"] || lessons["futureDate"]))
		{
			this.insertLessons(lessons);
		}
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
			headerDate = getDateFieldsDateObject(), day = headerDate.getDay(),
			head = this.table.getElementsByTagName("thead")[0], headItems = head.getElementsByTagName("th");

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
			rows = this.table.getElementsByTagName("tbody")[0].getElementsByTagName("tr"), rowIndex,
			block, lesson, tableStartTime, tableEndTime, blockTimes, lessonElements, gridIndex, blockStart, blockEnd,
			cell, nextCell, nextBlock, nextRow, showOwnTime;

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
				// blockIndex for grid, rowIndex for rows without break
				gridIndex = 1;
				rowIndex = 0;
				for (block in lessons[date])
				{
					if (!lessons[date].hasOwnProperty(block))
					{
						continue;
					}

					tableStartTime = this.timeGrid.periods[gridIndex].startTime;
					tableEndTime = this.timeGrid.periods[gridIndex].endTime;
					blockTimes = block.match(/^(\d{4})-(\d{4})$/);
					blockStart = blockTimes[1];
					blockEnd = blockTimes[2];

					// Block does not fit? go to next block
					while (tableEndTime <= blockStart)
					{
						do {
							++rowIndex;
						}
						while (rows[rowIndex] && rows[rowIndex].className.match(/break/));

						++gridIndex;
						tableStartTime = this.timeGrid.periods[gridIndex].startTime;
						tableEndTime = this.timeGrid.periods[gridIndex].endTime;
					}

					for (lesson in lessons[date][block])
					{
						if (!lessons[date][block].hasOwnProperty(lesson))
						{
							continue;
						}

						// Append lesson in current table cell
						cell = rows[rowIndex].getElementsByTagName("td")[colNumber];
						showOwnTime = tableStartTime != blockStart || tableEndTime != blockEnd;
						lessonElements = this.createLesson(lessons[date][block][lesson], showOwnTime);
						lessonElements.forEach(function (element)
						{
							cell.appendChild(element);
						});
						jQuery(cell).addClass("lessons");

						// Lesson fits into next cell too? Add a copy to this
						nextBlock = this.timeGrid.periods[gridIndex + 1];
						nextRow = rows[rowIndex + 1];
						if (nextRow && nextRow.className.match(/break/))
						{
							nextRow = rows[rowIndex + 2];
						}
						if (nextRow && nextBlock && blockEnd > nextBlock.startTime)
						{
							nextCell = nextRow.getElementsByTagName("td")[colNumber];
							jQuery(nextCell).addClass("lessons");
							lessonElements = this.createLesson(lessons[date][block][lesson], showOwnTime);
							lessonElements.forEach(function (element)
							{
								nextCell.appendChild(element);
							});
						}
					}
					++gridIndex;
					// Jump over break
					do {
						++rowIndex;
					}
					while (rows[rowIndex] && rows[rowIndex].className.match(/break/));
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
		var lessons, subject, subjectData, lessonElement, ownTimeSpan, subjectOuterDiv, poolID,
			poolName, poolsOuterDiv, poolDiv, poolLink, teachersOuterDiv, teacherSpan, teacherLink, teacherID,
			teacherName, teacherDelta, roomsOuterDiv, roomSpan, roomLink, roomID, roomName, roomDelta, saveActionButton,
			deleteActionButton,	buttonIcon, added = false;

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

			if ((subjectData.shortName) || (subjectData.subjectNo))
			{
				subjectOuterDiv = document.createElement("div");
				subjectOuterDiv.className = "subjectNameNr";

				this.addSubjectElements(subjectOuterDiv, subjectData);
				lessonElement.appendChild(subjectOuterDiv);
			}

			if (schedule.id !== "user" && schedule.resource !== "pool" && subjectData.pools)
			{
				poolsOuterDiv = document.createElement("div");
				poolsOuterDiv.className = "pools";

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
						poolDiv = document.createElement("div");
						poolDiv.className = "pool " + (data.poolDelta ? data.poolDelta : "");
						poolDiv.appendChild(poolLink);
						poolsOuterDiv.appendChild(poolDiv);
					}
				}
				lessonElement.appendChild(poolsOuterDiv);
			}

			if (schedule.resource !== "teacher" && subjectData.teachers)
			{
				teachersOuterDiv = document.createElement("div");
				teachersOuterDiv.className = "persons";

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
						teacherDelta = subjectData.teacherDeltas[teacherID];
						teacherSpan = document.createElement("span");
						teacherSpan.className = "person " + (teacherDelta ? teacherDelta : "");
						teacherSpan.appendChild(teacherLink);
						teachersOuterDiv.appendChild(teacherSpan);
					}
				}
				lessonElement.appendChild(teachersOuterDiv);
			}

			if (schedule.resource !== "room" && subjectData.rooms)
			{
				roomsOuterDiv = document.createElement("div");
				roomsOuterDiv.className = "locations";

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
						roomDelta = subjectData.roomDeltas[roomID];
						roomSpan = document.createElement("span");
						roomSpan.className = "location " + (roomDelta ? roomDelta : "");
						roomSpan.appendChild(roomLink);
						roomsOuterDiv.appendChild(roomSpan);
					}
				}
				lessonElement.appendChild(roomsOuterDiv);
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
					handleLesson(variables.PERIOD_MODE, data.ccmID, true);
				});
				lessonElement.appendChild(saveActionButton);

				deleteActionButton = document.createElement("button");
				deleteActionButton.className = "delete-lesson";
				buttonIcon = document.createElement("span");
				buttonIcon.className = "icon-delete";
				deleteActionButton.appendChild(buttonIcon);
				deleteActionButton.addEventListener("click", function ()
				{
					handleLesson(variables.PERIOD_MODE, data.ccmID, false);
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
	 * Adds DOM-elements with eventListener directing to subject details, when there are some, to given outer element
	 *
	 * @param outerElement HTMLDivElement
	 * @param data Object lessonData with subjects
	 */
	this.addSubjectElements = function (outerElement, data)
	{
		var subjectLinkID, openSubjectDetailsLink, planProgramID, programID, schedule = this.schedule,
			subjectNameElement, numIndex, subjectNumbers, subjectNumberElement;

		// Find the right subjectID for subject details depending on schedule plan program
		function getSubjectDetailsID()
		{
			var subjectID, planProgramID;

			for (subjectID in data.programs)
			{
				if (!data.programs.hasOwnProperty(subjectID))
				{
					continue;
				}
				for (programID in data.programs[subjectID])
				{
					if (!data.programs[subjectID].hasOwnProperty(programID))
					{
						continue;
					}
					if (data.programs[subjectID][programID]["planProgramID"] === schedule.programID)
					{
						return subjectID;
					}
				}
			}
		}

		subjectLinkID = getSubjectDetailsID();
		openSubjectDetailsLink = function ()
		{
			window.open(variables.subjectDetailbase.replace(/&id=\d+/, "&id=" + subjectLinkID), "_blank");
		};

		// Add subject name and module name as DOM-elements to given outer element
		if (data.shortName)
		{
			if (subjectLinkID)
			{
				subjectNameElement = document.createElement("a");
				subjectNameElement.addEventListener("click", openSubjectDetailsLink);
			}
			else
			{
				subjectNameElement = document.createElement("span");
			}
			subjectNameElement.className = "name " + (data.subjectDelta ? data.subjectDelta : "");
			subjectNameElement.innerHTML = data.shortName + (data.method ? " - " + data.method : "");
			outerElement.appendChild(subjectNameElement);
		}
		if (data.subjectNo)
		{
			// multiple spans in case of semicolon separated module number for the design
			subjectNumbers = data.subjectNo.split(";");
			for (numIndex = 0; numIndex < subjectNumbers.length; ++numIndex)
			{
				if (subjectLinkID)
				{
					subjectNumberElement = document.createElement("a");
					subjectNumberElement.addEventListener("click", openSubjectDetailsLink);
				}
				else
				{
					subjectNumberElement = document.createElement("span");
				}
				subjectNumberElement.className = "module";
				subjectNumberElement.innerHTML = subjectNumbers[numIndex];
				outerElement.appendChild(subjectNumberElement);
			}
		}
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
			that.saveMenu.parentElement.style.display = "none";
		});
		this.saveSemesterMode.addEventListener("click", function ()
		{
			handleLesson(variables.SEMESTER_MODE, that.currentCcmID, true);
			that.saveMenu.style.display = "none";
		});
		this.savePeriodMode.addEventListener("click", function ()
		{
			handleLesson(variables.PERIOD_MODE, that.currentCcmID, true);
			that.saveMenu.style.display = "none";
		});
		this.saveInstanceMode.addEventListener("click", function ()
		{
			handleLesson(variables.INSTANCE_MODE, that.currentCcmID, true);
			that.saveMenu.style.display = "none";
		});
		this.closeDeleteMenuButton.addEventListener("click", function ()
		{
			that.deleteMenu.parentELement.style.display = "none";
		});
		this.deleteSemesterMode.addEventListener("click", function ()
		{
			handleLesson(variables.SEMESTER_MODE, that.currentCcmID, false);
			that.deleteMenu.style.display = "none";
		});
		this.deletePeriodMode.addEventListener("click", function ()
		{
			handleLesson(variables.PERIOD_MODE, that.currentCcmID, false);
			that.deleteMenu.style.display = "none";
		});
		this.deleteInstanceMode.addEventListener("click", function ()
		{
			handleLesson(variables.INSTANCE_MODE, that.currentCcmID, false);
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
 * adds event listeners and initialise (user) schedule and date input form field
 */
jQuery(document).ready(function ()
{
	var startX, startY, hasDepartmentSelection = document.getElementById("department-input");

	window.dateField = document.getElementById("date");
	window.dateField.value = new Date().getPresentationFormat();
	initSchedule();
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
	pastDateButton.addEventListener("click", nextDateEventHandler);
	futureDateButton.addEventListener("click", nextDateEventHandler);
	window.nextDateSelection.getElementsByClassName("close")[0].addEventListener("click", function ()
	{
		window.jumpToNextDate = false;
		window.nextDateSelection.style.display = "none";
		updateSchedule();
	});

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
		formats, resourceID, exportSelection = jQuery('#export-selection');

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
			exportSelection.val('placeholder');
			exportSelection.trigger("chosen:updated");
			return;
		}
	}
	else
	{
		resourceID = schedule.match(/[0-9]+/);

		if (resourceID === null)
		{
			exportSelection.val('placeholder');
			exportSelection.trigger("chosen:updated");
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
			exportSelection.val('placeholder');
			exportSelection.trigger("chosen:updated");
			return;
		}
	}

	window.open(url);
	exportSelection.val('placeholder');
	exportSelection.trigger("chosen:updated");
}

/**
 * sets values for the start and shows only the actual day on mobile devices
 */
function initSchedule()
{
	window.calendar = new Calendar();
	window.calendar.init();
	window.futureDateButton = document.getElementById("future-date");
	window.isMobile = window.matchMedia("(max-width: 677px)").matches;
	window.nextDateSelection = document.getElementById("next-date-selection");
	window.lessonMenu = new LessonMenu;
	window.lessonMenu.create();
	window.pastDateButton = document.getElementById("past-date");
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
	ajaxSelection.open("GET", variables.ajaxbase + task, true);
	ajaxSelection.onreadystatechange = updateForm;
	ajaxSelection.send(null);
}

/**
 * updates form fields with data from Ajax requests
 */
function updateForm()
{
	var values, fieldID, formField, option, optionCount, fields = ['pool', 'program', 'room', 'roomtype', 'teacher'];

	if (ajaxSelection.readyState === 4 && ajaxSelection.status === 200)
	{
		values = JSON.parse(ajaxSelection.responseText);
		fieldID = ajaxSelection.responseURL.match(/&task=get(\w+)s/)[1].toLowerCase();
		formField = document.getElementById(fieldID);
		removeChildren(formField);
		optionCount = Object.keys(values).length;

		if (fields.indexOf(fieldID) !== -1)
		{
			option = document.createElement("option");
			option.setAttribute("value", '');
			if (fieldID === 'pool')
			{
				option.innerHTML = text.POOL_PLACEHOLDER;
			}
			if (fieldID === 'program')
			{
				option.innerHTML = text.PROGRAM_PLACEHOLDER;
			}
			if (fieldID === 'room')
			{
				option.innerHTML = text.ROOM_PLACEHOLDER;
			}
			if (fieldID === 'roomtype')
			{
				option.innerHTML = text.ROOMTYPE_PLACEHOLDER;
			}
			if (fieldID === 'teacher')
			{
				option.innerHTML = text.TEACHER_PLACEHOLDER;
			}
			formField.appendChild(option);
		}

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
	}

	switchToScheduleListTab();
}

/**
 * Processes Ajax responses and updates the related schedules
 */
function insertLessonResponse()
{
	var ajaxRequest, response, schedule;

	for (var ajaxIndex = 0; ajaxIndex < window.scheduleRequests.length; ++ajaxIndex)
	{
		ajaxRequest = window.scheduleRequests[ajaxIndex];
		if (ajaxRequest.readyState === 4 && ajaxRequest.status === 200)
		{
			response = JSON.parse(ajaxRequest.responseText);
			if ((response.pastDate || response.futureDate) && window.jumpToNextDate)
			{
				openNextDateQuestion(response);
				window.scheduleRequests.splice(ajaxIndex, 1);
			}
			else
			{
				schedule = window.scheduleObjects.getScheduleByResponse(ajaxRequest.responseURL);
				schedule.setLessons(response);
				window.scheduleRequests.splice(ajaxIndex, 1);
			}
		}
		// Allow question about changing to next date again TODO: besseren Ort finden
		if (!window.jumpToNextDate && window.scheduleRequests.length === 0)
		{
			window.jumpToNextDate = true;
		}
	}
}

/**
 * Opens div which asks user to jump to the last or next available date
 *
 * @param dates array with pastDate and/or futureDate value
 */
function openNextDateQuestion(dates)
{
	var pastDate = dates["pastDate"] ? new Date(dates["pastDate"]) : null,
		futureDate = dates["futureDate"] ? new Date(dates["futureDate"]) : null;

	if (!dates)
	{
		return;
	}
	else
	{
		window.nextDateSelection.style.display = "block";
	}

	if (pastDate)
	{
		window.pastDateButton.innerHTML =
			window.pastDateButton.innerHTML.replace(window.datePattern, pastDate.getPresentationFormat());
		window.pastDateButton.dataset.date = dates["pastDate"];
		window.pastDateButton.style.display = "block";
	}
	else
	{
		window.pastDateButton.style.display = "none";
	}
	if (futureDate)
	{
		window.futureDateButton.innerHTML =
			window.futureDateButton.innerHTML.replace(window.datePattern, futureDate.getPresentationFormat());
		window.futureDateButton.dataset.date = dates["futureDate"];
		window.futureDateButton.style.display = "block";
	}
	else
	{
		window.futureDateButton.style.display = "none";
	}
}

/**
 * The date field gets the selected date, schedules get updates and the selection-div-element is hidden again
 */
function nextDateEventHandler()
{
	// 'this' is the element that triggered the eventHandler
	window.dateField.value = new Date(this.dataset.date).getPresentationFormat();
	window.nextDateSelection.style.display = "none";
	updateSchedule();
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

	task += "&mode=" + mode + "&ccmID=" + ccmID;
	ajaxSave = new XMLHttpRequest();
	ajaxSave.open("GET", variables.ajaxbase + task, true);
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
 * works just with chosen, don't work in mobile because the button wouldn't be added, because chosen isn't be used there
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
				jQuery(field).addClass("hide");
			}
			else
			{
				jQuery(field).removeClass("hide");
				fieldToShow = field;
			}
		}
		else
		{
			if (field.id !== fieldIDsToShow)
			{
				jQuery(field).addClass("hide");
			}
			else
			{
				jQuery(field).removeClass("hide");
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
 * goes one day for- or backward in the schedules and takes the date out of the input field with 'date' as id
 *
 * @param nextDate boolean goes forward by default, backward with false
 * @param weekStep boolean indicates the step the date takes
 */
function changeDate(nextDate, weekStep)
{
	var increaseDate = (typeof nextDate === "undefined") ? true : nextDate,
		week = (typeof weekStep === "undefined") ? true : weekStep,
		newDate = getDateFieldsDateObject();

	if (increaseDate)
	{
		if (week)
		{
			newDate.setDate(newDate.getDate() + 7);
		}
		else
		{
			newDate.setMonth(newDate.getMonth() + 1);
		}

		// Jump over sunday
		if (newDate.getDay() === 0)
		{
			newDate.setDate(newDate.getDate() + 1);
		}
	}
	// Decrease date
	else
	{
		if (week)
		{
			newDate.setDate(newDate.getDate() - 7);
		}
		else
		{
			newDate.setMonth(newDate.getMonth() - 1);
		}

		// Jump over sunday
		if (newDate.getDay() === 0)
		{
			newDate.setDate(newDate.getDate() - 1);
		}
	}

	window.dateField.value = newDate.getPresentationFormat();
}

/**
 * returns the current date field value as a string connected by minus.
 *
 * @returns string
 */
function getDateFieldString()
{
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
	if (parts)
	{
		// 12:00:00 o'clock for timezone offset
		return new Date(parseInt(parts[2], 10), parseInt(parts[1] - 1, 10), parseInt(parts[0], 10), 12, 0, 0);
	}
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
	jQuery("#schedule-form").removeClass("active");
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
 * add or remove rows for breaks depending of time grids
 * 
 * @param scheduleTable HTMLTableElement
 * @param grid object
 */
function handleBreakRows(scheduleTable, grid)
{
	var numberOfColumns = isMobile ? 2
			: jQuery(scheduleTable).find('tr:first').find('th').filter(function ()
		{
			return jQuery(this).css('display') != 'none';
		}).length,
		tableTbodyRow = jQuery(scheduleTable).find('tbody').find('tr'),
		addBreakRow = '<tr class="break-row"><td class="break" colspan=' + numberOfColumns + '></td></tr>',
		addLunchBreakRow = '<tr class="break-row"><td class="break" colspan=' + numberOfColumns + '>' + text.LUNCHTIME + '</td></tr>';

	if (grid)
	{
		if (!grid.hasOwnProperty('periods'))
		{
			jQuery(".break").closest('tr').remove();
			tableTbodyRow.not(':eq(0)').addClass("hide");
		}
		else if (grid.periods[1].endTime == grid.periods[2].startTime)
		{
			jQuery(".break").closest('tr').remove();
			tableTbodyRow.not(':eq(0)').removeClass("hide");
		}
		else if (!(tableTbodyRow.hasClass("break-row")))
		{
			tableTbodyRow.not(':eq(0)').removeClass("hide");
			for (var periods in grid.periods)
			{
				if (periods == 1 || periods == 2 || periods == 4 || periods == 5)
				{
					jQuery(addBreakRow).insertAfter(tableTbodyRow.eq(periods - 1));
				}
				if (periods == 3)
				{
					jQuery(addLunchBreakRow).insertAfter(tableTbodyRow.eq(periods - 1));
				}
			}
		}
	}
}

/*
 * context-menu-popup and calendar-popup will be closed when clicking outside this
 */
jQuery(document).mouseup(function (e)
{
	var popup = jQuery(".lesson-menu"),
		calendarPopup = jQuery("#calendar");

	if (!popup.is(e.target) && popup.has(e.target).length == 0)
	{
		popup.hide(500);
	}

	if (jQuery('.controls').css('display') !== 'none')
	{
		if (window.calendar.calendarDiv.style.visibility)
		{
			if (!calendarPopup.is(e.target) && calendarPopup.has(e.target).length == 0)
			{
				window.calendar.hideCalendar();
			}
		}
	}
});
