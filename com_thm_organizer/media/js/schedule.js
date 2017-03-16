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

var ajaxSave = null, ajaxSelection = null, calendar, dateField, datePattern, nextDateSelection, noLessons,
	scheduleObjects, scheduleWrapper, weekdays, placeholder,

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
		document.getElementById('calendar-next-month').addEventListener('click', function ()
		{
			that.changeCalendarMonth(true)
		});
		document.getElementById('calendar-previous-month').addEventListener('click', function ()
		{
			that.changeCalendarMonth(false);
		});
		document.getElementById('next-week').addEventListener('click', function ()
		{
			that.changeSelectedDate(true, "week");
		});
		document.getElementById('previous-week').addEventListener('click', function ()
		{
			that.changeSelectedDate(false, "week");
		});
		document.getElementById('next-month').addEventListener('click', function ()
		{
			that.changeSelectedDate(true, "month");
		});
		document.getElementById('previous-month').addEventListener('click', function ()
		{
			that.changeSelectedDate(false, "month");
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
		if (increaseMonth)
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
	 * Changes the current (date field) date and updates schedules
	 *
	 * @param increase boolean increase or decrease
	 * @param step string how big the change step is ("day"|"week"|"month")
	 */
	this.changeSelectedDate = function (increase, step)
	{
		changeDate(increase, step);
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
},

/**
 * Schedule 'class' for saving params and update the scheduleTable
 *
 * @param resource string name of (e.g. form) resource
 * @param IDs string makes together with resource the schedule ID
 * @param optionalTitle string optional title for directly linked schedules (e.g. teacher or room)
 */
Schedule = function (resource, IDs, optionalTitle)
{
	// for inner helper functions
	var that = this;

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
		this.requestUpdate();
		addScheduleToSelection(this);
		window.scheduleObjects.addSchedule(this);
	};

	/**
	 * Sets Ajax url for updating lessons
	 */
	this.task = (function ()
	{
		var task = "&departmentIDs=" + (variables.departmentID ? variables.departmentID : getSelectedValues("department"));
		task += variables.deltaDays ? "&deltaDays=" + variables.deltaDays : '';
		task += "&task=getLessons";
		task += "&date=" + getDateFieldString() + (variables.isMobile ? "&oneDay=true" : "");
		task += "&mySchedule=" + (resource === "user" ? "1" : "0");

		if (resource !== "user")
		{
			task += "&" + resource + "IDs=" + (IDs ? IDs.replace(/-/g, ",") : getSelectedValues(resource));
		}

		return task;
	})();

	/**
	 * Sets title that depends on the selected schedule
	 */
	this.title = (function ()
	{
		var title = variables.displayName ? variables.displayName : "",
			resourceField = document.getElementById(resource), resIndex,
			programField = document.getElementById("program"), programIndex;

		if (optionalTitle)
		{
			return optionalTitle;
		}

		if (resource === "user")
		{
			return text.MY_SCHEDULE;
		}

		// Get pre-selected value like "Informatik Master"
		if (resource === "pool" && programField.selectedOptions)
		{
			title = title ? title + " - " : title;
			for (programIndex = 0; programIndex < programField.selectedOptions.length; ++programIndex)
			{
				title += programField.selectedOptions[programIndex].text;
				if (programField.selectedOptions.length > programIndex + 1)
				{
					title += " - ";
				}
			}
		}

		// Get resource selection like "1. Semester" or "A20.1.1"
		if (resourceField && resourceField.selectedOptions)
		{
			title = title ? title + " - " : title;
			for (resIndex = 0; resIndex < resourceField.selectedOptions.length; ++resIndex)
			{
				title += resourceField.selectedOptions[resIndex].text;
				if (resourceField.selectedOptions.length > resIndex + 1)
				{
					title += " - ";
				}
			}
		}

		return title;
	}());

	/**
	 * Sends an Ajax request with the actual date to update the schedule
	 */
	this.requestUpdate = function ()
	{
		this.task = this.task.replace(/(date=)\d{4}\-\d{2}\-\d{2}/, "$1" + getDateFieldString());
		this.ajaxRequest.open("GET", variables.ajaxbase + this.task, true);

		this.ajaxRequest.onreadystatechange = function ()
		{
			if (that.ajaxRequest.readyState === 4 && that.ajaxRequest.status === 200)
			{
				var response = JSON.parse(that.ajaxRequest.responseText);

				if ((response.pastDate || response.futureDate) && that.id === getSelectedScheduleID())
				{
					openNextDateQuestion(response);
				}
				else if (response.pastDate === null && response.futureDate === null)
				{
					window.noLessons.style.display = "block";
				}
				else
				{
					that.lessons = response;
					that.scheduleTable.update(response);
				}
			}
		};

		this.ajaxRequest.send(null);
	};

	/**
	 * updates table with already given lessons, e.g. for changing time grids
	 */
	this.updateTable = function ()
	{
		this.scheduleTable.update(this.lessons, true);
	};
},

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
	this.timeGrid = JSON.parse(variables.grids[getSelectedValues("grid")].grid);
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
		if (newTimeGrid)
		{
			this.timeGrid = JSON.parse(variables.grids[getSelectedValues("grid")].grid);
			this.setGridTime();
		}

		this.resetTable();
		this.setGridDays();
		this.handleBreakRows();

		if (!(lessons["pastDate"] || lessons["futureDate"]))
		{
			this.insertLessons(lessons);
		}

		if (variables.isMobile)
		{
			this.setActiveColumn();
		}
	};

	/**
	 * Creates a table DOM-element with an input and label for selecting it and a caption with the given title.
	 * It gets appended to the scheduleWrapper.
	 */
	this.createScheduleElement = function ()
	{
		var input, div, tbody, row, initGrid, period, firstDay, weekEnd = 7;

		// Create input field for selecting this schedule
		input = document.createElement("input");
		input.className = "schedule-input";
		input.type = "radio";
		input.setAttribute("id", schedule.id + "-input");
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
		initGrid = this.timeGrid.hasOwnProperty("periods") ? this.timeGrid : variables.defaultGrid;
		for (period in initGrid.periods)
		{
			row = tbody.insertRow(-1);
			for (firstDay = 0; firstDay < weekEnd; ++firstDay)
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
			headerDate = getDateFieldsDateObject(), headIndex;

		// Set date to monday
		headerDate.setDate(headerDate.getDate() - headerDate.getDay());

		for (headIndex = 0; headIndex < weekend; ++headIndex)
		{
			th = document.createElement("th");
			thText = window.weekdays[headIndex - 1] + " (" + headerDate.getPresentationFormat() + ")";
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
			headerDate = getDateFieldsDateObject(), day = headerDate.getDay(), thElement,
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
		for (thElement = 1; thElement < headItems.length; ++thElement)
		{
			if (thElement === currentDay && currentDay <= endDay)
			{
				headItems[thElement].innerHTML = window.weekdays[currentDay - 1] +
					" (" + headerDate.getPresentationFormat(true) + ")";
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
		var colNumber = variables.isMobile ? this.visibleDay : 1,
			rows = this.table.getElementsByTagName("tbody")[0].getElementsByTagName("tr"), rowIndex,
			block, lesson, tableStartTime, tableEndTime, blockTimes, lessonElements, gridIndex, blockStart, blockEnd,
			cell, nextCell, nextBlock, nextRow, showOwnTime;

		if (this.timeGrid.periods)
		{
			for (var date in lessons)
			{
				if (!lessons.hasOwnProperty(date))
				{
					continue;
				}

				// gridIndex for grid, rowIndex for rows without break
				gridIndex = 1;
				rowIndex = 0;
				for (block in lessons[date])
				{
					if (!lessons[date].hasOwnProperty(block))
					{
						continue;
					}

					// Prevent going into next grid, when this block fits into previous too
					// e.g. block0 = 08:00 - 09:30, block1 = 08:00 - 10:00 o'clock
					if (gridIndex > 1)
					{
						// tableEndTime from last iterated block
						if (tableEndTime && block.match(/^(\d{4})-(\d{4})$/)[1] <= tableEndTime)
						{
							--gridIndex;
							do {
								--rowIndex;
							}
							while (rows[rowIndex] && rows[rowIndex].className.match(/break/));
						}
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

					cell = rows[rowIndex].getElementsByTagName("td")[colNumber];
					if (variables.registered && schedule.id !== "user"
						&& this.isOccupiedByUserLesson(rowIndex, colNumber))
					{
						jQuery(cell).addClass("occupied");
					}

					for (lesson in lessons[date][block])
					{
						if (!lessons[date][block].hasOwnProperty(lesson))
						{
							continue;
						}

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
				++colNumber;
			}
		}
		else
		{
			this.insertLessonsWithoutPeriod(lessons);
		}
	};

	/**
	 * No times on the left side - every lesson appears in the first row
	 *
	 * @param lessons object
	 */
	this.insertLessonsWithoutPeriod = function (lessons)
	{
		var colNumber = variables.isMobile ? this.visibleDay : 1,
			rows = this.table.getElementsByTagName("tbody")[0].getElementsByTagName("tr"),
			date, block, lesson, lessonElements, cell;

		for (date in lessons)
		{
			if (lessons.hasOwnProperty(date))
			{
				for (block in lessons[date])
				{
					if (lessons[date].hasOwnProperty(block))
					{
						for (lesson in lessons[date][block])
						{
							if (lessons[date][block].hasOwnProperty(lesson))
							{
								lessonElements = this.createLesson(lessons[date][block][lesson], true);
								lessonElements.forEach(function (element)
								{
									cell = rows[0].getElementsByTagName("td")[colNumber];
									cell.appendChild(element);
								});
							}
						}
					}
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
		var lessons, subject, subjectData, lessonElement, ownTimeSpan, subjectOuterDiv, poolsOuterDiv, teachersOuterDiv,
			roomsOuterDiv, added = false;

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
			subjectData.method = (data.method ? data.method : "");

			lessonElement = document.createElement("div");
			lessonElement.classList.add("lesson");

			// Data attributes instead of classes for finding the lesson later
			lessonElement.dataset.ccmID = data.ccmID;

			// Delta = "removed" or "new" or "changed" ? add class like "lesson-new"
			if (data.lessonDelta)
			{
				lessonElement.classList.add("lesson-" + data.lessonDelta);
			}
			if (data.calendarDelta)
			{
				lessonElement.classList.add("calendar-" + data.calendarDelta);
			}
			if (ownTime && data.startTime && data.endTime)
			{
				ownTimeSpan = document.createElement("span");
				ownTimeSpan.className = "own-time";
				ownTimeSpan.innerHTML =
					data.startTime.match(/^(\d{2}:\d{2})/)[1] + " - " + data.endTime.match(/^(\d{2}:\d{2})/)[1];
				lessonElement.appendChild(ownTimeSpan);
			}

			if (subjectData.name || subjectData.subjectNo)
			{
				subjectOuterDiv = document.createElement("div");
				subjectOuterDiv.className = "subjectNameNr";

				this.addSubjectElements(subjectOuterDiv, subjectData);
				lessonElement.appendChild(subjectOuterDiv);
			}

			if (subjectData.pools && schedule.id !== "user" && schedule.resource !== "pool")
			{
				poolsOuterDiv = document.createElement("div");
				poolsOuterDiv.className = "pools";
				this.addDataElements("pool", poolsOuterDiv, subjectData.pools, data.poolDelta);
				lessonElement.appendChild(poolsOuterDiv);
			}

			if (schedule.resource !== "teacher" && subjectData.teachers)
			{
				teachersOuterDiv = document.createElement("div");
				teachersOuterDiv.className = "persons";
				this.addDataElements("teacher", teachersOuterDiv, subjectData.teachers, subjectData.teacherDeltas, "person");
				lessonElement.appendChild(teachersOuterDiv);
			}

			if (schedule.resource !== "room" && subjectData.rooms)
			{
				roomsOuterDiv = document.createElement("div");
				roomsOuterDiv.className = "locations";
				this.addDataElements("room", roomsOuterDiv, subjectData.rooms, subjectData.roomDeltas, "location");
				lessonElement.appendChild(roomsOuterDiv);
			}

			if (variables.registered)
			{
				// Makes delete button visible only
				if (this.userSchedule || this.isSavedByUser(lessonElement))
				{
					lessonElement.classList.add("added");
				}

				this.addContextMenu(lessonElement, subjectData);
				this.addActionButtons(lessonElement, subjectData);
			}
			else
			{
				lessonElement.classList.add("no-saving");
			}

			this.lessonElements.push(lessonElement);
			lessons.push(lessonElement);
		}

		return lessons;
	};

	/**
	 * Adds context menu to given lessonElement
	 * Right click on lesson show save/delete menu
	 *
	 * @param lessonElement HTMLDivElement the html element which needs a context menu
	 * @param data array                   the lesson/subject data
	 */
	this.addContextMenu = function (lessonElement, data)
	{
		var lesson = lessonElement;

		lesson.addEventListener("contextmenu", function (event)
		{
			if (!lesson.classList.contains("calendar-removed") && !lesson.classList.contains("lesson-removed"))
			{
				event.preventDefault();
				window.lessonMenu.getSaveMenu(lesson);
				window.lessonMenu.setLessonData(data);
			}

			if (lesson.classList.contains("added"))
			{
				event.preventDefault();
				window.lessonMenu.getDeleteMenu(lesson);
				window.lessonMenu.setLessonData(data);
			}
		});
	};

	/**
	 * Adds buttons for saving and deleting a lesson
	 *
	 * @param lessonElement HTMLDivElement
	 * @param data Object
	 */
	this.addActionButtons = function(lessonElement, data)
	{
		var saveDiv, saveActionButton, questionActionButton, deleteDiv, deleteActionButton;

		// Saving a lesson
		saveActionButton = document.createElement("button");
		saveActionButton.className = "icon-plus";
		saveActionButton.addEventListener("click", function ()
		{
			handleLesson(variables.PERIOD_MODE, data.ccmID, true);
		});
		questionActionButton = document.createElement("button");
		questionActionButton.className = "icon-question";
		questionActionButton.addEventListener("click", function ()
		{
			window.lessonMenu.getSaveMenu(lessonElement);
			window.lessonMenu.setLessonData(subjectData);
		});
		saveDiv = document.createElement("div");
		saveDiv.className = "add-lesson";
		saveDiv.appendChild(saveActionButton);
		saveDiv.appendChild(questionActionButton);
		lessonElement.appendChild(saveDiv);

		// Deleting a lesson
		deleteActionButton = document.createElement("button");
		deleteActionButton.className = "icon-delete";
		deleteActionButton.addEventListener("click", function ()
		{
			handleLesson(variables.PERIOD_MODE, data.ccmID, false);
		});
		questionActionButton = document.createElement("button");
		questionActionButton.className = "icon-question";
		questionActionButton.addEventListener("click", function ()
		{
			window.lessonMenu.getDeleteMenu(lessonElement);
			window.lessonMenu.setLessonData(subjectData);
		});
		deleteDiv = document.createElement("div");
		deleteDiv.className = "delete-lesson";
		deleteDiv.appendChild(deleteActionButton);
		deleteDiv.appendChild(questionActionButton);

		lessonElement.appendChild(deleteDiv);
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
			subjectNameElement, name, numIndex, subjectNumbers, subjectNumberElement;

		// Find the right subjectID for subject details depending on schedule plan program
		subjectLinkID = (function ()
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
		}());

		openSubjectDetailsLink = function ()
		{
			window.open(variables.subjectDetailbase.replace(/&id=\d+/, "&id=" + subjectLinkID), "_blank");
		};

		// Add subject name and module name as DOM-elements to given outer element
		if (data.name && data.shortName)
		{
			if (subjectLinkID && variables.showPools !== "0")
			{
				subjectNameElement = document.createElement("a");
				subjectNameElement.addEventListener("click", openSubjectDetailsLink);
			}
			else
			{
				subjectNameElement = document.createElement("span");
			}
			if (variables.isMobile)
			{
				subjectNameElement.innerHTML = data.shortName + (data.method ? " - " + data.method : "");
			}
			else
			{
				// Append whitespace to slashs for better word break
				name = data.name.match(/\S\/\S/g) ? data.name.replace(/(\S)\/(\S)/g, "$1 / $2") : data.name;
				subjectNameElement.innerHTML = name + (data.method ? " - " + data.method : "");
			}
			subjectNameElement.className = "name " + (data.subjectDelta ? data.subjectDelta : "");
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
	 * Adds HTML elements containing the given data in relation to given resource.
	 *
	 * @param resource string 			resource to add e.g. "room" or "pool"
	 * @param outerElement HTMLElement  wrapper element
	 * @param data Object 				lesson data
	 * @param delta Object|string 		optional, delta like "new" or "remove"
	 * @param className string 			optional, class to style the elements
	 */
	this.addDataElements = function (resource, outerElement, data, delta, className)
	{
		var id, span, nameElement, showX = "show" + resource.slice(0, 1).toUpperCase() + resource.slice(1) + "s", deltaClass;

		for (id in data)
		{
			if (data.hasOwnProperty(id))
			{
				span = document.createElement("span");
				deltaClass = delta[id] ? delta[id] : (typeof delta === "string" ? delta : "");
				span.className = (className ? className : resource) + " " + deltaClass;
				nameElement = variables[showX] !== "0" ? document.createElement("a") : document.createElement("span");
				nameElement.innerHTML = data[id].gpuntisID ? data[id].gpuntisID : data[id];

				if (variables[showX] !== "0")
				{
					// closure because of for-loop
					(function (id)
					{
						nameElement.addEventListener("click", function ()
						{
							sendLessonRequest(resource, id, data[id].fullName ? data[id].fullName : data[id]);
						});
					})(id);
				}

				span.appendChild(nameElement);
				outerElement.appendChild(span);
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
	 * Checks for a block if the user has lessons in it already
	 *
	 * @param rowIndex int
	 * @param colIndex int
	 * @return boolean
	 */
	this.isOccupiedByUserLesson = function (rowIndex, colIndex)
	{
		var userScheduleTable = window.scheduleObjects.getScheduleById("user").scheduleTable.table,
			rows = userScheduleTable.getElementsByTagName("tbody")[0].getElementsByTagName("tr"),
			row = rows[rowIndex],
			cell = row ? row.getElementsByTagName("td")[colIndex] : false;

		return (cell && cell.className && cell.className.match(/lessons/));
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
		window.scheduleWrapper.removeChild(document.getElementById(schedule.id + "-input"));
		// table element
		window.scheduleWrapper.removeChild(document.getElementById(schedule.id + "-schedule"));
	};

	/**
	 * Add or remove rows for breaks depending on time grid
	 */
	this.handleBreakRows = function ()
	{
		var numberOfColumns = variables.isMobile ? 2
				: jQuery(this.table).find('tr:first').find('th').filter(function ()
			{
				return jQuery(this).css('display') != 'none';
			}).length,
			tableTbodyRow = jQuery(this.table).find('tbody').find('tr'),
			addBreakRow = '<tr class="break-row"><td class="break" colspan=' + numberOfColumns + '></td></tr>',
			addLunchBreakRow = '<tr class="break-row"><td class="break" colspan=' + numberOfColumns + '>' + text.LUNCHTIME + '</td></tr>';

		if (!this.timeGrid.hasOwnProperty('periods'))
		{
			jQuery(".break").closest('tr').remove();
			tableTbodyRow.not(':eq(0)').addClass("hide");
		}
		else if (this.timeGrid.periods[1].endTime == this.timeGrid.periods[2].startTime)
		{
			jQuery(".break").closest('tr').remove();
			tableTbodyRow.not(':eq(0)').removeClass("hide");
		}
		else if (!(tableTbodyRow.hasClass("break-row")))
		{
			tableTbodyRow.not(':eq(0)').removeClass("hide");
			for (var periods in this.timeGrid.periods)
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
	};
},

/**
 * Creates a lesson menu for saving and deleting a lesson, which opens by right clicking on it
 */
LessonMenu = function ()
{
	var that = this;
	this.currentCcmID = 0;
	this.lessonMenuElement = document.getElementsByClassName('lesson-menu')[0];
	this.closeButton = this.lessonMenuElement.getElementsByClassName('icon-cancel')[0];
	this.subjectSpan = this.lessonMenuElement.getElementsByClassName('subject')[0];
	this.moduleSpan = this.lessonMenuElement.getElementsByClassName('module')[0];
	this.personsDiv = this.lessonMenuElement.getElementsByClassName('persons')[0];
	this.roomsDiv = this.lessonMenuElement.getElementsByClassName('rooms')[0];
	this.poolsDiv = this.lessonMenuElement.getElementsByClassName('pools')[0];
	this.descriptionSpan = this.lessonMenuElement.getElementsByClassName('description')[0];
	this.saveMenu = this.lessonMenuElement.getElementsByClassName('save')[0];
	this.saveSemesterMode = document.getElementById('save-mode-semester');
	this.savePeriodMode = document.getElementById('save-mode-period');
	this.saveInstanceMode = document.getElementById('save-mode-instance');
	this.deleteMenu = this.lessonMenuElement.getElementsByClassName('delete')[0];
	this.deleteSemesterMode = document.getElementById('delete-mode-semester');
	this.deletePeriodMode = document.getElementById('delete-mode-period');
	this.deleteInstanceMode = document.getElementById('delete-mode-instance');

	/**
	 * Adds eventListeners to html elements
	 */
	this.create = function ()
	{
		this.closeButton.addEventListener("click", function ()
		{
			that.lessonMenuElement.style.display = "none";
		});
		this.saveSemesterMode.addEventListener("click", function ()
		{
			handleLesson(variables.SEMESTER_MODE, that.currentCcmID, true);
			that.saveMenu.parentNode.style.display = "none";
		});
		this.savePeriodMode.addEventListener("click", function ()
		{
			handleLesson(variables.PERIOD_MODE, that.currentCcmID, true);
			that.saveMenu.parentNode.style.display = "none";
		});
		this.saveInstanceMode.addEventListener("click", function ()
		{
			handleLesson(variables.INSTANCE_MODE, that.currentCcmID, true);
			that.saveMenu.parentNode.style.display = "none";
		});
		this.deleteSemesterMode.addEventListener("click", function ()
		{
			handleLesson(variables.SEMESTER_MODE, that.currentCcmID, false);
			that.deleteMenu.parentNode.style.display = "none";
		});
		this.deletePeriodMode.addEventListener("click", function ()
		{
			handleLesson(variables.PERIOD_MODE, that.currentCcmID, false);
			that.deleteMenu.parentNode.style.display = "none";
		});
		this.deleteInstanceMode.addEventListener("click", function ()
		{
			handleLesson(variables.INSTANCE_MODE, that.currentCcmID, false);
			that.deleteMenu.parentNode.style.display = "none";
		});
	};

	/**
	 * Resets HTMLDivElements
	 */
	this.resetElements = function ()
	{
		removeChildren(this.personsDiv);
		removeChildren(this.roomsDiv);
		removeChildren(this.poolsDiv);
	};

	/**
	 * Inserts data of active lesson
	 *
	 * @param data object lesson data like subject name, persons, locations...
	 */
	this.setLessonData = function (data)
	{
		var teacherID, personSpan, roomID, roomSpan, poolID, poolSpan;

		this.resetElements();

		this.subjectSpan.innerHTML = data.name;
		this.moduleSpan.innerHTML = data.subjectNo;
		this.descriptionSpan.innerHTML = ""; // TODO: description Link hinzufügen

		for (teacherID in data.teachers)
		{
			if (data.teachers.hasOwnProperty(teacherID) && data.teacherDeltas[teacherID] != "removed")
			{
				personSpan = document.createElement("span");
				personSpan.innerHTML = data.teachers[teacherID];
				this.personsDiv.appendChild(personSpan);
			}
		}
		for (roomID in data.rooms)
		{
			if (data.rooms.hasOwnProperty(roomID) && data.roomDeltas[roomID] != "removed")
			{
				roomSpan = document.createElement("span");
				roomSpan.innerHTML = data.rooms[roomID];
				this.roomsDiv.appendChild(roomSpan);
			}
		}
		for (poolID in data.pools)
		{
			if (data.pools.hasOwnProperty(poolID))
			{
				poolSpan = document.createElement("span");
				poolSpan.innerHTML = data.pools[poolID].gpuntisID;
				this.poolsDiv.appendChild(poolSpan);
			}
		}
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
},

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
 * @param shortYear boolean default = depending on
 * @returns string
 */
Date.prototype.getPresentationFormat = function (shortYear)
{
	var date = variables.dateFormat,
		day = this.getDate(),
		dayLong = day < 10 ? "0" + day : day,
		month = this.getMonth() + 1, // getMonth() is zero-based
		monthLong = month < 10 ? "0" + month : month,
		yearLong = this.getFullYear(),
		year = yearLong.toString().substr(2, 2);

	// Insert day
	date = date.replace(/j/, day.toString());
	date = date.replace(/d/, dayLong);
	// Insert month
	date = date.replace(/n/, month.toString());
	date = date.replace(/m/, monthLong);

	// Insert year
	if (typeof shortYear === "undefined" ? false : shortYear)
	{
		date = date.replace(/y|Y/, year.toString());
	}
	else
	{
		date = date.replace(/Y/, yearLong.toString());
		date = date.replace(/y/, year.toString());
	}

	return date;
};

/**
 * adds event listeners and initialise (user) schedule and date input form field
 */
jQuery(document).ready(function ()
{
	var startX, startY, userSchedule;

	window.weekdays = [
		text.MONDAY_SHORT,
		text.TUESDAY_SHORT,
		text.WEDNESDAY_SHORT,
		text.THURSDAY_SHORT,
		text.FRIDAY_SHORT,
		text.SATURDAY_SHORT,
		text.SUNDAY_SHORT
	];
	window.placeholder = {
		"pool": text.POOL_PLACEHOLDER,
		"program": text.PROGRAM_PLACEHOLDER,
		"room": text.ROOM_PLACEHOLDER,
		"roomtype": text.ROOMTYPE_PLACEHOLDER,
		"teacher": text.TEACHER_PLACEHOLDER
	};
	window.dateField = document.getElementById("date");
	window.dateField.value = new Date().getPresentationFormat();
	window.futureDateButton = document.getElementById("future-date");
	window.futureDateButton.addEventListener("click", nextDateEventHandler);
	window.inputFields = document.getElementById("schedule-form").getElementsByClassName("input-wrapper");
	window.nextDateSelection = document.getElementById("next-date-selection");
	window.nextDateSelection.getElementsByClassName("close")[0].addEventListener("click", function ()
	{
		window.nextDateSelection.style.display = "none";
	});
	window.noLessons = document.getElementById("no-lessons");
	window.noLessons.getElementsByClassName("close")[0].addEventListener("click", function ()
	{
		window.noLessons.style.display = "none";
	});
	window.pastDateButton = document.getElementById("past-date");
	window.pastDateButton.addEventListener("click", nextDateEventHandler);
	window.scheduleWrapper = document.getElementById("scheduleWrapper");

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
				changeDate(true, variables.isMobile ? "day" : "week");
				updateSchedule();
			}
			if (distX > minDist)
			{
				event.stopPropagation();
				changeDate(false, variables.isMobile ? "day" : "week");
				updateSchedule();
			}
		}
	});

	jQuery("#grid").chosen().change(updateSchedule);
	jQuery("#date").change(updateSchedule);
	jQuery("#category").chosen().change(function ()
	{
		sendFormRequest(this.value);
		showForm("category-" + this.value);
	});
	jQuery("#department").chosen().change(function ()
	{
		showForm("category-" + getSelectedValues("category"));
		sendFormRequest(getSelectedValues("category"));
	});

	jQuery("#schedules").chosen().change(function ()
	{
		var scheduleInput = document.getElementById(jQuery("#schedules").val());

		// To show the schedule after this input field (by css)
		scheduleInput.checked = "checked";
	});

	// Change Tab-Behaviour of menu-bar, so all tabs can be closed
	jQuery(".tabs-toggle").on("click", function (event)
	{
		changeTabBehaviour(jQuery(this));

		//prevent loading of tabs-url:
		event.preventDefault();
	});

	window.calendar = new Calendar();
	window.calendar.init();
	window.lessonMenu = new LessonMenu;
	window.lessonMenu.create();
	window.scheduleObjects = new Schedules;
	setDatePattern();
	changePositionOfDateInput();
	disableTabs();
	setUpForm();

	if (variables.registered)
	{
		userSchedule = new Schedule("user");
		userSchedule.create();
		switchToScheduleListTab();
	}
});

/**
 * Opens export window of selected schedule
 *
 * @param format string
 */
function handleExport(format)
{
	var schedule = getSelectedScheduleID(), url = variables.exportbase,
		formats, resourceID, exportSelection = jQuery('#export-selection'),
		gridID = variables.grids[getSelectedValues("grid")].id;

	formats = format.split('.');
	url += "&format=" + formats[0];

	if (formats[0] === 'pdf')
	{
		url += "&gridID=" + gridID;
	}

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
			return;
		}

		url += "&date=" + getDateFieldString();

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
			return;
		}
	}

	window.open(url);
	exportSelection.val('placeholder');
	exportSelection.trigger("chosen:updated");
}

/**
 * Configures and build form depending on (url-) parameters
 */
function setUpForm()
{
	var title = variables.displayName,
		formConfig = (function ()
		{
			var program = variables.showPrograms !== "0", // (string) "0" = true
				room = variables.showRooms !== "0",
				teacher = variables.showTeachers !== "0",
				paramIDs = (function ()
				{
					var params = variables.subjectIDs || variables.programIDs || variables.poolIDs || variables.roomIDs
						|| variables.roomTypeIDs || variables.teacherIDs;

					if (typeof params === "number")
					{
						return params.toString();
					}
					else if (typeof params === "object")
					{
						return params.join(",");
					}
					return null;
				})(),
				categoryName = (function ()
				{
					return variables.subjectIDs ? "subject"
						: variables.programIDs ? "program"
						: variables.poolIDs ? "pool"
						: variables.teacherIDs ? "teacher"
						: variables.roomTypeIDs ? "roomtype"
						: variables.roomIDs ? "room"
						: null;
				})();

			return {
				max: (program ? 1 : 0) + (room ? 1 : 0) + (teacher ? 1 : 0),
				inputToShowFirst: program ? "program" : room ? "roomtype" : "teacher",
				IDs: paramIDs,
				category: categoryName
			};
		})();

	// Hide category input when there is only one category activated or no category needed
	if (formConfig.max === 1 || formConfig.IDs)
	{
		document.getElementById("category-input").classList.add("hide");
	}

	if (formConfig.IDs)
	{
		if (variables.programIDs)
		{
			sendFormRequest("pool", formConfig.IDs);
			showForm("pool");
		}
		else if (variables.roomTypeIDs)
		{
			sendFormRequest("room", formConfig.IDs);
			showForm("room");
		}
		else
		{
			sendLessonRequest(formConfig.category, formConfig.IDs, title);
		}
		if (variables.poolIDs || variables.roomIDs || variables.teacherIDs)
		{
			disableTabs("tab-schedule-form");
		}
		if (variables.subjectIDs)
		{
			disableTabs(["tab-schedule-form", "tab-selected-schedules"]);
		}
	}
	else
	{
		sendFormRequest(formConfig.inputToShowFirst);
		showForm("category-" + formConfig.inputToShowFirst);
	}
}

/**
 * Sends a request for the given input ID and shows the belonging fields
 *
 * @param inputID string
 */
function showForm(inputID)
{
	var element, inputIndex, field, fieldLength = window.inputFields.length,
		order = {
			"category-program": ["program-input"],
			"program": ["program-input", "pool-input"],
			"pool": ["pool-input"],
			"category-roomtype": ["roomtype-input"],
			"roomtype": ["roomtype-input", "room-input"],
			"room": ["room-input"],
			"category-teacher": ["teacher-input"]
		};

	// no pre set department, so it is needed for every form
	if (variables.departmentID === "0")
	{
		for (element in order)
		{
			if (order.hasOwnProperty(element))
			{
				order[element].push("department-input");
			}
		}
	}

	for (inputIndex = 0; inputIndex < fieldLength; ++inputIndex)
	{
		field = window.inputFields[inputIndex];
		if (order[inputID].indexOf(field.id) !== -1)
		{
			field.classList.remove("hide");
		}
		else
		{
			field.classList.add("hide");
		}
	}
}

/**
 * starts an Ajax request to fill form fields with values
 *
 * @param resource  string
 * @param ids       string|int to insert them in request instead of form selection values
 */
function sendFormRequest(resource, ids)
{
	var task = "&departmentIDs=" + (variables.departmentID === "0" ? getSelectedValues("department") : variables.departmentID);

	switch (resource)
	{
		case "program":
			task += "&task=getPrograms";
			break;
		case "pool":
			task += "&task=getPools" + "&programIDs=" + (ids ? ids : getSelectedValues("program"));
			break;
		case "roomtype":
			task += "&task=getRoomTypes";
			break;
		case "room":
			task += "&task=getRooms" + "&typeID=" + (ids ? ids : getSelectedValues("roomtype"));
			break;
		case "teacher":
			task += "&task=getTeachers";
			break;
		case "subject":
			return sendLessonRequest(resource, ids);
		default:
			console.log("searching default category...");
			return;
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
	var value, values, fieldID, fieldElement, option, optionCount;

	if (ajaxSelection.readyState === 4 && ajaxSelection.status === 200)
	{
		fieldID = ajaxSelection.responseURL.match(/&task=get(\w+)s/)[1].toLowerCase();
		fieldElement = document.getElementById(fieldID);
		removeChildren(fieldElement);

		if (window.placeholder[fieldID])
		{
			option = document.createElement("option");
			option.setAttribute("value", "");
			option.innerHTML = window.placeholder[fieldID];
			fieldElement.appendChild(option);
		}

		values = JSON.parse(ajaxSelection.responseText);
		optionCount = Object.keys(values).length;
		for (value in values)
		{
			if (values.hasOwnProperty(value))
			{
				option = document.createElement("option");
				option.setAttribute("value", value.name ? values[value].id : values[value]);
				option.innerHTML = value.name ? values[value].name : value;
				option.selected = optionCount === 1;
				fieldElement.appendChild(option);
			}
		}

		fieldElement.removeAttribute("disabled");
		jQuery("#" + fieldID).chosen("destroy").chosen();
		addSelectionEvent(fieldID);

		if (optionCount === 1)
		{
			sendLessonRequest(fieldID);
		}
	}
}

/**
 * Adds an event handler for schedule form selection elements
 *
 * @param id string the elements ID which needs this event handler
 */
function addSelectionEvent(id)
{
	var input = document.getElementById(id + "-input"), drop = input.getElementsByClassName("chzn-drop")[0];

	if (variables.isMobile)
	{
		// no Chosen-library available
		document.getElementById(id).addEventListener("change", handleFormSelection);
	}
	// Select on click, even on already selected(!) options (unlike Chosens "change" event)
	if (input.dataset.next)
	{
		jQuery("#" + id).chosen().change(handleFormSelection);
	}
	else
	{
		drop.addEventListener("click", function (event)
		{
			if (event.target.dataset.optionArrayIndex !== "0")
			{
				handleFormSelection(id);
			}
		});
	}
}

/**
 * Event handler for changing/selecting a schedule form input to load the next data or loads lessons
 *
 * @param id string the form inputs id (optional).
 */
function handleFormSelection(id)
{
	var element = (typeof id === "string") ? document.getElementById(id) : id.target;
	if (element.dataset.next)
	{
		sendFormRequest(element.dataset.next);
		showForm(element.id);
	}
	else
	{
		sendLessonRequest(element.id);
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
	}

	switchToScheduleListTab();
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

	window.nextDateSelection.style.display = "block";

	if (pastDate)
	{
		window.pastDateButton.innerHTML =
			window.pastDateButton.innerHTML.replace(window.datePattern, pastDate.getPresentationFormat());
		window.pastDateButton.dataset.date = dates["pastDate"];
		jQuery(window.pastDateButton).show();
	}
	else
	{
		jQuery(window.pastDateButton).hide();
	}
	if (futureDate)
	{
		window.futureDateButton.innerHTML =
			window.futureDateButton.innerHTML.replace(window.datePattern, futureDate.getPresentationFormat());
		window.futureDateButton.dataset.date = dates["futureDate"];
		jQuery(window.futureDateButton).show();
	}
	else
	{
		jQuery(window.futureDateButton).hide();
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
						if (lessonElements[lessonIndex].classList.contains("lesson"))
						{
							if (lessonElements[lessonIndex].classList.contains("added"))
							{
								lessonElements[lessonIndex].classList.remove("added");
							}
							else
							{
								lessonElements[lessonIndex].classList.add("added");
							}
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
	var selectedItem, selectedTitle, showButton, removeButton;

	selectedItem = document.createElement("div");
	selectedItem.id = schedule.id;
	selectedItem.className = "selected-schedule";

	selectedTitle = document.createElement("button");
	selectedTitle.className = "title";
	selectedTitle.innerHTML = schedule.title;
	selectedTitle.addEventListener("click", function ()
	{
		showSchedule(schedule.id);
	});
	selectedItem.append(selectedTitle);

	showButton = document.createElement("button");
	showButton.className = "show-schedule";
	showButton.innerHTML = "<span class='icon-eye-close'></span>";
	showButton.addEventListener("click", function ()
	{
		showSchedule(schedule.id);
	});
	selectedItem.append(showButton);

	if (schedule.id !== "user")
	{
		removeButton = document.createElement("button");
		removeButton.className = "remove-schedule";
		removeButton.innerHTML = "<span class='icon-remove'></span>";
		removeButton.addEventListener("click", function ()
		{
			removeScheduleFromSelection(this, schedule);
		});
		selectedItem.append(removeButton);
	}
	jQuery("#selected-schedules").append(selectedItem);
	showSchedule(schedule.id);
}

/**
 * Shows schedule with given ID
 *
 * @param scheduleID string
 */
function showSchedule(scheduleID)
{
	var scheduleElements = jQuery(".schedule-input"), schedulesIndex;

	for (schedulesIndex = 0; schedulesIndex < scheduleElements.length; ++schedulesIndex)
	{
		if (scheduleElements[schedulesIndex].id === scheduleID + "-input")
		{
			scheduleElements[schedulesIndex].checked = "checked";
			jQuery(".selected-schedule").removeClass("shown");
			jQuery("#" + scheduleID).addClass("shown");
		}
	}
	disableTabs();
}

/**
 * Gets ID of now selected schedule in #selected-schedules HTMLDivElement.
 * Returns false in case no schedule was found.
 *
 * @returns string|boolean
 */
function getSelectedScheduleID()
{
	var selectedSchedule = document.getElementById("selected-schedules").getElementsByClassName("shown")[0];

	return selectedSchedule ? selectedSchedule.id : false;
}

/**
 * remove an entry from the dropdown field for selecting a schedule
 */
function removeScheduleFromSelection(clickedButton, schedule)
{
	clickedButton.parentNode.remove();
	window.scheduleObjects.removeSchedule(schedule);

	if (window.scheduleObjects.schedules.length === 0)
	{
		showSchedule("default");
		switchToFormTab();
	}
	else
	{
		showSchedule(jQuery("#selected-schedules").find(".selected-schedule").last().attr("id"));
	}
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
 * @param increase boolean goes forward by default, backward with false
 * @param step     string  defines how big the step is as "day", "week" or "month"
 */
function changeDate(increase, step)
{
	var stepString = step ? step : "week",
		stepInt = stepString === "week" ? 7 : 1,
		newDate = getDateFieldsDateObject();

	if (increase)
	{
		if (step === "month")
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
	// Decrease date
	else
	{
		if (step === "month")
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
 * Activates tab with a form for selecting a new schedule
 */
function switchToFormTab()
{
	jQuery("#tab-schedule-form").parent("li").addClass("active");
	jQuery("#schedule-form").addClass("active");
	jQuery("#tab-selected-schedules").parent("li").removeClass("active");
	jQuery("#selected-schedules").removeClass("active");
}

/**
 * change position of the date-input, depending of screen-width
 */
function changePositionOfDateInput()
{
	var mq = window.matchMedia("(max-width: 677px)");
	if (variables.isMobile)
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
 * Disable tabs, when only the default-schedule-table is shown
 *
 * @param tabIDs string|array  optional to disable only specific tabs
 */
function disableTabs(tabIDs)
{
	var i, allTabs = jQuery(".tabs-toggle"), scheduleInput = jQuery(".schedule-input"), disableTabs;

	disableTabs = [
		jQuery("#tab-selected-schedules"),
		jQuery("#tab-time-selection"),
		jQuery("#tab-exports")
	];

	if (tabIDs)
	{
		for (i = 0; i < allTabs.length; i++)
		{
			if (tabIDs.indexOf(allTabs[i].id) !== -1)
			{
				allTabs[i].dataset.toggle = "";
				allTabs[i].parentElement.classList.add("disabled-tab");
			}
		}
	}
	// No schedule selected - disable all but schedule form
	else if (scheduleInput.length == 1 && scheduleInput.is("#default-input"))
	{
		for (i = 0; i < disableTabs.length; i++)
		{
			disableTabs[i].attr("data-toggle", "");
			disableTabs[i].parent("li").addClass("disabled-tab");
		}
	}
	// Activates all tabs
	else
	{
		for (i = 0; i < disableTabs.length; i++)
		{
			disableTabs[i].attr("data-toggle", "tab");
			disableTabs[i].parent('li').removeClass("disabled-tab");
		}
	}
}

/**
 * context-menu-popup, calendar-popup and message-popup will be closed when clicking outside this
 */
jQuery(document).mouseup(function (e)
{
	var popup = jQuery(".lesson-menu"), calendarPopup = jQuery("#calendar"), messagePopup = jQuery(".message-pop-up");

	if (!popup.is(e.target) && popup.has(e.target).length == 0)
	{
		popup.hide(0);
	}

	if (!messagePopup.is(e.target) && messagePopup.has(e.target).length == 0)
	{
		messagePopup.hide(0);
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
