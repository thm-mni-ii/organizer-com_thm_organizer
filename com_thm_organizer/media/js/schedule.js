jQuery(document).ready(function () {
	"use strict";
	window.scheduleApp = new ScheduleApp(Joomla.getOptions('text', {}), Joomla.getOptions('variables', {}));
});

/**
 * Object that builds schedule tables, an interactive calendar and a form which defines loaded schedules
 * @param {mixed|Object} text - contains translated text strings
 * @param {string} text.APRIL - name of April
 * @param {string} text.AUGUST - name of August
 * @param {string} text.COPY - text for generating a link
 * @param {string} text.DECEMBER - name of December
 * @param {string} text.FEBRUARY - name of February
 * @param {string} text.FRIDAY_SHORT - short name of Friday
 * @param {string} text.JANUARY - name of January
 * @param {string} text.JULY - name of July
 * @param {string} text.JUNE - name of June
 * @param {string} text.LUNCHTIME - text for break/lunchtime in schedule
 * @param {string} text.MARCH - name of March
 * @param {string} text.MAY - name of May
 * @param {string} text.MONDAY_SHORT - short name of Monday
 * @param {string} text.MY_SCHEDULE - text for a personal schedule
 * @param {string} text.NOVEMBER - name of November
 * @param {string} text.OCTOBER - name of October
 * @param {string} text.POOL_PLACEHOLDER - text for a placeholder in pool selection
 * @param {string} text.PROGRAM_PLACEHOLDER - text for a placeholder in program selection
 * @param {string} text.ROOM_PLACEHOLDER - text for a placeholder in room selection
 * @param {string} text.ROOM_TYPE_PLACEHOLDER - text for a placeholder in room type selection
 * @param {string} text.SATURDAY_SHORT - short name of Saturday
 * @param {string} text.SEPTEMBER - name of September
 * @param {string} text.SUNDAY_SHORT - short name of Sunday
 * @param {string} text.THURSDAY_SHORT - short name of Thursday
 * @param {string} text.TIME - text for time
 * @param {string} text.TEACHER_PLACEHOLDER - text for a placeholder in teacher selection
 * @param {string} text.TUESDAY_SHORT - short name of Tuesday
 * @param {string} text.WEDNESDAY_SHORT - short name of Wednesday
 * @param {mixed|Object} variables - contains website configurations
 * @param {string} variables.ajaxBase - basic url for ajax requests
 * @param {string} variables.auth - token to authenticate user
 * @param {string} variables.dateFormat - configured format of date for this website (e.g. d.m.Y)
 * @param {string} variables.defaultGrid - JSON which contains the default schedule grid
 * @param {number} variables.departmentID - ID of selected department
 * @param {string} variables.deltaDays - amount of days deleted/moved lessons should get displayed
 * @param {string} variables.displayName - indicates whether name of page should be displayed
 * @param {string} variables.exportBase - basic url for exporting schedules
 * @param {Object.<number, Object>} variables.grids - all schedule grids with days and times
 * @param {number} variables.INSTANCE_MODE - present selected mode of saving/deleting lessons
 * @param {boolean} variables.isMobile - checks type of device
 * @param {string} variables.menuID - active menu id (used as session key)
 * @param {number} variables.PERIOD_MODE - present selected mode of saving/deleting lessons
 * @param {boolean} variables.registered - indicates whether an user is logged in
 * @param {number} variables.SEMESTER_MODE - present selected mode of saving/deleting lessons
 * @param {number} variables.showPools - whether pools are allowed to show
 * @param {number} variables.showUnpublished - whether unpublished lessons should be displayed
 * @param {string} variables.subjectDetailBase - basic url for subject details
 * @param {string} variables.username - name of currently logged in user
 */
