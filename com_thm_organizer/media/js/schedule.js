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

jQuery(document).ready(function ()
{
	window.scheduleApp = new ScheduleApp();
});

var ScheduleApp = function ()
{
	/**
	 * @private
	 */
	var app = this,
		ajaxSave = new XMLHttpRequest(),
		ajaxSelection = new XMLHttpRequest(),
		calendar, lessonMenu, scheduleObjects, // Get initialised in constructor
		futureDateButton = document.getElementById("future-date"),
		inputFields = document.querySelectorAll("[data-input-kind='flexible']"),
		nextDateSelection = document.getElementById("next-date-selection"),
		noLessons = document.getElementById("no-lessons"),
		pastDateButton = document.getElementById("past-date"),
		placeholder = {
			"pool": text.POOL_PLACEHOLDER,
			"program": text.PROGRAM_PLACEHOLDER,
			"room": text.ROOM_PLACEHOLDER,
			"roomtype": text.ROOMTYPE_PLACEHOLDER,
			"teacher": text.TEACHER_PLACEHOLDER
		},
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
		datePattern = (function ()
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

			return new RegExp(pattern);
		})(),

		/**
		 * Calendar class for a date input field with HTMLTableElement as calendar.
		 * By choosing a date, schedules are updated.
		 */
		Calendar = function ()
		{
			var that = this, // Helper for inner functions
				calendarDiv = document.getElementById("calendar"),
				calendarIsVisible = false,
				activeDate = new Date(),
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
				year = document.getElementById("display-year"),

				/**
				 * Display calendar controls like changing to previous month.
				 */
				showControls = function ()
				{
					var dateControls = document.getElementsByClassName("date-input")[0].getElementsByClassName("controls");

					for (var controlIndex = 0; controlIndex < dateControls.length; ++controlIndex)
					{
						dateControls[controlIndex].style.display = "inline";
					}
				},

				/**
				 * Displays month and year in calendar table head
				 */
				setUpCalendarHead = function ()
				{
					month.innerHTML = months[activeDate.getMonth()];
					year.innerHTML = activeDate.getFullYear().toString();
				},

				/**
				 * Deletes the rows of the calendar table for refreshing.
				 */
				resetTable = function ()
				{
					var tableBody = table.getElementsByTagName("tbody")[0],
						rowLength = table.getElementsByTagName("tr").length;

					for (var rowIndex = 0; rowIndex < rowLength; ++rowIndex)
					{
						// "-1" represents the last row
						tableBody.deleteRow(-1);
					}
				},

				/**
				 * Calendar table gets filled with days of the month, chosen by the given date
				 */
				fillCalendar = function ()
				{
					// Inspired by https://wiki.selfhtml.org/wiki/JavaScript/Anwendung_und_Praxis/Monatskalender
					var tableBody = table.getElementsByTagName("tbody")[0],
						rows, rowIndex, row, cell, cellIndex, months30days = [4, 6, 9, 11], days = 31, day = 1,
						generalMonth = new Date(activeDate.getFullYear(), activeDate.getMonth(), 1),
						weekdayStart = generalMonth.getDay() === 0 ? 7 : generalMonth.getDay(),
						month = activeDate.getMonth() + 1,
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
					rows = Math.min(Math.ceil((days + generalMonth.getDay() - 1) / 7), 6);

					for (rowIndex = 0; rowIndex <= rows; rowIndex++)
					{
						row = tableBody.insertRow(rowIndex);
						for (cellIndex = 0; cellIndex <= 6; cellIndex++)
						{
							cell = row.insertCell(cellIndex);
							if ((rowIndex === 0 && cellIndex < weekdayStart - 1) || day > days)
							{
								cell.innerHTML = " ";
							}
							else
							{
								// Closure function needed, to give individual params to eventListeners inside of a for-loop
								(function (day)
								{
									var button = document.createElement("button");
									button.type = "button";
									button.className = "day";
									button.innerHTML = day.toString();
									button.addEventListener("click", function ()
									{
										that.insertDate(new Date(year, month - 1, day));
									}, false);
									cell.appendChild(button);
								}(day));

								day++;
							}
						}
					}
				};

			/**
			 * Increase or decrease displayed month in calendar table.
			 *
			 * @param {boolean} [increaseMonth=true]
			 */
			this.changeCalendarMonth = function (increaseMonth)
			{
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
			 *
			 * @param {boolean} increase - increase or decrease
			 * @param {string} step - how big the change step is ("day"|"week"|"month")
			 */
			this.changeSelectedDate = function (increase, step)
			{
				changeDate(increase, step);
				app.updateSchedule();

				if (calendarIsVisible)
				{
					this.setUpCalendar();
				}

				window.sessionStorage.setItem("scheduleDate", getDateFieldsDateObject().toJSON());
			};

			/**
			 * Hides or shows the calendar, depending on its previous status.
			 */
			this.showCalendar = function ()
			{
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
			this.hideCalendar = function ()
			{
				calendarDiv.style.visibility = "hidden";
				calendarIsVisible = false;
			};

			/**
			 * The date chosen in the calendar table gets set in the date field
			 *
			 * @param {Date} date
			 */
			this.insertDate = function (date)
			{
				activeDate = (typeof date === "undefined") ? new Date() : date;
				app.dateField.value = activeDate.getPresentationFormat();

				this.hideCalendar();
				app.updateSchedule();
			};

			/**
			 * Builds the calendar (table), depending on a given date or the date field.
			 */
			this.setUpCalendar = function ()
			{
				resetTable();
				setUpCalendarHead();
				fillCalendar();
			};

			/**
			 * Getter for visibility of this calendar
			 * @returns {boolean}
			 */
			this.isVisible = function ()
			{
				return calendarIsVisible;
			};

			/**
			 * This function is called immediately after creating a new Calendar.
			 * Sets eventListeners for HTML-elements and variables.
			 */
			(function ()
			{
				that.activeDate = getDateFieldsDateObject();
				showControls();

				app.dateField.addEventListener("change", that.setUpCalendar);
				document.getElementById("today").addEventListener("click", function ()
				{
					that.insertDate();
					that.setUpCalendar();
				});
			})();
		},

		/**
		 * Schedule 'class' for saving params and update the scheduleTable
		 *
		 * @param {string} source - name of source (e.g. form-input)
		 * @param {string} [IDs] - makes together with source the schedule ID and defines the task
		 * @param {string} [optionalTitle] - optional title for directly linked schedules (e.g. teacher or room)
		 */
		Schedule = function (source, IDs, optionalTitle)
		{
			// for inner helper functions
			var that = this,
				ajaxRequest = new XMLHttpRequest(),
				id = source === "user" ? source : IDs ? source + IDs : source + getSelectedValues(source, "-"),
				lessons = [],
				resource = source,
				resourceIDs = IDs ? IDs : source === "user" ? null : getSelectedValues(source, "-"),
				table,

				/**
				 * Sets Ajax url for updating lessons
				 */
				task = (function ()
				{
					var task = "&departmentIDs=" + (variables.departmentID !== "0" ? variables.departmentID :
							getSelectedValues("department") ? getSelectedValues("department") : "0");

					task += variables.deltaDays ? "&deltaDays=" + variables.deltaDays : "";
					task += "&task=getLessons";
					task += "&date=" + getDateFieldString() + (variables.isMobile ? "&oneDay=true" : "");
					task += "&mySchedule=" + (resource === "user" ? "1" : "0");

					if (source !== "user")
					{
						task += "&" + resource + "IDs=" + resourceIDs;
					}

					return task;
				})(),

				/**
				 * Sets title that depends on the selected schedule
				 */
				title = (function ()
				{
					var title = variables.displayName ? variables.displayName : "",
						resourceField = document.getElementById(resource),
						programField = document.getElementById("program"), index, options, selection = [];

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
						(function ()
						{
							options = programField.options;
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

					// Get resource selection like "1. Semester" or "A20.1.1"
					if (resourceField && resourceField.selectedIndex !== -1)
					{
						(function ()
						{
							options = resourceField.options;
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

					title += selection.join(" - ");

					return title;
				}());

			/**
			 * Sends an Ajax request with the actual date to update the schedule
			 */
			this.requestUpdate = function ()
			{
				task = task.replace(/(date=)\d{4}\-\d{2}\-\d{2}/, "$1" + getDateFieldString());
				ajaxRequest.open("GET", variables.ajaxbase + task, true);

				ajaxRequest.onreadystatechange = function ()
				{
					if (ajaxRequest.readyState === 4 && ajaxRequest.status === 200)
					{
						var response = JSON.parse(ajaxRequest.responseText);
						lessons = response;
						table.update(response);

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

				ajaxRequest.send(null);
			};

			/**
			 * updates table with already given lessons, e.g. for changing time grids
			 */
			this.updateTable = function ()
			{
				table.update(lessons, true);
			};

			/**
			 * Getter for id of schedule
			 * @returns {string}
			 */
			this.getId = function ()
			{
				return id;
			};

			/**
			 * Getter for the IDs of the resource
			 * @returns {string}
			 */
			this.getProgramID = function ()
			{
				return resource === "pool" ? getSelectedValues("program") : null;
			};

			/**
			 * Getter for resource of schedule
			 * @returns {string}
			 */
			this.getResource = function ()
			{
				return resource;
			};

			/**
			 * Getter for the IDs of the resource
			 * @returns {string}
			 */
			this.getResourceIDs = function ()
			{
				return resourceIDs;
			};

			/**
			 * Getter for title of schedule
			 * @returns {string}
			 */
			this.getTitle = function ()
			{
				return title;
			};

			/**
			 * Getter for the ScheduleTable related with this schedule
			 * @returns {ScheduleTable}
			 */
			this.getTable = function ()
			{
				return table;
			};

			/**
			 * constructor-like function
			 */
			(function ()
			{
				table = new ScheduleTable(that);
				that.requestUpdate();
				addScheduleToSelection(that);
				scheduleObjects.addSchedule(that);
				handleBreakRows();
			})();
		},

		/**
		 * Class for the HTMLTableElement of a schedule
		 *
		 * @param {Schedule} schedule
		 */
		ScheduleTable = function (schedule)
		{
			var lessonElements = [], // HTMLDivElements
				scheduleObject = schedule,
				table = document.createElement("table"), // HTMLTableElement
				timeGrid = JSON.parse(variables.grids[getSelectedValues("grid")].grid),
				userSchedule = schedule.getId() === "user",
				visibleDay = getDateFieldsDateObject().getDay(),

				/**
				 * Creates a table DOM-element with an input and label for selecting it and a caption with the given title.
				 * It gets appended to the scheduleWrapper.
				 */
				createScheduleElement = function ()
				{
					var input, div, body, row, initGrid, period, firstDay, weekEnd = 7;

					// Create input field for selecting this schedule
					input = document.createElement("input");
					input.className = "schedule-input";
					input.type = "radio";
					input.setAttribute("id", schedule.getId() + "-input");
					input.setAttribute("name", "schedules");
					input.setAttribute("checked", "checked");
					scheduleWrapper.appendChild(input);

					// Create a new schedule table
					div = document.createElement("div");
					div.setAttribute("id", schedule.getId() + "-schedule");
					div.setAttribute("class", "schedule-table");
					div.appendChild(table);
					scheduleWrapper.appendChild(div);

					body = document.createElement("tbody");
					table.appendChild(body);

					// Filled with rows and cells (with -1 for last position)
					initGrid = timeGrid.hasOwnProperty("periods") ? timeGrid : variables.defaultGrid;
					for (period in initGrid.periods)
					{
						row = body.insertRow(-1);
						for (firstDay = 0; firstDay < weekEnd; ++firstDay)
						{
							row.insertCell(-1);
						}
					}
				},

				/**
				 * Insert table head and side cells with time data
				 */
				insertTableHead = function ()
				{
					var tHead = table.createTHead(), tr = tHead.insertRow(0), weekend = 7, th, thText,
						headerDate = getDateFieldsDateObject(), headIndex;

					// Set date to monday
					headerDate.setDate(headerDate.getDate() - headerDate.getDay());

					for (headIndex = 0; headIndex < weekend; ++headIndex)
					{
						th = document.createElement("th");
						thText = weekdays[headIndex - 1] + " (" + headerDate.getPresentationFormat() + ")";
						th.innerHTML = (headIndex === 0) ? text.TIME : thText;
						if (headIndex === visibleDay)
						{
							jQuery(th).addClass("activeColumn");
						}
						tr.appendChild(th);
						headerDate.setDate(headerDate.getDate() + 1);
					}
				},

				/**
				 * sets the chosen times of the grid in the schedules tables
				 */
				setGridTime = function ()
				{
					var rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr"),
						hasPeriods = timeGrid.hasOwnProperty("periods"), period = 1, timeCell, startTime, endTime;

					for (var row = 0; row < rows.length; ++row)
					{
						if (!rows[row].className.match(/break-row/))
						{
							timeCell = rows[row].getElementsByTagName("td")[0];
							if (hasPeriods)
							{
								startTime = timeGrid.periods[period].startTime;
								startTime = startTime.replace(/(\d{2})(\d{2})/, "$1:$2");
								endTime = timeGrid.periods[period].endTime;
								endTime = endTime.replace(/(\d{2})(\d{2})/, "$1:$2");
								timeCell.style.display = "";
								timeCell.innerHTML = startTime + "<br> - <br>" + endTime;

								++period;
							}
							else
							{
								timeCell.style.display = "none";
							}
						}
					}
				},

				/**
				 * here the table head changes to the grids specified weekdays with start day and end day
				 */
				setGridDays = function ()
				{
					var currentDay = timeGrid.startDay, endDay = timeGrid.endDay,
						headerDate = getDateFieldsDateObject(), day = headerDate.getDay(), thElement,
						head = table.getElementsByTagName("thead")[0], headItems = head.getElementsByTagName("th");

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
					for (thElement = 1; thElement < headItems.length; ++thElement)
					{
						if (thElement === currentDay && currentDay <= endDay)
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
				},

				/**
				 * inserts lessons into a schedule
				 *
				 * @param {Object} lessons
				 */
				insertLessons = function (lessons)
				{
					var colNumber = variables.isMobile ? visibleDay : 1,
						rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr"), rowIndex,
						block, lesson, tableStartTime, tableEndTime, blockTimes, lessonElements, gridIndex, blockStart, blockEnd,
						cell, nextCell, nextBlock, nextRow, showOwnTime;

					if (timeGrid.periods)
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

								tableStartTime = timeGrid.periods[gridIndex].startTime;
								tableEndTime = timeGrid.periods[gridIndex].endTime;
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

								for (lesson in lessons[date][block])
								{
									if (!lessons[date][block].hasOwnProperty(lesson))
									{
										continue;
									}

									showOwnTime = tableStartTime !== blockStart || tableEndTime !== blockEnd;
									lessonElements = createLesson(lessons[date][block][lesson], showOwnTime);
									lessonElements.forEach(function (element)
									{
										cell.appendChild(element);
									});
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
						insertLessonsWithoutPeriod(lessons);
					}
				},

				/**
				 * No times on the left side - every lesson appears in the first row
				 *
				 * @param {Object} lessons
				 */
				insertLessonsWithoutPeriod = function (lessons)
				{
					var colNumber = variables.isMobile ? visibleDay : 1,
						rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr"),
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
											lessonElements = createLesson(lessons[date][block][lesson], true);
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
				},

				/**
				 * Creates a lesson which means a div element filled by data
				 *
				 * @param {Object} data - lesson data
				 * @param {boolean} ownTime - show own time
				 *
				 * @returns {Array|boolean} HTMLDivElements in an array or false in case of wrong input
				 */
				createLesson = function (data, ownTime)
				{
					var added = false, commentDiv, irrelevantPool, lessonElement, lessons, ownTimeSpan, poolsOuterDiv,
						roomsOuterDiv, scheduleID = schedule.getId(), scheduleResource = schedule.getResource(),
						subject, subjectOuterDiv, subjectData, teachersOuterDiv;

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

						irrelevantPool = scheduleResource === "pool" && subjectData.poolDeltas[scheduleID.replace("pool", "")] === "removed";

						if (irrelevantPool || (data.lessonDelta && data.lessonDelta === "removed") || (data.calendarDelta && data.calendarDelta === "removed"))
						{
							lessonElement.classList.add("calendar-removed");
						}

						// Delta = "removed" or "new" or "changed" ? add class like "lesson-new"
						else if ((data.lessonDelta && data.lessonDelta === "new") || (data.calendarDelta && data.calendarDelta === "new"))
						{
							lessonElement.classList.add("calendar-new");
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

							addSubjectElements(subjectOuterDiv, subjectData);
							lessonElement.appendChild(subjectOuterDiv);
						}

						subjectData.comment = (data.comment ? data.comment : "");

						if (subjectData.comment)
						{
							commentDiv = document.createElement("div");
							commentDiv.innerHTML = subjectData.comment;
							commentDiv.className = "comment-container";
							lessonElement.appendChild(commentDiv);
						}

						if (scheduleResource !== "pool" && subjectData.pools && scheduleID !== "user")
						{
							poolsOuterDiv = document.createElement("div");
							poolsOuterDiv.className = "pools";
							addDataElements("pool", poolsOuterDiv, subjectData.pools, subjectData.poolDeltas);
							lessonElement.appendChild(poolsOuterDiv);
						}

						if (scheduleResource !== "teacher" && subjectData.teachers)
						{
							teachersOuterDiv = document.createElement("div");
							teachersOuterDiv.className = "persons";
							addDataElements("teacher", teachersOuterDiv, subjectData.teachers, subjectData.teacherDeltas, "person");
							lessonElement.appendChild(teachersOuterDiv);
						}

						if (scheduleResource !== "room" && subjectData.rooms)
						{
							roomsOuterDiv = document.createElement("div");
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
				},

				/**
				 * Adds context menu to given lessonElement
				 * Right click on lesson show save/delete menu
				 *
				 * @param {HTMLDivElement} lessonElement - the html element which needs a context menu
				 * @param {Array} data - the lesson/subject data
				 */
				addContextMenu = function (lessonElement, data)
				{
					var lesson = lessonElement;

					lesson.addEventListener("contextmenu", function (event)
					{
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
				},

				/**
				 * Adds buttons for saving and deleting a lesson
				 *
				 * @param {HTMLDivElement} lessonElement
				 * @param {Object} data
				 */
				addActionButtons = function (lessonElement, data)
				{
					var saveDiv, saveActionButton, questionActionButton, deleteDiv, deleteActionButton;

					// Saving a lesson
					saveActionButton = document.createElement("button");
					saveActionButton.className = "icon-plus";
					saveActionButton.addEventListener("click", function ()
					{
						handleLesson(variables.PERIOD_MODE, lessonElement.dataset.ccmID, true);
					});
					questionActionButton = document.createElement("button");
					questionActionButton.className = "icon-question";
					questionActionButton.addEventListener("click", function ()
					{
						lessonMenu.getSaveMenu(lessonElement, data);
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
						handleLesson(variables.PERIOD_MODE, lessonElement.dataset.ccmID, false);
					});
					questionActionButton = document.createElement("button");
					questionActionButton.className = "icon-question";
					questionActionButton.addEventListener("click", function ()
					{
						lessonMenu.getDeleteMenu(lessonElement, data);
					});
					deleteDiv = document.createElement("div");
					deleteDiv.className = "delete-lesson";
					deleteDiv.appendChild(deleteActionButton);
					deleteDiv.appendChild(questionActionButton);

					lessonElement.appendChild(deleteDiv);
				},

				/**
				 * Adds DOM-elements with eventListener directing to subject details, when there are some, to given outer element
				 *
				 * @param {HTMLDivElement} outerElement
				 * @param {Object} data - lessonData with subjects
				 */
				addSubjectElements = function (outerElement, data)
				{
					var subjectLinkID, openSubjectDetailsLink, planProgramID, programID,
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
								if (data.programs[subjectID][programID]["planProgramID"] === scheduleObject.getProgramID())
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
				},

				/**
				 * Adds HTML elements containing the given data in relation to given resource.
				 *
				 * @param {string} resource - resource to add e.g. "room" or "pool"
				 * @param {HTMLElement} outerElement - wrapper element
				 * @param {Object} data - lesson data
				 * @param {Object|string} [delta] - optional, delta like "new" or "remove"
				 * @param {string} [className] - optional, class to style the elements
				 */
				addDataElements = function (resource, outerElement, data, delta, className)
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
				},

				/**
				 * checks for a lesson if it is already saved in the users schedule
				 *
				 * @param {HTMLElement} lesson
				 * @return {boolean}
				 */
				isSavedByUser = function (lesson)
				{
					var userSchedule = scheduleObjects.getScheduleById("user"), lessons;

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
				},

				/**
				 * Checks for a block if the user has lessons in it already
				 *
				 * @param {number} rowIndex
				 * @param {number} colIndex
				 * @return {boolean}
				 */
				isOccupiedByUserLesson = function (rowIndex, colIndex)
				{
					var userScheduleTable = scheduleObjects.getScheduleById("user").getTable().getTableElement(),
						rows = userScheduleTable.getElementsByTagName("tbody")[0].getElementsByTagName("tr"),
						row = rows[rowIndex],
						cell = row ? row.getElementsByTagName("td")[colIndex] : false;

					return (cell && cell.className && cell.className.match(/lessons/));
				},

				/**
				 * Removes all lessons
				 */
				resetTable = function ()
				{
					var lessons = lessonElements;

					for (var index = lessons.length - 1; index >= 0; --index)
					{
						lessons[index].parentNode.className = "";
						lessons[index].parentNode.removeChild(lessons[index]);
					}

					lessonElements = [];
				},

				/**
				 * Sets only the selected day column visible for mobile devices
				 */
				setActiveColumn = function ()
				{
					var heads, cells, rows = table.getElementsByTagName("tr");

					for (var row = 0; row < rows.length; ++row)
					{
						heads = rows[row].getElementsByTagName("th");
						for (var head = 1; head < heads.length; ++head)
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
						for (var cell = 1; cell < cells.length; ++cell)
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
				};

			/**
			 * updates the table with the actual selected time grid and given lessons.
			 *
			 * @param {Object} lessons
			 * @param {boolean} newTimeGrid
			 */
			this.update = function (lessons, newTimeGrid)
			{
				visibleDay = getDateFieldsDateObject().getDay();
				if (newTimeGrid)
				{
					timeGrid = JSON.parse(variables.grids[getSelectedValues("grid")].grid);
					setGridTime();
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
			this.remove = function ()
			{
				// input element
				scheduleWrapper.removeChild(document.getElementById(schedule.getId() + "-input"));
				// table element
				scheduleWrapper.removeChild(document.getElementById(schedule.getId() + "-schedule"));
			};

			/**
			 * Getter for HTMLDivElements which represents the lessons of this table
			 * @returns {Array}
			 */
			this.getLessons = function ()
			{
				return lessonElements;
			};

			/**
			 * Getter for the HTMLTableElement
			 * @returns {Element}
			 */
			this.getTableElement = function ()
			{
				return table;
			};

			/**
			 * constructor-like function to build the HTMLTableElement
			 */
			(function ()
			{
				createScheduleElement();
				insertTableHead();
				setGridTime();
			}());
		},

		/**
		 * Creates a lesson menu for saving and deleting a lesson, which opens by right clicking on it
		 */
		LessonMenu = function ()
		{
			var currentCcmID = 0,
				lessonMenuElement = document.getElementsByClassName("lesson-menu")[0],
				subjectSpan = lessonMenuElement.getElementsByClassName("subject")[0],
				moduleSpan = lessonMenuElement.getElementsByClassName("module")[0],
				personsDiv = lessonMenuElement.getElementsByClassName("persons")[0],
				roomsDiv = lessonMenuElement.getElementsByClassName("rooms")[0],
				poolsDiv = lessonMenuElement.getElementsByClassName("pools")[0],
				descriptionSpan = lessonMenuElement.getElementsByClassName("description")[0],
				saveMenu = lessonMenuElement.getElementsByClassName("save")[0],
				saveSemesterMode = document.getElementById("save-mode-semester"),
				savePeriodMode = document.getElementById("save-mode-period"),
				saveInstanceMode = document.getElementById("save-mode-instance"),
				deleteMenu = lessonMenuElement.getElementsByClassName("delete")[0],
				deleteSemesterMode = document.getElementById("delete-mode-semester"),
				deletePeriodMode = document.getElementById("delete-mode-period"),
				deleteInstanceMode = document.getElementById("delete-mode-instance"),

				/**
				 * Resets HTMLDivElements
				 */
				resetElements = function ()
				{
					removeChildren(personsDiv);
					removeChildren(roomsDiv);
					removeChildren(poolsDiv);
				},

				/**
				 * Inserts data of active lesson
				 *
				 * @param {Object} data - lesson data like subject name, persons, locations...
				 */
				setLessonData = function (data)
				{
					var teacherID, personSpan, roomID, roomSpan, poolID, poolSpan;

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

					if (lessonMenuElement.parentNode.getElementsByClassName("comment-container")[0] !== undefined)
					{
						descriptionSpan.innerHTML =
							lessonMenuElement.parentNode.getElementsByClassName("comment-container")[0].innerText;
					}
					else
					{
						descriptionSpan.innerHTML = "";
					}

					for (teacherID in data.teachers)
					{
						if (data.teachers.hasOwnProperty(teacherID) && data.teacherDeltas[teacherID] !== "removed")
						{
							personSpan = document.createElement("span");
							personSpan.innerHTML = data.teachers[teacherID];
							personsDiv.appendChild(personSpan);
						}
					}
					for (roomID in data.rooms)
					{
						if (data.rooms.hasOwnProperty(roomID) && data.roomDeltas[roomID] !== "removed")
						{
							roomSpan = document.createElement("span");
							roomSpan.innerHTML = data.rooms[roomID];
							roomsDiv.appendChild(roomSpan);
						}
					}
					for (poolID in data.pools)
					{
						if (data.pools.hasOwnProperty(poolID))
						{
							poolSpan = document.createElement("span");
							poolSpan.innerHTML = data.pools[poolID].gpuntisID;
							poolsDiv.appendChild(poolSpan);
						}
					}
				};

			/**
			 * Adds eventListeners to html elements
			 */
			(function ()
			{
				saveSemesterMode.addEventListener("click", function ()
				{
					handleLesson(variables.SEMESTER_MODE, currentCcmID, true);
					saveMenu.parentNode.style.display = "none";
				});
				savePeriodMode.addEventListener("click", function ()
				{
					handleLesson(variables.PERIOD_MODE, currentCcmID, true);
					saveMenu.parentNode.style.display = "none";
				});
				saveInstanceMode.addEventListener("click", function ()
				{
					handleLesson(variables.INSTANCE_MODE, currentCcmID, true);
					saveMenu.parentNode.style.display = "none";
				});
				deleteSemesterMode.addEventListener("click", function ()
				{
					handleLesson(variables.SEMESTER_MODE, currentCcmID, false);
					deleteMenu.parentNode.style.display = "none";
				});
				deletePeriodMode.addEventListener("click", function ()
				{
					handleLesson(variables.PERIOD_MODE, currentCcmID, false);
					deleteMenu.parentNode.style.display = "none";
				});
				deleteInstanceMode.addEventListener("click", function ()
				{
					handleLesson(variables.INSTANCE_MODE, currentCcmID, false);
					deleteMenu.parentNode.style.display = "none";
				});
			}());

			/**
			 * Pops up at clicked lesson and sends an ajaxRequest to save lessons ccmID
			 *
			 * @param {HTMLDivElement} lessonElement
			 * @param {Object} data - lesson data like subject name, persons, locations...
			 */
			this.getSaveMenu = function (lessonElement, data)
			{
				currentCcmID = lessonElement.dataset.ccmID;
				saveMenu.style.display = "block";
				deleteMenu.style.display = "none";
				lessonMenuElement.style.display = "block";
				lessonElement.appendChild(lessonMenuElement);
				setLessonData(data);
			};

			/**
			 * Pops up at clicked lesson and sends an ajaxRequest to delete lessons ccmID
			 *
			 * @param {HTMLDivElement} lessonElement
			 * @param {Object} data - lesson data like subject name, persons, locations...
			 */
			this.getDeleteMenu = function (lessonElement, data)
			{
				currentCcmID = lessonElement.dataset.ccmID;
				saveMenu.style.display = "none";
				deleteMenu.style.display = "block";
				lessonMenuElement.style.display = "block";
				lessonElement.appendChild(lessonMenuElement);
				setLessonData(data);
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
			 * Adds a schedule to the list and set it into session storage
			 * @param {Schedule} schedule
			 */
			this.addSchedule = function (schedule)
			{
				var scheduleObject = {
					title: schedule.getTitle(),
					resource: schedule.getResource(),
					IDs: schedule.getResourceIDs()
				}, schedules = JSON.parse(window.sessionStorage.getItem("schedules"));

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
			 * @param {Schedule|string} schedule - the object or id of schedule
			 */
			this.removeSchedule = function (schedule)
			{
				var schedules = JSON.parse(window.sessionStorage.getItem("schedules"));

				delete schedules[schedule.getId()];
				window.sessionStorage.setItem("schedules", JSON.stringify(schedules));

				if (typeof schedule === "string")
				{
					schedule = this.schedules.find(
						function (obj)
						{
							return obj.id === schedule;
						}
					);
				}

				if (schedule.getTable())
				{
					schedule.getTable().remove();
					this.schedules.splice(this.schedules.indexOf(schedule), 1);
				}
			};

			/**
			 * gets the Schedule object which belongs to the given id
			 * @param {string} id
			 * @return {Schedule|boolean}
			 */
			this.getScheduleById = function (id)
			{
				for (var scheduleIndex = 0; scheduleIndex < this.schedules.length; ++scheduleIndex)
				{
					if (this.schedules[scheduleIndex].getId() === id)
					{
						return this.schedules[scheduleIndex];
					}
				}

				return false;
			};
		},

		/**
		 * Loads schedules from session storage
		 */
		loadSessionSchedules = function ()
		{
			var id, schedules = JSON.parse(window.sessionStorage.getItem("schedules"));

			if (schedules && Object.keys(schedules).length > 0)
			{
				for (id in schedules)
				{
					if (schedules.hasOwnProperty(id) && !scheduleObjects.getScheduleById(id))
					{
						new Schedule(schedules[id]["resource"], schedules[id]["IDs"], schedules[id]["title"]);
					}
				}

				if (scheduleObjects.schedules.length > 0)
				{
					switchToScheduleListTab();
				}

				showSchedule(jQuery("#selected-schedules").find(".selected-schedule").last().attr("id"));
			}
		},

		/**
		 * Select the grid of session storage
		 */
		selectSessionGrid = function ()
		{
			var grid = window.sessionStorage.getItem("scheduleGrid");

			if (grid)
			{
				document.querySelector("#grid [value='" + grid + "']").selected = true;
			}
		},

		/**
		 * Configures and build form depending on (url-) parameters
		 */
		setUpForm = function ()
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
						category: categoryName,
						session: JSON.parse(window.sessionStorage.getItem("scheduleCategory"))
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
			else if (formConfig.session)
			{
				sendFormRequest(formConfig.session.category, NaN, formConfig.session.id);
				showForm(formConfig.session.category);
				selectCategory(formConfig.session.category);
			}
			else
			{
				sendFormRequest(formConfig.inputToShowFirst);
				showForm("category-" + formConfig.inputToShowFirst);
			}
		},

		/**
		 * Selects given category in category-input of schedule form
		 *
		 * @param {string} category - name of selected category
		 */
		selectCategory = function (category)
		{
			var options = document.getElementById("category").options, index, option;

			for (index = 0; index < options.length; ++index)
			{
				option = options[index];
				option.selected = option.value === category;
			}
		},

		/**
		 * Sends a request for the given input ID and shows the belonging fields
		 *
		 * @param {string} inputID
		 */
		showForm = function (inputID)
		{
			var element, inputIndex, field, fieldLength = inputFields.length,
				order = {
					"category-program": ["program-input"],
					"program": ["program-input", "pool-input"],
					"pool": ["pool-input"],
					"category-roomtype": ["roomtype-input"],
					"roomtype": ["roomtype-input", "room-input"],
					"room": ["room-input"],
					"category-teacher": ["teacher-input"],
					"teacher": ["teacher-input"]
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
				field = inputFields[inputIndex];
				if (order[inputID].indexOf(field.id) !== -1)
				{
					field.classList.remove("hide");
				}
				else
				{
					field.classList.add("hide");
				}
			}
		},

		/**
		 * starts an Ajax request to fill form fields with values
		 *
		 * @param {string} resource
		 * @param {string|number} [ids] - to insert them in request instead of form selection values
		 * @param {number} [selectedId]  - select value immediately and show next form input
		 */
		sendFormRequest = function (resource, ids, selectedId)
		{
			var task = "&departmentIDs=" + (variables.departmentID !== "0" ? variables.departmentID :
					getSelectedValues("department") ? getSelectedValues("department") : "0");

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
			ajaxSelection.open("GET", variables.ajaxbase + task, true);
			ajaxSelection.onreadystatechange = function ()
			{
				var value, values, fieldElement, option, optionCount, optionValue,
					nextResource = document.getElementById(resource).dataset.next;

				if (ajaxSelection.readyState === 4 && ajaxSelection.status === 200)
				{
					fieldElement = document.getElementById(resource);
					removeChildren(fieldElement);

					if (placeholder[resource])
					{
						option = document.createElement("option");
						option.setAttribute("value", "");
						option.innerHTML = placeholder[resource];
						fieldElement.appendChild(option);
					}

					values = JSON.parse(ajaxSelection.responseText);
					optionCount = Object.keys(values).length;
					for (value in values)
					{
						if (values.hasOwnProperty(value))
						{
							option = document.createElement("option");
							optionValue = value.name ? values[value].id : values[value];
							option.setAttribute("value", optionValue);
							option.innerHTML = value.name ? values[value].name : value;
							fieldElement.appendChild(option);

							if (optionValue === selectedId || optionCount === 1)
							{
								option.selected = true;
								if (nextResource)
								{
									sendFormRequest(nextResource, selectedId);
								}
								else
								{
									sendLessonRequest(resource, selectedId);
								}
							}
						}
					}

					fieldElement.removeAttribute("disabled");
					jQuery("#" + resource).chosen("destroy").chosen();
					addSelectionEvent(resource);

					if (optionCount === 1)
					{
						sendLessonRequest(resource);
					}
				}
			};

			ajaxSelection.send(null);
		},

		/**
		 * Adds an event handler for schedule form selection elements
		 *
		 * @param {string} id - the elements ID which needs this event handler
		 */
		addSelectionEvent = function (id)
		{
			var input = document.getElementById(id + "-input"), drop = input.getElementsByClassName("chzn-results")[0];

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
		},

		/**
		 * Event handler for changing/selecting a schedule form input to load the next data or loads lessons
		 *
		 * @param {string} id - the form inputs id (optional).
		 */
		handleFormSelection = function (id)
		{
			var element = (typeof id === "string") ? document.getElementById(id) : id.target,
				sessionCategory = {
					"category": element.id,
					"id": getSelectedValues(element.id)
				};

			if (element.dataset.next)
			{
				window.sessionStorage.setItem("scheduleCategory", JSON.stringify(sessionCategory));
				sendFormRequest(element.dataset.next);
				showForm(element.id);
			}
			else
			{
				sendLessonRequest(element.id);
			}
		},

		/**
		 * starts an Ajax request to get lessons for the selected resource
		 *
		 * @param {string} resource
		 * @param {string|number} [optionalID] - specific id instead of resource form selection
		 * @param {string} [optionalTitle] - the same with optionalID
		 */
		sendLessonRequest = function (resource, optionalID, optionalTitle)
		{
			var IDs = optionalID || getSelectedValues(resource, "-"), schedule = scheduleObjects.getScheduleById(resource + IDs);

			if (schedule)
			{
				schedule.requestUpdate();
			}
			else
			{
				new Schedule(resource, IDs, optionalTitle);
			}

			switchToScheduleListTab();
		},

		/**
		 * Opens div which asks user to jump to the last or next available date
		 *
		 * @param {Array} dates - with pastDate and/or futureDate value
		 */
		openNextDateQuestion = function (dates)
		{
			var pastDate = dates.pastDate ? new Date(dates.pastDate) : null,
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
		},

		/**
		 * save lesson in users personal schedule
		 * choose between lessons of whole semester (1),
		 * just this daytime (2)
		 * or only the selected instance of a lesson (3).
		 *
		 * @param {number} [taskNumber=1]
		 * @param {number} ccmID - calendar_configuration_map ID
		 * @param {boolean} [save=true] - indicate to save or to delete the lesson
		 */
		handleLesson = function (taskNumber, ccmID, save)
		{
			var mode = (typeof taskNumber === "undefined") ? "1" : taskNumber,
				saving = (typeof save === "undefined") ? true : save,
				task = "&task=" + (saving ? "saveLesson" : "deleteLesson");

			if (!ccmID)
			{
				return false;
			}

			task += "&mode=" + mode + "&ccmID=" + ccmID;
			ajaxSave.open("GET", variables.ajaxbase + task, true);
			ajaxSave.onreadystatechange = function ()
			{
				var handledLessons, lessonIndex, lessonElements, lessonElement;

				if (ajaxSave.readyState === 4 && ajaxSave.status === 200)
				{
					handledLessons = JSON.parse(ajaxSave.responseText);

					scheduleObjects.schedules.forEach(function (schedule)
						{
							lessonElements = schedule.getTable().getLessons();
							for (lessonIndex = 0; lessonIndex < lessonElements.length; ++lessonIndex)
							{
								lessonElement = lessonElements[lessonIndex];

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
						}
					);

					app.updateSchedule();
				}
			};
			ajaxSave.send(null);
		},

		/**
		 * create a new entry in the dropdown field for selecting a schedule
		 *
		 * @param {Schedule} schedule
		 */
		addScheduleToSelection = function (schedule)
		{
			var selectedItem, selectedTitle, showButton, removeButton;

			selectedItem = document.createElement("div");
			selectedItem.id = schedule.getId();
			selectedItem.className = "selected-schedule";
			jQuery("#selected-schedules").append(selectedItem);

			selectedTitle = document.createElement("button");
			selectedTitle.className = "title";
			selectedTitle.innerHTML = schedule.getTitle();
			selectedTitle.addEventListener("click", function ()
			{
				showSchedule(schedule.getId());
			});
			selectedItem.appendChild(selectedTitle);

			showButton = document.createElement("button");
			showButton.className = "show-schedule";
			showButton.innerHTML = "<span class='icon-eye-close'></span>";
			showButton.addEventListener("click", function ()
			{
				showSchedule(schedule.getId());
			});
			selectedItem.appendChild(showButton);

			if (schedule.getId() !== "user")
			{
				removeButton = document.createElement("button");
				removeButton.className = "remove-schedule";
				removeButton.innerHTML = "<span class='icon-remove'></span>";
				removeButton.addEventListener("click", function ()
				{
					removeScheduleFromSelection(selectedItem, schedule);
				});
				selectedItem.appendChild(removeButton);
			}
			showSchedule(schedule.getId());
		},

		/**
		 * Shows schedule with given ID
		 *
		 * @param {string} scheduleID
		 */
		showSchedule = function (scheduleID)
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
		},

		/**
		 * Gets ID of now selected schedule in #selected-schedules HTMLDivElement.
		 * Returns false in case no schedule was found.
		 *
		 * @returns {string|boolean}
		 */
		getSelectedScheduleID = function ()
		{
			var selectedSchedule = document.getElementById("selected-schedules").getElementsByClassName("shown")[0];

			return selectedSchedule ? selectedSchedule.id : false;
		},

		/**
		 * remove an entry from the dropdown field for selecting a schedule
		 * @param {HTMLDivElement} scheduleSelectionElement - remove this element
		 * @param {Schedule} schedule - remove this object
		 */
		removeScheduleFromSelection = function (scheduleSelectionElement, schedule)
		{
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
		},

		/**
		 * removes all children elements of one given parent element
		 *
		 * @param {Object} element - parent element
		 */
		removeChildren = function (element)
		{
			var children = element.children, maxIndex = children.length - 1;

			for (var index = maxIndex; index >= 0; --index)
			{
				element.removeChild(children[index]);
			}
		},

		/**
		 * gets the concatenated and selected values of one multiple form field
		 *
		 * @param {string} fieldID
		 * @param {string} [separator=","]
		 * @returns {string|boolean}
		 */
		getSelectedValues = function (fieldID, separator)
		{
			var sep = separator ? separator : ",", field = document.getElementById(fieldID),
				options = field ? field.options : undefined, result = [];

			if (!field)
			{
				return false;
			}

			if (field.selectedIndex > -1)
			{
				for (var index = 0; index < options.length; ++index)
				{
					if (options[index].selected)
					{
						result.push(options[index].value);
					}
				}
			}

			return result.join(sep);
		},

		/**
		 * goes one day for- or backward in the schedules and takes the date out of the input field with 'date' as id
		 *
		 * @param {boolean} increase - goes forward by default, backward with false
		 * @param {string} step - defines how big the step is as "day", "week" or "month"
		 */
		changeDate = function (increase, step)
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

			app.dateField.value = newDate.getPresentationFormat();
		},

		/**
		 * returns the current date field value as a string connected by minus.
		 *
		 * @returns {string}
		 */
		getDateFieldString = function ()
		{
			return app.dateField.value.replace(/(\d{2})\.(\d{2})\.(\d{4})/, "$3" + "-" + "$2" + "-" + "$1");
		},

		/**
		 * returns a Date object by the current date field value
		 *
		 * @returns {Date}
		 */
		getDateFieldsDateObject = function ()
		{
			var parts = app.dateField.value.split(".", 3);
			if (parts)
			{
				// 12:00:00 o'clock for timezone offset
				return new Date(parseInt(parts[2], 10), parseInt(parts[1] - 1, 10), parseInt(parts[0], 10), 12, 0, 0);
			}
		},

		/**
		 * Change tab-behaviour of tabs in menu-bar, so all tabs can be closed
		 *
		 * @param {Object} clickedTab
		 */
		changeTabBehaviour = function (clickedTab)
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
		},

		/**
		 * Activates tab with a list of selected schedules
		 */
		switchToScheduleListTab = function ()
		{
			var selectedSchedulesTab = jQuery("#tab-selected-schedules");

			if (!selectedSchedulesTab.parent("li").hasClass("disabled-tab"))
			{
				selectedSchedulesTab.parent("li").addClass("active");
				jQuery("#selected-schedules").addClass("active");
			}
			jQuery("#tab-schedule-form").parent("li").removeClass("active");
			jQuery("#schedule-form").removeClass("active");
		},

		/**
		 * Activates tab with a form for selecting a new schedule
		 */
		switchToFormTab = function ()
		{
			var formTab = jQuery("#tab-schedule-form");

			if (!formTab.parent("li").hasClass("disabled-tab"))
			{
				formTab.parent("li").addClass("active");
				jQuery("#schedule-form").addClass("active");
			}
			jQuery("#tab-selected-schedules").parent("li").removeClass("active");
			jQuery("#selected-schedules").removeClass("active");
		},

		/**
		 * change position of the date-input, depending of screen-width
		 */
		changePositionOfDateInput = function ()
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
		},

		/**
		 * Disable tabs, when only the default-schedule-table is shown
		 *
		 * @param {string|Array} [tabIDs] - optional to disable only specific tabs
		 */
		disableTabs = function (tabIDs)
		{
			var i, allTabs = jQuery(".tabs-toggle"), scheduleInput = jQuery(".schedule-input"),
				tabsToDisable = [
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
		},

		/**
		 * Add or remove rows for breaks depending on time grid
		 */
		handleBreakRows = function ()
		{
			var tables = jQuery(".schedule-table"),
				numberOfColumns = variables.isMobile ? 2 : tables.find("tr:first").find("th").filter(
					function ()
					{
						return jQuery(this).css("display") !== "none";
					}
				).length,
				timeGrid = JSON.parse(variables.grids[getSelectedValues("grid")].grid),
				addBreakRow, addLunchBreakRow, periods, table, rows;

			tables.each(function(index, table)
			{
				rows = jQuery(table).find("tbody").find("tr");
				if (!timeGrid.hasOwnProperty("periods"))
				{
					jQuery(".break").closest("tr").remove();
					rows.not(":eq(0)").addClass("hide");
				}
				else if (timeGrid.periods[1].endTime === timeGrid.periods[2].startTime)
				{
					jQuery(".break").closest("tr").remove();
					rows.not(":eq(0)").removeClass("hide");
				}
				else if (!(rows.hasClass("break-row")))
				{
					rows.not(":eq(0)").removeClass("hide");
					for (periods in timeGrid.periods)
					{
						if (periods === "1" || periods === "2" || periods === "4" || periods === "5")
						{
							addBreakRow = '<tr class="break-row"><td class="break" colspan=' + numberOfColumns + '></td></tr>';
							jQuery(addBreakRow).insertAfter(rows.eq(periods - 1));
						}
						if (periods === "3")
						{
							addLunchBreakRow = '<tr class="break-row"><td class="break" colspan=' + numberOfColumns + '>'
								+ text.LUNCHTIME + '</td></tr>';
							jQuery(addLunchBreakRow).insertAfter(rows.eq(periods - 1));
						}
					}
				}
			});
		};

	/**
	 * @public
	 * @type {Element}
	 */
	this.dateField = document.getElementById("date");

	/**
	 * Sends an Ajax request to update all schedules or just the specified one.
	 *
	 * @param {string} id
	 */
	this.updateSchedule = function (id)
	{
		var schedule = scheduleObjects.getScheduleById(id);
		if (schedule)
		{
			schedule.requestUpdate();
		}
		else
		{
			scheduleObjects.schedules.forEach(
				function (schedule)
				{
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

			if (id === "date")
			{
				window.sessionStorage.setItem("scheduleDate", getDateFieldsDateObject().toJSON());
			}
		}
	};

	/**
	 * The date field gets the selected date, schedules get updates and the selection-div-element is hidden again
	 * @param {Object} event - Event that triggers this function
	 */
	this.nextDateEventHandler = function (event)
	{
		this.dateField.value = new Date(event.target.dataset.date).getPresentationFormat();
		nextDateSelection.style.display = "none";
		this.updateSchedule();
	};

	/**
	 * Opens export window of selected schedule
	 *
	 * @param {string} format
	 */
	this.handleExport = function (format)
	{
		var schedule = getSelectedScheduleID(), url = variables.exportbase,
			formats, resourceID, exportSelection = jQuery("#export-selection"),
			gridID = variables.grids[getSelectedValues("grid")].id;

		formats = format.split(".");
		url += "&format=" + formats[0];

		if (formats[0] === "pdf")
		{
			url += "&gridID=" + gridID;
		}

		if (formats[1] !== undefined)
		{
			url += "&documentFormat=" + formats[1];
		}

		if (schedule === "user")
		{
			url += "&myschedule=1";

			if (formats[0] === "ics")
			{
				url += "&username=" + variables.username + "&auth=" + variables.auth;
			}
		}
		else
		{
			resourceID = schedule.match(/[0-9]+/);

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
			window.prompt(text.copy, url);
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
	 * @return {Object} calendar
	 */
	this.getCalendar = function ()
	{
		return calendar;
	};

	/**
	 * "Constructor"
	 * Adds EventListener and initialise menus, schedules, tabs, calendar and form
	 */
	(function ()
	{
		var sessionDate = window.sessionStorage.getItem("scheduleDate"), startX, startY,
			date = sessionDate ? new Date(sessionDate) : new Date();

		calendar = new Calendar();
		lessonMenu = new LessonMenu();
		scheduleObjects = new Schedules();
		app.dateField.value = date.getPresentationFormat();

		if (variables.registered && !scheduleObjects.getScheduleById("user"))
		{
			new Schedule("user");
			switchToScheduleListTab();
		}

		changePositionOfDateInput();
		disableTabs();
		selectSessionGrid();
		setUpForm();
		loadSessionSchedules();

		/**
		 * swipe touch event handler changing the shown day and date
		 * @see http://www.javascriptkit.com/javatutors/touchevents.shtml
		 * @see http://www.html5rocks.com/de/mobile/touch/
		 */
		scheduleWrapper.addEventListener("touchstart", function (event)
		{
			var touch = event.changedTouches[0];
			startX = parseInt(touch.pageX);
			startY = parseInt(touch.pageY);
		});
		scheduleWrapper.addEventListener("touchend", function (event)
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
		jQuery("#category").chosen().change(function ()
		{
			window.sessionStorage.setItem("scheduleCategory", JSON.stringify({"category": this.value, "id": null}));
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
	})();

	/**
	 * context-menu-popup, calendar-popup and message-popup will be closed when clicking outside this
	 */
	jQuery(document).mouseup(function (e)
	{
		var popup = jQuery(".lesson-menu"), calendarPopup = jQuery("#calendar"), messagePopup = jQuery(".message-pop-up");

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

/**
 * get date string in the components specified format.
 * @see http://stackoverflow.com/a/3067896/6355472
 *
 * @param {boolean} [shortYear=true]
 * @returns {string}
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