const ScheduleApp = function (text, variables) {
	"use strict";

	let calendar, form, lessonMenu, scheduleObjects; // Get initialised in constructor
	const app = this,
		ajaxSave = new XMLHttpRequest(),
		futureDateButton = document.getElementById("future-date"),
		nextDateSelection = document.getElementById("next-date-selection"),
		noLessons = document.getElementById("no-lessons"),
		pastDateButton = document.getElementById("past-date"),
		scheduleWrapper = document.getElementById("scheduleWrapper"),
		weekdays = [
			text.MONDAY_SHORT,
			text.TUESDAY_SHORT,
			text.WEDNESDAY_SHORT,
			text.THURSDAY_SHORT,
			text.FRIDAY_SHORT,
			text.SATURDAY_SHORT,
			text.SUNDAY_SHORT
		],

		/**
		 * RegExp for date format, specified by website configuration
		 */
		datePattern = (function () {
			let pattern = variables.dateFormat;

			pattern = pattern.replace(/d/, "\\d{2}");
			pattern = pattern.replace(/j/, "\\d{1,2}");
			pattern = pattern.replace(/m/, "\\d{2}");
			pattern = pattern.replace(/n/, "\\d{1,2}");
			pattern = pattern.replace(/y/, "\\d{2}");
			pattern = pattern.replace(/Y/, "\\d{4}");
			// Escape bindings like dots
			pattern = pattern.replace(/\./g, "\\.");
			pattern = pattern.replace(/\\/g, "\\");

			return new RegExp(pattern);
		})();

	/**
	 * Calendar class for a date input field with HTMLTableElement as calendar.
	 * By choosing a date, schedules are updated.
	 */
	function Calendar() {
		let activeDate = new Date(),
			calendarIsVisible = false,
			that = this; // Helper for inner functions;
		const calendarDiv = document.getElementById("calendar"),
			month = document.getElementById("display-month"),
			months = [
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
			],
			table = document.getElementById("calendar-table"),
			year = document.getElementById("display-year");

		/**
		 * Display calendar controls like changing to previous month.
		 */
		function showControls() {
			const dateControls = document.getElementsByClassName("date-input")[0].getElementsByClassName("controls");

			for (var controlIndex = 0; controlIndex < dateControls.length; ++controlIndex)
			{
				dateControls[controlIndex].style.display = "inline";
			}
		}

		/**
		 * Displays month and year in calendar table head
		 */
		function setUpCalendarHead() {
			month.innerHTML = months[activeDate.getMonth()];
			year.innerHTML = activeDate.getFullYear().toString();
		}

		/**
		 * Deletes the rows of the calendar table for refreshing.
		 */
		function resetTable() {
			const tableBody = table.getElementsByTagName("tbody")[0],
				rowLength = table.getElementsByTagName("tr").length;

			for (var rowIndex = 0; rowIndex < rowLength; ++rowIndex)
			{
				// "-1" represents the last row
				tableBody.deleteRow(-1);
			}
		}

		/**
		 * Calendar table gets filled with days of the month, chosen by the given date
		 * Inspired by https://wiki.selfhtml.org/wiki/JavaScript/Anwendung_und_Praxis/Monatskalender
		 */
		function fillCalendar() {
			let days = 31, day = 1, rowCount;
			const generalMonth = new Date(activeDate.getFullYear(), activeDate.getMonth(), 1),
				month = activeDate.getMonth() + 1,
				months30days = [4, 6, 9, 11],
				tableBody = table.getElementsByTagName("tbody")[0],
				weekdayStart = generalMonth.getDay() || 7,
				year = activeDate.getFullYear();

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

			for (var rowIndex = 0; rowIndex <= rowCount; rowIndex++)
			{
				const row = tableBody.insertRow(rowIndex);

				for (var cellIndex = 0; cellIndex <= 6; cellIndex++)
				{
					const cell = row.insertCell(cellIndex);

					if ((rowIndex === 0 && cellIndex < weekdayStart - 1) || day > days)
					{
						cell.innerHTML = " ";
					}
					else
					{
						addInsertDateButton(new Date(year, month - 1, day), cell);
						day++;
					}
				}
			}
		}

		/**
		 * Appends one button to a table cell which inserts given date
		 * @param {Date} date
		 * @param {HTMLElement} cell
		 */
		function addInsertDateButton(date, cell) {
			const button = document.createElement("button");
			button.type = "button";
			button.className = "day";
			button.innerHTML = date.getDate().toString();
			button.addEventListener("click", function () {
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
				// day 1 for preventing get Feb 31
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
		 * @param {string} step - how big the change step is ("day"|"week"|"month")
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
			calendarDiv.style.visibility = (calendarIsVisible) ? "hidden" : "visible";
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
			calendarDiv.style.visibility = "hidden";
			calendarIsVisible = false;
		};

		/**
		 * The date chosen in the calendar table gets set in the date field
		 * @param {Date} [date]
		 */
		this.insertDate = function (date) {
			activeDate = (typeof date === "undefined") ? new Date() : date;
			app.dateField.value = activeDate.getPresentationFormat();
			window.sessionStorage.setItem("scheduleDate", activeDate.toJSON());
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
			app.dateField.addEventListener("change", that.setUpCalendar);
			document.getElementById("today").addEventListener("click", function () {
				that.insertDate();
				that.setUpCalendar();
			});
		})();
	}

	/**
	 * Schedule 'class' for saving params and update the scheduleTable
	 * @param {string} source - name of source (e.g. form-input)
	 * @param {string} [IDs] - makes together with source the schedule ID and defines the task
	 * @param {string} [optionalTitle] - optional title for directly linked schedules (e.g. teacher or room)
	 */
	function Schedule(source, IDs, optionalTitle) {
		const id = (source === "user" ? source : IDs ? source + IDs : source + getSelectedValues(source, "-")),
			resource = source,
			resourceIDs = IDs ? IDs : source === "user" ? null : getSelectedValues(source, "-"),
			that = this; // for inner helper functions
		let ajaxRequest = new XMLHttpRequest(),
			lessons = [],
			table,

			/**
			 * Sets Ajax url for updating lessons
			 */
			ajaxUrl = (function () {
				let url = getAjaxUrl();

				url += "&deltaDays=" + (source === "room" || source === "teacher" ? "0" : variables.deltaDays);
				url += "&date=" + getDateFieldString() + (variables.isMobile ? "&oneDay=true" : "");
				url += "&mySchedule=" + (resource === "user" ? "1" : "0");

				if (source !== "user")
				{
					url += "&" + resource + "IDs=" + resourceIDs;
				}

				return url;
			})(),

			/**
			 * Sets title that depends on the selected schedule
			 */
			title = (function () {
				const resourceField = document.getElementById(resource),
					programField = document.getElementById("program"),
					selection = [];

				if (optionalTitle)
				{
					return optionalTitle;
				}

				if (resource === "user")
				{
					return text.MY_SCHEDULE;
				}

				// Get pre-selected value like "Informatik Master"
				if (resource === "pool" && programField.selectedIndex !== -1)
				{
					(function () {
						const options = programField.options;
						for (var index = 0; index < options.length; ++index)
						{
							if (options[index].selected)
							{
								selection.push(options[index].text);
								return;
							}
						}
					})();
				}

				// Get resource selection like "1. Semester" or "A20.1.1"
				if (resourceField && resourceField.selectedIndex !== -1)
				{
					(function () {
						const options = resourceField.options;
						for (var index = 0; index < options.length; ++index)
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
					return selection.join(" - ");
				}

				return variables.displayName || "";
			}());

		/**
		 * Sends an Ajax request with the actual date to update the schedule
		 */
		this.requestUpdate = function () {
			ajaxUrl = ajaxUrl.replace(/(date=)\d{4}-\d{2}-\d{2}/, "$1" + getDateFieldString());
			ajaxRequest.open("GET", ajaxUrl, true);

			ajaxRequest.onreadystatechange = function () {
				if (ajaxRequest.readyState === 4 && ajaxRequest.status === 200)
				{
					/**
					 * @param {Object} response
					 * @param {Date} response.pastDate
					 * @param {Date} response.futureDate
					 */
					const response = JSON.parse(ajaxRequest.responseText);
					lessons = response;
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
							noLessons.style.display = "block";
						}
					}
				}
			};

			ajaxRequest.send();
		};

		/**
		 * updates table with already given lessons, e.g. for changing time grids
		 */
		this.updateTable = function () {
			table.update(lessons, true);
			that.popUp();
		};

		/**
		 * Creates a pop-up like div with a copy of schedule table, which is movable by user
		 * @param {boolean} [create] - create new pop-up element
		 */
		this.popUp = function (create) {
			let cancelBtn, floatDiv = document.getElementById(id + "-pop-up"), titleElement;

			if (floatDiv)
			{
				floatDiv.removeChild(floatDiv.lastChild);
				jQuery(table.getTableElement()).clone(true).appendTo(jQuery(floatDiv));
			}
			else if (create)
			{
				floatDiv = document.createElement("div");
				floatDiv.id = id + "-pop-up";
				floatDiv.className = "pop-up schedule-table";
				floatDiv.style.zIndex = getHighestZIndexForClass(".pop-up.schedule-table");
				floatDiv.draggable = true;
				/**
				 * @param {Event} event
				 * @param {DataTransfer|Object} event.dataTransfer gives data to the drop event
				 */
				floatDiv.addEventListener("dragstart", function (event) {
					const data = {"id": event.target.id, "x": event.pageX, "y": event.pageY};
					event.dataTransfer.setData("text/plain", JSON.stringify(data));
					event.dropEffect = "move";
				});
				floatDiv.addEventListener("click", function () {
					this.style.zIndex = getHighestZIndexForClass(".pop-up.schedule-table");
				});

				cancelBtn = document.createElement("button");
				cancelBtn.className = "icon-cancel";
				cancelBtn.addEventListener("click", function () {
					this.parentElement.style.display = "none";
				});
				floatDiv.appendChild(cancelBtn);

				titleElement = document.createElement("h3");
				titleElement.innerHTML = title;
				floatDiv.appendChild(titleElement);

				document.getElementsByClassName("organizer")[0].appendChild(floatDiv);
				jQuery(table.getTableElement()).clone(true).appendTo(jQuery(floatDiv));
			}

			if (create)
			{
				floatDiv.style.display = "block";
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
			addScheduleToSelection(that);
			scheduleObjects.addSchedule(that);
			handleBreakRows();
		})();
	}

	/**
	 * Class for the HTMLTableElement of a schedule
	 * @param {Schedule} schedule
	 */
	function ScheduleTable(schedule) {
		const table = document.createElement("table"), // HTMLTableElement
			userSchedule = schedule.getId() === "user",
			weekend = 7;
		let defaultGridID = null,
			lessonElements = [], // HTMLDivElements
			lessonData = {},
			/**
			 * @param {number} timeGrid.endDay - 1 for monday etc.
			 * @param {number} timeGrid.startDay - 2 for tuesday etc.
			 */
			timeGrid = JSON.parse(variables.grids[getSelectedValues("grid")].grid),
			visibleDay = getDateFieldsDateObject().getDay();

		/**
		 * Creates a table DOM-element with an input and label for selecting it and a caption with the given title.
		 * It gets appended to the scheduleWrapper.
		 */
		function createScheduleElement() {
			const input = document.createElement("input"),
				div = document.createElement("div"),
				body = document.createElement("tbody"),
				initGrid = timeGrid.hasOwnProperty("periods") ? timeGrid : JSON.parse(variables.defaultGrid),
				rowCount = Object.keys(initGrid.periods).length;

			// Create input field for selecting this schedule
			input.className = "schedule-input";
			input.type = "radio";
			input.setAttribute("id", schedule.getId() + "-input");
			input.setAttribute("name", "schedules");
			input.setAttribute("checked", "checked");
			scheduleWrapper.appendChild(input);

			// Create a new schedule table
			div.setAttribute("id", schedule.getId() + "-schedule");
			div.setAttribute("class", "schedule-table");
			div.appendChild(table);
			scheduleWrapper.appendChild(div);

			table.appendChild(body);

			for (var rowIndex = 0; rowIndex < rowCount; ++rowIndex)
			{
				// Filled with rows and cells (with -1 for last position)
				const row = body.insertRow(-1);
				for (var firstDay = 0; firstDay < weekend; ++firstDay)
				{
					row.insertCell(-1);
				}
			}
		}

		/**
		 * Insert table head and side cells with time data
		 */
		function insertTableHead() {
			const headerDate = getDateFieldsDateObject(), tr = table.createTHead().insertRow(0);

			// Set date to monday
			headerDate.setDate(headerDate.getDate() - headerDate.getDay());

			for (var headIndex = 0; headIndex < weekend; ++headIndex)
			{
				const th = document.createElement("th");

				th.innerHTML = (headIndex === 0) ? text.TIME :
					weekdays[headIndex - 1] + " (" + headerDate.getPresentationFormat() + ")";

				if (headIndex === visibleDay)
				{
					jQuery(th).addClass("activeColumn");
				}
				tr.appendChild(th);
				headerDate.setDate(headerDate.getDate() + 1);
			}
		}

		/**
		 * Sets the chosen times of the grid in the schedules tables
		 */
		function setGridTime() {
			const hasPeriods = timeGrid.hasOwnProperty("periods"),
				rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
			let period = 1;

			for (var row = 0; row < rows.length; ++row)
			{
				if (!rows[row].className.match(/break-row/))
				{
					const timeCell = rows[row].getElementsByTagName("td")[0];
					if (hasPeriods)
					{
						const startTime = timeGrid.periods[period].startTime.replace(/(\d{2})(\d{2})/, "$1:$2"),
							endTime = timeGrid.periods[period].endTime.replace(/(\d{2})(\d{2})/, "$1:$2");

						timeCell.innerHTML = startTime + "<br> - <br>" + endTime;
						timeCell.style.display = "";

						++period;
					}
					else
					{
						timeCell.style.display = "none";
					}
				}
			}
		}

		/**
		 * Here the table head changes to the grids specified weekdays with start day and end day
		 */
		function setGridDays() {
			const headItems = table.getElementsByTagName("thead")[0].getElementsByTagName("th"),
				headerDate = getDateFieldsDateObject(),
				day = headerDate.getDay();
			let currentDay = timeGrid.startDay;

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
			headItems[0].style.display = timeGrid.hasOwnProperty("periods") ? "" : "none";

			// Fill tHead with days of week
			for (var thElement = 1; thElement < headItems.length; ++thElement)
			{
				if (thElement === currentDay && currentDay <= timeGrid.endDay)
				{
					headItems[thElement].innerHTML = weekdays[currentDay - 1] +
						" (" + headerDate.getPresentationFormat(true) + ")";
					headerDate.setDate(headerDate.getDate() + 1);
					++currentDay;
				}
				else
				{
					headItems[thElement].innerHTML = "";
				}
			}
		}

		/**
		 * Inserts lessons into a schedule
		 * @param {Object} lessons
		 */
		function insertLessons(lessons) {
			const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
			let blockEnd, blockStart, blockTimes, cell, lessonElements, nextBlock, nextCell, nextRow, showOwnTime,
				tableStartTime, tableEndTime, colNumber = variables.isMobile ? visibleDay : 1;

			if (timeGrid.periods)
			{
				for (var date in lessons)
				{
					if (!lessons.hasOwnProperty(date))
					{
						continue;
					}

					// gridIndex for grid, rowIndex for rows without break
					let gridIndex = 1, rowIndex = 0;

					for (var block in lessons[date])
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
								do
								{
									--rowIndex;
								}
								while (rows[rowIndex] && rows[rowIndex].className.match(/break/));
							}
						}

						tableStartTime = timeGrid.periods[gridIndex].startTime;
						tableEndTime = timeGrid.periods[gridIndex].endTime;
						blockTimes = block.match(/^(\d{4})-(\d{4})$/);
						blockStart = blockTimes[1];
						blockEnd = blockTimes[2];

						// Block does not fit? go to next block
						while (tableEndTime <= blockStart)
						{
							do
							{
								++rowIndex;
							}
							while (rows[rowIndex] && rows[rowIndex].className.match(/break/));

							++gridIndex;
							tableStartTime = timeGrid.periods[gridIndex].startTime;
							tableEndTime = timeGrid.periods[gridIndex].endTime;
						}

						cell = rows[rowIndex].getElementsByTagName("td")[colNumber];
						if (variables.registered && schedule.getId() !== "user" &&
							isOccupiedByUserLesson(rowIndex, colNumber)
						)
						{
							jQuery(cell).addClass("occupied");
						}

						for (var lesson in lessons[date][block])
						{
							if (!lessons[date][block].hasOwnProperty(lesson))
							{
								continue;
							}

							showOwnTime = tableStartTime !== blockStart || tableEndTime !== blockEnd;
							lessonElements = createLesson(lessons[date][block][lesson], showOwnTime);
							for (var elementIndex = 0; elementIndex < lessonElements.length; ++elementIndex)
							{
								cell.appendChild(lessonElements[elementIndex]);
							}
							jQuery(cell).addClass("lessons");

							// Lesson fits into next cell too? Add a copy to this
							nextBlock = timeGrid.periods[gridIndex + 1];
							nextRow = rows[rowIndex + 1];
							if (nextRow && nextRow.className.match(/break/))
							{
								nextRow = rows[rowIndex + 2];
							}
							if (nextRow && nextBlock && blockEnd > nextBlock.startTime)
							{
								nextCell = nextRow.getElementsByTagName("td")[colNumber];
								jQuery(nextCell).addClass("lessons");
								lessonElements = createLesson(lessons[date][block][lesson], showOwnTime);
								for (var elementIndex = 0; elementIndex < lessonElements.length; ++elementIndex)
								{
									nextCell.appendChild(lessonElements[elementIndex]);
								}
							}
						}
						++gridIndex;
						// Jump over break
						do
						{
							++rowIndex;
						}
						while (rows[rowIndex] && rows[rowIndex].className.match(/break/));
					}
					++colNumber;
				}
			}
			else
			{
				insertLessonsWithoutPeriod(lessons);
			}
		}

		/**
		 * No times on the left side - every lesson appears in the first row
		 * @param {Object} lessons
		 */
		function insertLessonsWithoutPeriod(lessons) {
			const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
			let colNumber = variables.isMobile ? visibleDay : 1;

			for (var date in lessons)
			{
				if (!lessons.hasOwnProperty(date))
				{
					continue;
				}

				for (var block in lessons[date])
				{
					if (lessons[date].hasOwnProperty(block))
					{
						for (var lesson in lessons[date][block])
						{
							if (lessons[date][block].hasOwnProperty(lesson))
							{
								const lessonElements = createLesson(lessons[date][block][lesson], true);
								for (var elementIndex = 0; elementIndex < lessonElements.length; ++elementIndex)
								{
									const cell = rows[0].getElementsByTagName("td")[colNumber];
									cell.appendChild(lessonElements[elementIndex]);
								}
							}
						}
					}
				}
				++colNumber;
			}
		}

		/**
		 * Creates a lesson which means a div element filled by data
		 * @param {Object} data - lesson data
		 * @param {string} data.ccmID - id of calendar configuration mapping
		 * @param {string} data.calendarDelta - changes of calendar date/time
		 * @param {string} data.comment - some comment for the lesson
		 * @param {string} data.lessonDelta - changes of lessons
		 * @param {string} data.method - method (e.g. lecture) of a lesson
		 * @param {Object.<string, Object|string>} data.subjects - subjects of a lesson
		 * @param {string} data.startTime - lessons start time
		 * @param {string} data.endTime - lessons end time
		 * @param {boolean} [ownTime=false] - show own time
		 * @returns {HTMLDivElement[]|boolean} HTMLDivElements in an array or false in case of wrong input
		 */
		function createLesson(data, ownTime) {
			const lessons = [], scheduleID = schedule.getId(), scheduleResource = schedule.getResource();
			ownTime = typeof ownTime === "undefined" ? false : ownTime;

			for (var subject in data.subjects)
			{
				if (!data.subjects.hasOwnProperty(subject))
				{
					continue;
				}

				const lessonElement = document.createElement("div"),
					subjectData = data.subjects[subject],
					irrelevantPool = (scheduleResource === "pool" &&
						subjectData.poolDeltas[scheduleID.replace("pool", "")] === "removed");

				// Data attributes instead of classes for finding the lesson later
				lessonElement.dataset.ccmID = data.ccmID;
				lessonElement.classList.add("lesson");

				if (irrelevantPool ||
					(data.lessonDelta && data.lessonDelta === "removed") ||
					(data.calendarDelta && data.calendarDelta === "removed"))
				{
					lessonElement.classList.add("calendar-removed");
				}
				else if ((data.lessonDelta && data.lessonDelta === "new") ||
					(data.calendarDelta && data.calendarDelta === "new"))
				{
					lessonElement.classList.add("calendar-new");
				}

				if (ownTime && data.startTime && data.endTime)
				{
					const ownTimeSpan = document.createElement("span");
					ownTimeSpan.className = "own-time";
					ownTimeSpan.innerHTML =
						data.startTime.match(/^(\d{2}:\d{2})/)[1] + " - " + data.endTime.match(/^(\d{2}:\d{2})/)[1];
					lessonElement.appendChild(ownTimeSpan);
				}

				if (subjectData.name || subjectData.subjectNo)
				{
					const subjectOuterDiv = document.createElement("div");
					subjectOuterDiv.className = "subjectNameNr";
					subjectData.method = data.method || "";
					addSubjectElements(subjectOuterDiv, subjectData);
					lessonElement.appendChild(subjectOuterDiv);
				}

				if (data.comment)
				{
					const commentDiv = document.createElement("div");
					commentDiv.innerHTML = data.comment;
					commentDiv.className = "comment-container";
					lessonElement.appendChild(commentDiv);
				}

				if (scheduleResource !== "pool" && subjectData.pools && scheduleID !== "user")
				{
					const poolsOuterDiv = document.createElement("div");
					poolsOuterDiv.className = "pools";
					addDataElements("pool", poolsOuterDiv, subjectData.pools, subjectData.poolDeltas);
					lessonElement.appendChild(poolsOuterDiv);
				}

				if (scheduleResource !== "teacher" && subjectData.teachers)
				{
					const teachersOuterDiv = document.createElement("div");
					teachersOuterDiv.className = "persons";
					addDataElements("teacher", teachersOuterDiv, subjectData.teachers, subjectData.teacherDeltas, "person");
					lessonElement.appendChild(teachersOuterDiv);
				}

				if (scheduleResource !== "room" && subjectData.rooms)
				{
					const roomsOuterDiv = document.createElement("div");
					roomsOuterDiv.className = "locations";
					addDataElements("room", roomsOuterDiv, subjectData.rooms, subjectData.roomDeltas, "location");
					lessonElement.appendChild(roomsOuterDiv);
				}

				if (variables.registered)
				{
					// Makes delete button visible only
					if (userSchedule || isSavedByUser(lessonElement))
					{
						lessonElement.classList.add("added");
					}

					addContextMenu(lessonElement, subjectData);
					addActionButtons(lessonElement, subjectData);
				}
				else
				{
					lessonElement.classList.add("no-saving");
				}

				lessonElements.push(lessonElement);
				lessons.push(lessonElement);
			}

			return lessons;
		}

		/**
		 * Adds context menu to given lessonElement
		 * Right click on lesson show save/delete menu
		 * @param {HTMLElement} lesson - the html element which needs a context menu
		 * @param {Object} data - the lesson/subject data
		 */
		function addContextMenu(lesson, data) {
			lesson.addEventListener("contextmenu", function (event) {
				if (!lesson.classList.contains("calendar-removed") && !lesson.classList.contains("lesson-removed"))
				{
					event.preventDefault();
					lessonMenu.getSaveMenu(lesson, data);
				}

				if (lesson.classList.contains("added"))
				{
					event.preventDefault();
					lessonMenu.getDeleteMenu(lesson, data);
				}
			});
		}

		/**
		 * Adds buttons for saving and deleting a lesson
		 * @param {HTMLElement} lessonElement
		 * @param {Object} data
		 */
		function addActionButtons(lessonElement, data) {
			const saveDiv = document.createElement("div"),
				saveActionButton = document.createElement("button"),
				questionActionButton = document.createElement("button"),
				deleteDiv = document.createElement("div"),
				deleteActionButton = document.createElement("button");

			// Saving a lesson
			saveActionButton.className = "icon-plus";
			saveActionButton.addEventListener("click", function () {
				handleLesson(lessonElement.dataset.ccmID, variables.PERIOD_MODE, true);
			});
			questionActionButton.className = "icon-question";
			questionActionButton.addEventListener("click", function () {
				lessonMenu.getSaveMenu(lessonElement, data);
			});
			saveDiv.className = "add-lesson";
			saveDiv.appendChild(saveActionButton);
			saveDiv.appendChild(questionActionButton);
			lessonElement.appendChild(saveDiv);

			// Deleting a lesson
			deleteActionButton.className = "icon-delete";
			deleteActionButton.addEventListener("click", function () {
				handleLesson(lessonElement.dataset.ccmID, variables.PERIOD_MODE, false);
			});
			questionActionButton.addEventListener("click", function () {
				lessonMenu.getDeleteMenu(lessonElement, data);
			});
			deleteDiv.className = "delete-lesson";
			deleteDiv.appendChild(deleteActionButton);
			deleteDiv.appendChild(questionActionButton);

			lessonElement.appendChild(deleteDiv);
		}

		/**
		 * Adds DOM-elements with subject name and eventListener directing to subject details
		 * @param {HTMLElement} outerElement
		 * @param {Object} data - lesson data with subjects
		 * @param {string} data.name - name of subject
		 * @param {string} data.method - method (e.g. lecture) of a lesson
		 * @param {string} data.subjectDelta - changes of subject
		 * @param {string} data.subjectID - ID of lesson subject
		 * @param {string} data.subjectNo - number of subject
		 * @param {string} data.shortName - short name of subject for small devices
		 */
		function addSubjectElements(outerElement, data) {
			const openSubjectDetailsLink = function () {
				window.open(variables.subjectDetailBase.replace(/&id=\d+/, "&id=" + data.subjectID), "_blank");
			};

			if (data.name && data.shortName)
			{
				let subjectNameElement;

				if (data.subjectID && variables.showPools)
				{
					subjectNameElement = document.createElement("a");
					subjectNameElement.addEventListener("click", openSubjectDetailsLink);
				}
				else
				{
					subjectNameElement = document.createElement("span");
				}

				subjectNameElement.innerHTML = variables.isMobile ? data.shortName : data.name;
				subjectNameElement.innerHTML += data.method ? " - " + data.method : "";
				// Append whitespace to slashs for better word break
				subjectNameElement.innerHTML = subjectNameElement.innerHTML.replace(/(\S)\/(\S)/g, "$1 / $2");
				subjectNameElement.className = "name " + (data.subjectDelta ? data.subjectDelta : "");
				outerElement.appendChild(subjectNameElement);
			}
			if (data.subjectNo)
			{
				let subjectNumberElement;

				// Multiple spans in case of semicolon separated module number for the design
				const subjectNumbers = data.subjectNo.split(";");
				for (var numIndex = 0; numIndex < subjectNumbers.length; ++numIndex)
				{
					if (data.subjectID)
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
		}

		/**
		 * Adds HTML elements containing the given data in relation to given resource.
		 * @param {string} resource - resource to add e.g. "room" or "pool"
		 * @param {HTMLElement} outerElement - wrapper element
		 * @param {Object.<number, string>} data - lesson data
		 * @param {string} data[].gpuntisID - subject id in schedule planner program
		 * @param {Object.<number, string>} [delta] - optional, delta like "new" or "remove" assigned to (resource) id
		 * @param {string} [className] - optional, class to style the elements
		 */
		function addDataElements(resource, outerElement, data, delta, className) {
			const showX = "show" + resource.slice(0, 1).toUpperCase() + resource.slice(1) + "s";

			for (var id in data)
			{
				if (data.hasOwnProperty(id))
				{
					const span = document.createElement("span"),
						deltaClass = delta[id] || delta || "",
						nameElement = variables[showX] ? document.createElement("a") : document.createElement("span");

					span.className = (className ? className : resource) + " " + deltaClass;
					nameElement.innerHTML = data[id].gpuntisID ? data[id].gpuntisID : data[id];

					if (variables[showX])
					{
						nameElement.addEventListener("click", function () {
							sendLessonRequest(resource, id, data[id].fullName || data[id]);
						});
					}

					span.appendChild(nameElement);
					outerElement.appendChild(span);
				}
			}
		}

		/**
		 * checks for a lesson if it is already saved in the users schedule
		 * @param {HTMLElement} lesson
		 * @return {boolean}
		 */
		function isSavedByUser(lesson) {
			const userSchedule = scheduleObjects.getScheduleById("user");
			let lessons;

			if (!lesson || !userSchedule)
			{
				return false;
			}

			lessons = userSchedule.getTable().getLessons();
			for (var lessonIndex = 0; lessonIndex < lessons.length; ++lessonIndex)
			{
				if (lessons[lessonIndex].dataset.ccmID === lesson.dataset.ccmID)
				{
					return true;
				}
			}

			return false;
		}

		/**
		 * Checks for a block if the user has lessons in it already
		 * @param {number} rowIndex
		 * @param {number} colIndex
		 * @return {boolean}
		 */
		function isOccupiedByUserLesson(rowIndex, colIndex) {
			const userScheduleTable = scheduleObjects.getScheduleById("user").getTable().getTableElement(),
				rows = userScheduleTable.getElementsByTagName("tbody")[0].getElementsByTagName("tr"),
				row = rows[rowIndex],
				cell = row ? row.getElementsByTagName("td")[colIndex] : false;

			return (cell && cell.className && cell.className.match(/lessons/));
		}

		/**
		 * Removes all lessons
		 */
		function resetTable() {
			for (var index = lessonElements.length - 1; index >= 0; --index)
			{
				lessonElements[index].parentNode.className = "";
				lessonElements[index].parentNode.removeChild(lessonElements[index]);
			}

			lessonElements = [];
		}

		/**
		 * Sets only the selected day column visible for mobile devices
		 */
		function setActiveColumn() {
			const rows = table.getElementsByTagName("tr");
			let head, heads, cell, cells, row;

			for (row = 0; row < rows.length; ++row)
			{
				heads = rows[row].getElementsByTagName("th");
				for (head = 1; head < heads.length; ++head)
				{
					if (head === visibleDay)
					{
						jQuery(heads[head]).addClass("activeColumn");
					}
					else
					{
						jQuery(heads[head]).removeClass("activeColumn");
					}
				}
				cells = rows[row].getElementsByTagName("td");
				for (cell = 1; cell < cells.length; ++cell)
				{
					if (cell === visibleDay)
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

		/**
		 * Sets default gridID of schedule and select it in grid form field
		 */
		function setOwnGrid() {
			if (!defaultGridID)
			{
				// Function returns first found gridID
				defaultGridID = (function () {
					for (var day in lessonData)
					{
						if (!lessonData.hasOwnProperty(day))
						{
							continue;
						}
						for (var time in lessonData[day])
						{
							if (!lessonData[day].hasOwnProperty(time))
							{
								continue;
							}
							for (var lesson in lessonData[day][time])
							{
								if (lessonData[day][time].hasOwnProperty(lesson) && lessonData[day][time][lesson].gridID)
								{
									return lessonData[day][time][lesson].gridID;
								}
							}
						}
					}
				})();
			}

			if (defaultGridID)
			{
				setGrid(defaultGridID);
				timeGrid = JSON.parse(variables.grids[defaultGridID].grid);
				setGridTime();
			}
		}

		/**
		 * Updates the table with the actual selected time grid and given lessons.
		 * @param {Object} lessons - all lessons of a schedule
		 * @param {Object} lessons[][][].gridID - default grid id of lessons
		 * @param {boolean} [newTimeGrid]
		 */
		this.update = function (lessons, newTimeGrid) {
			lessonData = lessons;
			visibleDay = getDateFieldsDateObject().getDay();
			if (newTimeGrid)
			{
				timeGrid = JSON.parse(variables.grids[getSelectedValues("grid")].grid);
				setGridTime();
			}
			else
			{
				setOwnGrid();
			}

			resetTable();
			setGridDays();

			if (!(lessons.pastDate || lessons.futureDate))
			{
				insertLessons(lessons);
			}

			if (variables.isMobile)
			{
				setActiveColumn();
			}
		};

		/**
		 * Removes the HTMLTableElement itself and the related HTMLInputElement
		 */
		this.remove = function () {
			// input element
			scheduleWrapper.removeChild(document.getElementById(schedule.getId() + "-input"));
			// table element
			scheduleWrapper.removeChild(document.getElementById(schedule.getId() + "-schedule"));
		};

		/**
		 * Getter for HTMLDivElements which represents the lessons of this table
		 * @returns {Array}
		 */
		this.getLessons = function () {
			return lessonElements;
		};

		/**
		 * Getter for the HTMLTableElement
		 * @returns {Element}
		 */
		this.getTableElement = function () {
			return table;
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
	 * Creates a lesson menu for saving and deleting a lesson, which opens by right clicking on it
	 */
	function LessonMenu() {
		const lessonMenuElement = document.getElementsByClassName("lesson-menu")[0],
			deleteInstanceMode = document.getElementById("delete-mode-instance"),
			deleteMenu = lessonMenuElement.getElementsByClassName("delete")[0],
			deletePeriodMode = document.getElementById("delete-mode-period"),
			deleteSemesterMode = document.getElementById("delete-mode-semester"),
			descriptionSpan = lessonMenuElement.getElementsByClassName("description")[0],
			moduleSpan = lessonMenuElement.getElementsByClassName("module")[0],
			personsDiv = lessonMenuElement.getElementsByClassName("persons")[0],
			poolsDiv = lessonMenuElement.getElementsByClassName("pools")[0],
			roomsDiv = lessonMenuElement.getElementsByClassName("rooms")[0],
			saveInstanceMode = document.getElementById("save-mode-instance"),
			saveMenu = lessonMenuElement.getElementsByClassName("save")[0],
			savePeriodMode = document.getElementById("save-mode-period"),
			saveSemesterMode = document.getElementById("save-mode-semester"),
			subjectSpan = lessonMenuElement.getElementsByClassName("subject")[0];
		let currentCcmID = "0";

		/**
		 * Resets HTMLDivElements
		 */
		function resetElements() {
			removeChildren(personsDiv);
			removeChildren(roomsDiv);
			removeChildren(poolsDiv);
		}

		/**
		 * Inserts data of active lesson
		 * @param {Object} data - lesson data like subject name, persons, locations...
		 * @param {string} data.name - name of lesson subject
		 * @param {string} data.subjectNo - number of subject
		 * @param {Object} data.pools - all pools
		 * @param {Object} data.poolDeltas - changed pools
		 * @param {Object} data.rooms - all rooms
		 * @param {Object} data.roomDeltas - changed rooms
		 * @param {Object} data.teachers - all teachers
		 * @param {Object} data.teacherDeltas - changed teachers
		 */
		function setLessonData(data) {
			resetElements();
			subjectSpan.innerHTML = data.name;

			if (data.subjectNo === "")
			{
				moduleSpan.style.display = "none";
			}
			else
			{
				moduleSpan.style.display = "inline-block";
				moduleSpan.innerHTML = data.subjectNo;
			}

			descriptionSpan.innerHTML =	lessonMenuElement.parentNode.getElementsByClassName("comment-container")[0] ?
				lessonMenuElement.parentNode.getElementsByClassName("comment-container")[0].innerText : "";

			for (var teacherID in data.teachers)
			{
				if (data.teachers.hasOwnProperty(teacherID) && data.teacherDeltas[teacherID] !== "removed")
				{
					const personSpan = document.createElement("span");
					personSpan.innerHTML = data.teachers[teacherID];
					personsDiv.appendChild(personSpan);
				}
			}
			for (var roomID in data.rooms)
			{
				if (data.rooms.hasOwnProperty(roomID) && data.roomDeltas[roomID] !== "removed")
				{
					const roomSpan = document.createElement("span");
					roomSpan.innerHTML = data.rooms[roomID];
					roomsDiv.appendChild(roomSpan);
				}
			}
			for (var poolID in data.pools)
			{
				if (data.pools.hasOwnProperty(poolID))
				{
					const poolSpan = document.createElement("span");
					poolSpan.innerHTML = data.pools[poolID].gpuntisID;
					poolsDiv.appendChild(poolSpan);
				}
			}
		}

		/**
		 * Adds eventListeners to html elements
		 */
		(function () {
			saveSemesterMode.addEventListener("click", function () {
				handleLesson(currentCcmID, variables.SEMESTER_MODE, true);
				saveMenu.parentNode.style.display = "none";
			});
			savePeriodMode.addEventListener("click", function () {
				handleLesson(currentCcmID, variables.PERIOD_MODE, true);
				saveMenu.parentNode.style.display = "none";
			});
			saveInstanceMode.addEventListener("click", function () {
				handleLesson(currentCcmID, variables.INSTANCE_MODE, true);
				saveMenu.parentNode.style.display = "none";
			});
			deleteSemesterMode.addEventListener("click", function () {
				handleLesson(currentCcmID, variables.SEMESTER_MODE, false);
				deleteMenu.parentNode.style.display = "none";
			});
			deletePeriodMode.addEventListener("click", function () {
				handleLesson(currentCcmID, variables.PERIOD_MODE, false);
				deleteMenu.parentNode.style.display = "none";
			});
			deleteInstanceMode.addEventListener("click", function () {
				handleLesson(currentCcmID, variables.INSTANCE_MODE, false);
				deleteMenu.parentNode.style.display = "none";
			});
		}());

		/**
		 * Pops up at clicked lesson and sends an ajaxRequest to save lessons ccmID
		 * @param {HTMLDivElement} lessonElement
		 * @param {Object} data - lesson data like subject name, persons, locations...
		 */
		this.getSaveMenu = function (lessonElement, data) {
			currentCcmID = lessonElement.dataset.ccmID;
			saveMenu.style.display = "block";
			deleteMenu.style.display = "none";
			lessonMenuElement.style.display = "block";
			lessonElement.appendChild(lessonMenuElement);
			setLessonData(data);
		};

		/**
		 * Pops up at clicked lesson and sends an ajaxRequest to delete lessons ccmID
		 * @param {HTMLDivElement} lessonElement
		 * @param {Object} data - lesson data like subject name, persons, locations...
		 */
		this.getDeleteMenu = function (lessonElement, data) {
			currentCcmID = lessonElement.dataset.ccmID;
			saveMenu.style.display = "none";
			deleteMenu.style.display = "block";
			lessonMenuElement.style.display = "block";
			lessonElement.appendChild(lessonMenuElement);
			setLessonData(data);
		};
	}

	/**
	 * Container for all schedule objects
	 * Including functions to get the right schedule by id or response url.
	 */
	function Schedules() {
		this.schedules = []; // Schedule objects

		/**
		 * Adds a schedule to the list and set it into session storage
		 * @param {Schedule} schedule
		 */
		this.addSchedule = function (schedule) {
			let schedules = JSON.parse(window.sessionStorage.getItem("schedules"));
			const scheduleObject = {
				title: schedule.getTitle(),
				resource: schedule.getResource(),
				IDs: schedule.getResourceIDs()
			};

			// No user schedules in session. When someone is logged in, the schedule gets loaded anyway.
			if (schedule.getId() !== "user")
			{
				if (!schedules)
				{
					schedules = {};
				}

				schedules[schedule.getId()] = scheduleObject;
				window.sessionStorage.setItem("schedules", JSON.stringify(schedules));
			}

			this.schedules.push(schedule);
		};

		/**
		 * Removes a schedule and all related HTML elements
		 * @param {Schedule} schedule - the object or id of schedule
		 */
		this.removeSchedule = function (schedule) {
			const sessionSchedules = JSON.parse(window.sessionStorage.getItem("schedules"));

			delete sessionSchedules[schedule.getId()];
			window.sessionStorage.setItem("schedules", JSON.stringify(sessionSchedules));

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
			for (var scheduleIndex = 0; scheduleIndex < this.schedules.length; ++scheduleIndex)
			{
				if (this.schedules[scheduleIndex].getId() === id)
				{
					return this.schedules[scheduleIndex];
				}
			}

			return false;
		};
	}

	/**
	 * Form of selecting a schedule
	 */
	function ScheduleForm() {
		const fieldsToShow = {},
			config = {
				"name": "",
				"values": []
			},
			fields = {
				"category": document.getElementById("category"),
				"department": document.getElementById("department"),
				"pool": document.getElementById("pool"),
				"program": document.getElementById("program"),
				"roomType": document.getElementById("roomType"),
				"room": document.getElementById("room"),
				"teacher": document.getElementById("teacher")
			},
			placeholder = {
				"pool": text.POOL_PLACEHOLDER,
				"program": text.PROGRAM_PLACEHOLDER,
				"roomType": text.ROOM_TYPE_PLACEHOLDER,
				"room": text.ROOM_PLACEHOLDER,
				"teacher": text.TEACHER_PLACEHOLDER
			},
			wrappers = {
				"category": document.getElementById("category-input"),
				"department": document.getElementById("department-input"),
				"pool": document.getElementById("pool-input"),
				"program": document.getElementById("program-input"),
				"roomType": document.getElementById("roomType-input"),
				"room": document.getElementById("room-input"),
				"teacher": document.getElementById("teacher-input")
			},
			sessionFields = JSON.parse(window.sessionStorage.getItem("scheduleForm")) || {},
			sessionDepartments = JSON.parse(window.sessionStorage.getItem("scheduleDepartment")) || {};

		/**
		 * Get ajax url for selecting a form field
		 * @param {HTMLSelectElement} field - selected field
		 * @param {string} [values] - optional values to specify task
		 */
		function getFormTask(field, values) {
			const previousField = document.querySelector("[data-next=" + field.id + "]");
			let task = getAjaxUrl("get" + (field.dataset.input === "static" ? jQuery(field).val() : field.id) + "s");

			if (previousField)
			{
				task += "&" + previousField.id + "IDs=" + (values ? values : getSelectedValues(previousField.id));
			}

			return task;
		}

		/**
		 * Set an option with placeholder text after removing all options
		 * @param {HTMLSelectElement} field
		 */
		function setPlaceholder(field) {
			removeChildren(field);

			if (placeholder[field.id])
			{
				const option = document.createElement("option");
				option.setAttribute("value", "");
				option.setAttribute("disabled", "disabled");
				option.setAttribute("selected", "selected");
				option.innerHTML = placeholder[field.id];
				field.appendChild(option);
			}
		}

		/**
		 * Add an event handler for all schedule form selection elements
		 * @param {HTMLSelectElement} field
		 */
		function addSelectEventListener(field) {
			if (variables.isMobile) // no Chosen-library available
			{
				fields[field.id].addEventListener("change", handleField);
			}
			else
			{
				jQuery(field).chosen().change(handleField);

				if (field.dataset.next === "lesson")
				{
					// Select on click, even on already selected(!) options (unlike Chosens "change" event)
					wrappers[field.id].getElementsByClassName("chzn-results")[0].addEventListener("click",
						function () {
							handleField(field.id);
						}
					);
				}
			}
		}

		/**
		 * Show given field and its 'parents' (like roomtype to room) and hide rest
		 * @param {string} name - id of field
		 */
		function showField(name) {
			const selectedValue = fields[name].dataset.input === "static" ? getSelectedValues(name) : "";

			// Go through all ScheduleForm fields and show/hide them, when they are related to given field
			for (var id in fields)
			{
				if (fields.hasOwnProperty(id))
				{
					const field = fields[id];

					if (fieldsToShow[id.toLowerCase()] && (
							id === name || // Show the as param given field
							field.dataset.next === name || // Show previous field
							field.dataset.input === "static" || // Show static fields like category
							id === selectedValue // Show static fields and their selection
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
		 * Set session data to save form state, provided that the field does not fire a new schedule (lessons)
		 * @param {HTMLSelectElement} field - will be set into session storage
		 */
		function setSession(field) {
			if (field.dataset.next !== "lesson")
			{
				const session = {};
				session.name = field.id;
				session.value = getSelectedValues(field.id);

				if (field.id === "department")
				{
					sessionDepartments[variables.menuID] = session;
					window.sessionStorage.setItem("scheduleDepartment", JSON.stringify(sessionDepartments));
				}
				else
				{
					sessionFields[variables.menuID] = session;
					window.sessionStorage.setItem("scheduleForm", JSON.stringify(sessionFields));
				}
			}
		}

		/**
		 * Loads field which is set in session
		 * @return boolean - success indicator
		 */
		function loadSession() {
			const department = sessionDepartments[variables.menuID], session = sessionFields[variables.menuID];

			if (department)
			{
				jQuery("#department").val(department.value).chosen("destroy").chosen();
			}

			if (session)
			{
				if (session.name === config.name) // Prevent overwriting configuration values
				{
					sendFormRequest(session.name, session.value, config.values);
				}
				else if (fields[session.name].dataset.input === "static")
				{
					jQuery(fields[session.name]).val(session.value).chosen("destroy").chosen();

					if (fields[session.value]) // update static selected field like program
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
		function sendFormRequest(name, selectedValue, onlyValues) {
			const ajax = new XMLHttpRequest(), field = fields[name];

			ajax.open("GET", getFormTask(field, selectedValue), true);
			ajax.onreadystatechange = function () {
				let option, optionCount, response, value;

				if (ajax.readyState === 4 && ajax.status === 200)
				{
					response = JSON.parse(ajax.responseText);
					optionCount = onlyValues ? onlyValues.length : Object.keys(response).length;
					setPlaceholder(field);

					for (value in response)
					{
						if (response.hasOwnProperty(value) && (!onlyValues || onlyValues.includes(response[value])))
						{
							option = document.createElement("option");
							option.value = value.id ? value.id : response[value];
							option.innerHTML = value.name ? value.name : value;
							option.selected = (optionCount === 1 || option.value === selectedValue);
							field.appendChild(option);
						}
					}

					if (optionCount === 1 || selectedValue)
					{
						if (field.dataset.next === "lesson")
						{
							sendLessonRequest(field.id);
						}
						else
						{
							sendFormRequest(field.dataset.next);
						}
					}

					jQuery(field).chosen("destroy").chosen();
					// Because of Chosen update, options loose their eventListener after changes
					addSelectEventListener(field);
					showField(field.id);
				}
			};
			ajax.send();
		}

		/**
		 * Request for lessons or the next field will be send, depending on fields data-set
		 * @param {Event|string} field - the triggered event or id of field
		 */
		function handleField(field) {
			const element = fields[field] || fields[field.target.id];

			// Do not target placeholder
			if (element.selectedIndex !== 0)
			{
				if (element.dataset.next === "lesson")
				{
					sendLessonRequest(element.id);
					return;
				}

				if (element.dataset.input === "static")
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
		function handleFirstField() {
			let firstField, name;

			// Subjects do not have a select field, so the necessary information is simulated here
			if (config.name === 'subject')
			{
				firstField = {"id": "subject", "dataset": {"next": "lesson"}};
			}
			else
			{
				firstField = fields[config.name] || fields.category;
			}
			name = firstField.id;

			if (config.name)
			{
				if (firstField.dataset.next === "lesson")
				{
					config.values.forEach(function (value) {
						const ajaxRequest = new XMLHttpRequest(),
							titleTask = getAjaxUrl("getTitle") + "&resource=" + name + "&value=" + value;

						// Gets title per Ajax for each schedule before it gets created
						ajaxRequest.open("GET", titleTask, true);
						ajaxRequest.onreadystatechange = function () {
							if (ajaxRequest.readyState === 4 && ajaxRequest.status === 200)
							{
								sendLessonRequest(name, value, ajaxRequest.responseText);
							}
						};
						ajaxRequest.send();
					});

					disableTabs("tab-schedule-form");
				}
				else
				{
					sendFormRequest(name, "", config.values);
				}
			}
			else // First field is static (category)
			{
				sendFormRequest(getSelectedValues(name));
				showField(name);
			}
		}

		/**
		 * Reloads the next visible and flexible field of the form (for updating departmentID)
		 */
		function updateNextVisibleField() {
			const toUpdate = {"next": "", "lesson": ""};

			for (var name in fields)
			{
				if (fields.hasOwnProperty(name))
				{
					const field = fields[name],
						wrapper = jQuery(wrappers[name]);

					if (wrapper.css("display") !== "none" && field.dataset.input !== "static")
					{
						if (field.dataset.next === "lesson")
						{
							toUpdate.lesson = field.id;
						}
						else
						{
							toUpdate.next = field.id;
						}
					}
				}
			}
			// non lesson-fields have priority, but in some cases there are only lesson-fields (teacher)
			sendFormRequest(toUpdate.next || toUpdate.lesson);
		}

		/**
		 * Build the form by collecting backend configurations and handles the first field of schedule form
		 */
		(function () {
			for (var variable in variables)
			{
				if (!variables.hasOwnProperty(variable))
				{
					continue;
				}

				const idMatch = /^(\w+)*IDs$/.exec(variable),
					showMatch = /^show(\w+)s$/i.exec(variable);

				if (idMatch)
				{
					const values = variables[variable];
					config.name = idMatch[1].toLowerCase();

					// Convert values to strings, to compare them later with Ajax response
					if (jQuery.isArray(values))
					{
						for (var valueIndex = 0; valueIndex < values.length; ++valueIndex)
						{
							config.values.push("" + values[valueIndex]);
						}
					}
					else
					{
						config.values.push("" + values);
					}
				}

				if (showMatch)
				{
					fieldsToShow[showMatch[1].toLowerCase()] = variables[variable];
				}
			}

			// No configured field => category have to be visible
			fieldsToShow.category = !config.name;
			fieldsToShow.department = !config.name && !variables.departmentID;

			jQuery("#category").chosen().change(function () {
				sendFormRequest(getSelectedValues(this.id));
				setSession(this);
			});
			jQuery("#department").chosen().change(function () {
				updateNextVisibleField();
				setSession(this);
			});

			if (!loadSession())
			{
				handleFirstField();
			}
		})();
	}

	/**
	 * Get the general ajax url
	 * @param {string} [task = "getLessons"]
	 * @returns {string}
	 */
	function getAjaxUrl(task) {
		let url = "&departmentIDs=";
		url += variables.departmentID || getSelectedValues("department") || 0;
		url += "&task=" + (task ? task : "getLessons");
		url += typeof variables.showUnpublished === 'undefined' ?
			'' : "&showUnpublished=" + variables.showUnpublished;

		return variables.ajaxBase + url;
	}

	/**
	 * Loads schedules from session storage
	 */
	function loadSessionSchedules() {
		const schedules = JSON.parse(window.sessionStorage.getItem("schedules"));

		if (schedules && Object.keys(schedules).length > 0)
		{
			for (var id in schedules)
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

			showSchedule(jQuery("#selected-schedules").find(".selected-schedule").last().attr("id"));
		}
	}

	/**
	 * Select the grid of session storage
	 */
	function selectSessionGrid() {
		const grid = window.sessionStorage.getItem("scheduleGrid");

		if (grid)
		{
			setGrid(grid);
		}
	}

	/**
	 * Selects the given grid id in grid form field
	 * @param {string} id - grid id to set as selected
	 */
	function setGrid(id) {
		jQuery("#grid").val(id).chosen("destroy").chosen();
	}

	/**
	 * Starts an Ajax request to get lessons for the selected resource
	 * @param {string} resource
	 * @param {string} [id]
	 * @param {string} [title]
	 */
	function sendLessonRequest(resource, id, title) {
		const IDs = id || getSelectedValues(resource, "-");
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
	 * @param {Object} dates - dates to jump to next lesson in schedule
	 * @param {string} dates.futureDate - next date in the future
	 * @param {string} dates.pastDate - next date in the past
	 */
	function openNextDateQuestion(dates) {
		const pastDate = dates.pastDate ? new Date(dates.pastDate) : null,
			futureDate = dates.futureDate ? new Date(dates.futureDate) : null;

		nextDateSelection.style.display = "block";

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
	 * Save lesson in users personal schedule
	 * Choose between lessons of whole semester (1),
	 * just this daytime (2)
	 * or only the selected instance of a lesson (3).
	 * @param {string} ccmID - calendar_configuration_map ID
	 * @param {number} [taskNumber=1]
	 * @param {boolean} [save=true] - indicate to save or to delete the lesson
	 */
	function handleLesson(ccmID, taskNumber, save) {
		const saving = (typeof save === "undefined") ? true : save;
		let task = getAjaxUrl(saving ? "saveLesson" : "deleteLesson");

		task += "&mode=" + (taskNumber || "1") + "&ccmID=" + ccmID;
		ajaxSave.open("GET", task, true);
		ajaxSave.onreadystatechange = function () {
			if (ajaxSave.readyState === 4 && ajaxSave.status === 200)
			{
				const handledLessons = JSON.parse(ajaxSave.responseText);

				// TODO: "occupied" cells in schedule object ablegen und abfragen, statt auf Elemente zu warten, die von asynchronen requests abhängen
				scheduleObjects.schedules.forEach(function (schedule) {
					const lessonElements = schedule.getTable().getLessons();

					for (var lessonIndex = 0; lessonIndex < lessonElements.length; ++lessonIndex)
					{
						const lessonElement = lessonElements[lessonIndex];

						if (handledLessons.includes(lessonElement.dataset.ccmID))
						{
							if (saving)
							{
								lessonElement.classList.add("added");
							}
							else
							{
								lessonElement.classList.remove("added");
							}
						}
					}
				});

				app.updateSchedule();
			}
		};
		ajaxSave.send();
	}

	/**
	 * Create a new entry in the drop-down field for selecting a schedule
	 * @param {Schedule} schedule
	 */
	function addScheduleToSelection(schedule) {
		const selectedItem = document.createElement("div"),
			selectedTitle = document.createElement("button"),
			showButton = document.createElement("button");

		selectedItem.id = schedule.getId();
		selectedItem.className = "selected-schedule";
		jQuery("#selected-schedules").append(selectedItem);

		selectedTitle.className = "title";
		selectedTitle.innerHTML = schedule.getTitle();
		selectedTitle.addEventListener("click", function () {
			showSchedule(schedule.getId());
		});
		selectedItem.appendChild(selectedTitle);

		showButton.className = "show-schedule";
		showButton.innerHTML = "<span class='icon-eye-close'></span>";
		showButton.addEventListener("click", function () {
			showSchedule(schedule.getId());
		});
		selectedItem.appendChild(showButton);

		if (!variables.isMobile)
		{
			const popUpButton = document.createElement("button");
			popUpButton.className = "pop-up-schedule";
			popUpButton.innerHTML = "<span class='icon-move'></span>";
			popUpButton.addEventListener("click", function () {
				schedule.popUp(true);
			});
			selectedItem.appendChild(popUpButton);
		}

		if (schedule.getId() !== "user")
		{
			const removeButton = document.createElement("button");
			removeButton.className = "remove-schedule";
			removeButton.innerHTML = "<span class='icon-remove'></span>";
			removeButton.addEventListener("click", function () {
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
	function showSchedule(scheduleID) {
		const scheduleElements = jQuery(".schedule-input");

		for (var schedulesIndex = 0; schedulesIndex < scheduleElements.length; ++schedulesIndex)
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
	 * @returns {string|boolean}
	 */
	function getSelectedScheduleID() {
		const selectedSchedule = document.getElementById("selected-schedules").getElementsByClassName("shown")[0];

		return selectedSchedule ? selectedSchedule.id : false;
	}

	/**
	 * Remove an entry from the drop-down field for selecting a schedule
	 * @param {HTMLElement} scheduleSelectionElement - remove this element
	 * @param {Schedule} schedule - remove this object
	 */
	function removeScheduleFromSelection(scheduleSelectionElement, schedule) {
		scheduleSelectionElement.parentNode.removeChild(scheduleSelectionElement);
		scheduleObjects.removeSchedule(schedule);

		if (scheduleObjects.schedules.length === 0)
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
	 * Removes all children elements of one given parent element
	 * @param {HTMLElement} element - parent element
	 */
	function removeChildren(element) {
		const children = element.children, maxIndex = children.length - 1;

		for (var index = maxIndex; index >= 0; --index)
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
	function getSelectedValues(fieldID, separator) {
		const field = document.getElementById(fieldID),
			options = field ? field.options : undefined,
			result = [];

		if (field && field.selectedIndex > -1)
		{
			for (var index = 0; index < options.length; ++index)
			{
				if (options[index].selected)
				{
					result.push(options[index].value);
				}
			}
			return result.join(separator || ",");
		}

		return false;
	}

	/**
	 * Goes one day for- or backward in the schedules and takes the date out of the input field with 'date' as id
	 * @param {boolean} increase - goes forward with true or backward with false
	 * @param {string} [step="week"] - defines how big the step is as "day", "week" or "month"
	 */
	function changeDate(increase, step) {
		const stepString = step || "week",
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

		app.dateField.value = newDate.getPresentationFormat();
		window.sessionStorage.setItem("scheduleDate", newDate.toJSON());
	}

	/**
	 * Returns the current date field value as a string connected by minus.
	 * @returns {string}
	 */
	function getDateFieldString() {
		return app.dateField.value.replace(/(\d{2})\.(\d{2})\.(\d{4})/, "$3" + "-" + "$2" + "-" + "$1");
	}

	/**
	 * Returns a Date object by the current date field value
	 * @returns {Date}
	 */
	function getDateFieldsDateObject() {
		const parts = app.dateField.value.split(".", 3);

		if (parts)
		{
			// 12:00:00 o'clock for timezone offset
			return new Date(parseInt(parts[2], 10), parseInt(parts[1] - 1, 10), parseInt(parts[0], 10), 12, 0, 0);
		}
	}

	/**
	 * Change tab-behaviour of tabs in menu-bar, so all tabs can be closed
	 * @param {Object} clickedTab - jQuery object of tab
	 */
	function changeTabBehaviour(clickedTab) {
		if (clickedTab.parent("li").hasClass("active"))
		{
			clickedTab.parent("li").toggleClass("inactive", "");
			jQuery("#" + clickedTab.attr("data-id")).toggleClass("inactive", "");
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
	function switchToScheduleListTab() {
		const selectedSchedulesTab = jQuery("#tab-selected-schedules");

		if (!selectedSchedulesTab.parent("li").hasClass("disabled-tab"))
		{
			selectedSchedulesTab.parent("li").addClass("active");
			jQuery("#selected-schedules").addClass("active");
		}
		jQuery("#tab-schedule-form").parent("li").removeClass("active");
		jQuery("#schedule-form").removeClass("active");
	}

	/**
	 * Activates tab with a form for selecting a new schedule
	 */
	function switchToFormTab() {
		const formTab = jQuery("#tab-schedule-form");

		if (!formTab.parent("li").hasClass("disabled-tab"))
		{
			formTab.parent("li").addClass("active");
			jQuery("#schedule-form").addClass("active");
		}
		jQuery("#tab-selected-schedules").parent("li").removeClass("active");
		jQuery("#selected-schedules").removeClass("active");
	}

	/**
	 * Change position of the date-input, depending of screen-width
	 */
	function changePositionOfDateInput() {
		const mq = window.matchMedia("(max-width: 677px)");

		if (variables.isMobile)
		{
			jQuery(".date-input").insertAfter(".menu-bar");
		}

		mq.addListener(function () {
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
	 * @param {string} [tabID] - optional to disable all tabs except this
	 */
	function disableTabs(tabID) {
		const scheduleInput = jQuery(".schedule-input"),
			tabsToDisable = [
				jQuery("#tab-selected-schedules"),
				jQuery("#tab-time-selection"),
				jQuery("#tab-exports")
			];
		let i;

		if (tabID)
		{
			const allTabs = jQuery(".tabs-toggle");
			for (i = 0; i < allTabs.length; i++)
			{
				if (tabID !== allTabs[i].id)
				{
					allTabs[i].dataset.toggle = "";
					allTabs[i].parentElement.classList.add("disabled-tab");
				}
			}
		}
		// No schedule selected - disable all but schedule form
		else if (scheduleInput.length === 1 && scheduleInput.is("#default-input"))
		{
			for (i = 0; i < tabsToDisable.length; i++)
			{
				tabsToDisable[i].attr("data-toggle", "");
				tabsToDisable[i].parent("li").addClass("disabled-tab");
			}
		}
		// Activates all tabs
		else
		{
			for (i = 0; i < tabsToDisable.length; i++)
			{
				tabsToDisable[i].attr("data-toggle", "tab");
				tabsToDisable[i].parent("li").removeClass("disabled-tab");
			}
		}
	}

	/**
	 * Add or remove rows for breaks depending on time grid
	 */
	function handleBreakRows() {
		const tables = jQuery(".schedule-table"),
			numberOfColumns = variables.isMobile ? 2 : tables.find("tr:first").find("th").filter(
				function () {
					return jQuery(this).css("display") !== "none";
				}
			).length,
			addBreakRow = '<tr class="break-row"><td class="break" colspan=' + numberOfColumns + '></td></tr>',
			addLunchBreakRow = '<tr class="break-row">' + '<td class="break" colspan=' + numberOfColumns + '>' +
				text.LUNCHTIME + '</td></tr>',
			/**
			 * @param {Object.<string, number|Object>} timeGrid.periods - has all blocks with their number, start- and end times
			 */
			timeGrid = JSON.parse(variables.grids[getSelectedValues("grid")].grid);

		tables.each(function (index, table) {
			const rows = jQuery(table).find("tbody").find("tr");
			let endFirst, startSecond;

			if (!timeGrid.hasOwnProperty("periods"))
			{
				jQuery(".break").closest("tr").remove();
				rows.not(":eq(0)").addClass("hide");
				return true;
			}

			endFirst = "Mon Apr 24 2017 " + timeGrid.periods[1].endTime.replace(/(\d{2})(\d{2})/, "$1:$2");
			endFirst = new Date(endFirst);
			endFirst = endFirst.getTime();
			startSecond = "Mon Apr 24 2017 " + timeGrid.periods[2].startTime.replace(/(\d{2})(\d{2})/, "$1:$2");
			startSecond = new Date(startSecond);
			startSecond = startSecond.setSeconds(startSecond.getSeconds() - 60);

			if (endFirst === startSecond)
			{
				jQuery(".break").closest("tr").remove();
				rows.not(":eq(0)").removeClass("hide");
			}
			else if (!(rows.hasClass("break-row")))
			{
				rows.not(":eq(0)").removeClass("hide");
				for (var periods in timeGrid.periods)
				{
					if (!timeGrid.periods.hasOwnProperty(periods))
					{
						continue;
					}

					if (periods === "3")
					{
						jQuery(addLunchBreakRow).insertAfter(rows.eq(periods - 1));
					}
					else if (periods !== "6")
					{
						jQuery(addBreakRow).insertAfter(rows.eq(periods - 1));
					}
				}
			}
		});
	}

	/**
	 * EventHandler for moving schedule pop-ups over the page
	 * @param {Event} event
	 * @param {DataTransfer|Object} event.dataTransfer - drag data store
	 */
	function handleDragOver(event) {
		event.preventDefault();
		event.dataTransfer.dropEffect = "move";
	}

	/**
	 * EventHandler for dropping schedule pop-ups
	 * @param {Event} event
	 * @param {DataTransfer|Object} event.dataTransfer - drag data store
	 */
	function handleDrops(event) {
		const data = JSON.parse(event.dataTransfer.getData("text/plain")),
			element = document.getElementById(data.id),
			left = window.getComputedStyle(element).getPropertyValue("left"),
			top = window.getComputedStyle(element).getPropertyValue("top");
		let matchLeft, matchTop, oldLeft, oldTop;

		event.preventDefault();

		// Get the old style values without unit (e.g. "px")
		matchLeft = left.match(/^(-?\d+)\w*$/);
		oldLeft = matchLeft ? matchLeft[1] : 0;
		matchTop = top.match(/^(-?\d+)\w*$/);
		oldTop = matchTop ? matchTop[1] : 0;

		element.style.left = parseInt(oldLeft, 10) + parseInt(event.pageX - data.x, 10) + "px";
		element.style.top = parseInt(oldTop, 10) + parseInt(event.pageY - data.y, 10) + "px";

		// Last dragged schedule gets the highest z-index
		element.style.zIndex = getHighestZIndexForClass(".pop-up.schedule-table");
	}

	/**
	 * Returns the highest z-index of the given class elements
	 * @param {string} className
	 * @returns {number}
	 */
	function getHighestZIndexForClass(className) {
		let maxZIndex = 1;
		const elements = document.querySelectorAll(className);

		for (var index = 0; index < elements.length; ++index)
		{
			const zIndex = parseInt(window.getComputedStyle(elements[index]).getPropertyValue("z-index"));
			maxZIndex = Math.max(zIndex, maxZIndex);
		}
		return ++maxZIndex;
	}

	/**
	 * @public
	 * @type {Element}
	 */
	this.dateField = document.getElementById("date");

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
			scheduleObjects.schedules.forEach(
				function (schedule) {
					// Function called by grids eventListener
					if (id === "grid")
					{
						window.sessionStorage.setItem("scheduleGrid", getSelectedValues("grid"));
						schedule.updateTable();
						handleBreakRows();
					}
					else
					{
						schedule.requestUpdate();
					}
				}
			);
		}
	};

	/**
	 * The date field gets the selected date, schedules get updates and the selection-div-element is hidden again
	 * @param {Event} event - Event that triggers this function
	 */
	this.nextDateEventHandler = function (event) {
		const date = new Date(event.target.dataset.date);
		this.dateField.value = date.getPresentationFormat();
		window.sessionStorage.setItem("scheduleDate", date.toJSON());
		nextDateSelection.style.display = "none";
		this.updateSchedule();
	};

	/**
	 * Opens export window of selected schedule
	 * @param {string} format
	 */
	this.handleExport = function (format) {
		const exportSelection = jQuery("#export-selection"),
			schedule = getSelectedScheduleID(),
			formats = format.split(".");
		let url = variables.exportBase;

		url += "&format=" + formats[0];

		if (formats[0] === "pdf")
		{
			url += "&gridID=" + variables.grids[getSelectedValues("grid")].id;
		}

		url += typeof variables.showUnpublished === 'undefined' ?
			'' : "&showUnpublished=" + variables.showUnpublished;

		if (formats[1] !== undefined)
		{
			url += "&documentFormat=" + formats[1];
		}

		url += "&username=" + variables.username + "&auth=" + variables.auth;

		if (schedule === "user")
		{
			url += "&myschedule=1";
		}
		else
		{
			const resourceID = schedule.match(/[0-9]+/);

			if (resourceID === null)
			{
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
				return;
			}
		}

		if (formats[0] === "ics")
		{
			url += "&username=" + variables.username + "&auth=" + variables.auth;
			window.prompt(text.COPY, url);
			exportSelection.val("placeholder");
			exportSelection.trigger("chosen:updated");
			return;
		}

		url += "&date=" + getDateFieldString();

		window.open(url);
		exportSelection.val("placeholder");
		exportSelection.trigger("chosen:updated");
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
			dayLong = day < 10 ? "0" + day : day,
			month = this.getMonth() + 1, // getMonth() is zero-based
			monthLong = month < 10 ? "0" + month : month,
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
		if (typeof shortYear === "undefined" ? false : shortYear)
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
	 * "Constructor"
	 * Adds EventListener and initialise menus, schedules, tabs, calendar and form
	 */
	(function () {
		let startX, startY;
		const sessionDate = window.sessionStorage.getItem("scheduleDate"),
			date = sessionDate ? new Date(sessionDate) : new Date();

		app.dateField.value = date.getPresentationFormat();
		calendar = new Calendar();
		lessonMenu = new LessonMenu();
		scheduleObjects = new Schedules();
		form = new ScheduleForm();

		if (variables.registered && !scheduleObjects.getScheduleById("user"))
		{
			new Schedule("user");
			switchToScheduleListTab();
		}

		changePositionOfDateInput();
		disableTabs();
		selectSessionGrid();
		loadSessionSchedules();

		/**
		 * Swipe touch event handler changing the shown day and date
		 * @see http://www.javascriptkit.com/javatutors/touchevents.shtml
		 * @see http://www.html5rocks.com/de/mobile/touch/
		 */
		scheduleWrapper.addEventListener("touchstart", function (event) {
			const touch = event.changedTouches[0];
			startX = parseInt(touch.pageX, 10);
			startY = parseInt(touch.pageY, 10);
		}, {passive: true}); // To say the browser, that we not 'prevent default' (and suppress warnings)
		scheduleWrapper.addEventListener("touchend", function (event) {
			const touch = event.changedTouches[0],
				distX = parseInt(touch.pageX, 10) - startX,
				distY = parseInt(touch.pageY, 10) - startY,
				minDist = 50;

			if (Math.abs(distX) > Math.abs(distY))
			{
				if (distX < -(minDist))
				{
					event.stopPropagation();
					changeDate(true, variables.isMobile ? "day" : "week");
					app.updateSchedule();
				}
				if (distX > minDist)
				{
					event.stopPropagation();
					changeDate(false, variables.isMobile ? "day" : "week");
					app.updateSchedule();
				}
			}
		});
		jQuery("#schedules").chosen().change(function () {
			const scheduleInput = document.getElementById(jQuery("#schedules").val());

			// To show the schedule after this input field (by css)
			scheduleInput.checked = "checked";
		});
		// Change Tab-Behaviour of menu-bar, so all tabs can be closed
		jQuery(".tabs-toggle").on("click", function (event) {
			changeTabBehaviour(jQuery(this));

			//prevent loading of tabs-url:
			event.preventDefault();
		});

		// Drag'n'drop effect for schedule pop-ups
		document.getElementById("main").addEventListener("drop", handleDrops);
		document.getElementById("main").addEventListener("dragover", handleDragOver);
	})();

	/**
	 * Context-menu-popup, calendar-popup and message-popup will be closed when clicking outside this
	 */
	jQuery(document).mouseup(function (e) {
		const calendarPopup = jQuery("#calendar"),
			messagePopup = jQuery(".message.pop-up"),
			popup = jQuery(".lesson-menu");

		if (!popup.is(e.target) && popup.has(e.target).length === 0)
		{
			popup.hide(0);
		}

		if (!messagePopup.is(e.target) && messagePopup.has(e.target).length === 0)
		{
			messagePopup.hide(0);
		}

		if (jQuery(".controls").css("display") !== "none")
		{
			if (calendar.isVisible())
			{
				if (!calendarPopup.is(e.target) && calendarPopup.has(e.target).length === 0)
				{
					calendar.hideCalendar();
				}
			}
		}
	});
};