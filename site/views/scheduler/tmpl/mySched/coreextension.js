/**
 * Make changes to display events
 **/
Ext.DatePicker.prototype.update = function (date, forceRefresh) {
	if (typeof date == "undefined") return;
	if (this.rendered) {
		var vd = this.activeDate,
			vis = this.isVisible();
		this.activeDate = date;
		if (!forceRefresh && vd && this.el) {
			var t = date.getTime();
			if (vd.getMonth() == date.getMonth() && vd.getFullYear() == date.getFullYear()) {
				this.cells.removeClass('x-date-selected');
				this.cells.each(function (c) {
					if (c.dom.firstChild.dateValue == t) {
						c.addClass('x-date-selected');
						if (vis && !this.cancelFocus) {
							Ext.fly(c.dom.firstChild).focus(50);
						}
						return false;
					}
				}, this);
				return;
			}
		}
		var days = date.getDaysInMonth(),
			firstOfMonth = date.getFirstDateOfMonth(),
			startingPos = firstOfMonth.getDay() - this.startDay;

		if (startingPos < 0) {
			startingPos += 7;
		}
		days += startingPos;

		var pm = date.add('mo', -1),
			prevStart = pm.getDaysInMonth() - startingPos,
			cells = this.cells.elements,
			textEls = this.textNodes,

			d = (new Date(pm.getFullYear(), pm.getMonth(), prevStart, 0)),
			today = new Date().clearTime().getTime(),
			sel = date.clearTime(true).getTime(),
			min = this.minDate ? this.minDate.clearTime(true) : Number.NEGATIVE_INFINITY,
			max = this.maxDate ? this.maxDate.clearTime(true) : Number.POSITIVE_INFINITY,
			ddMatch = this.disabledDatesRE,
			ddText = this.disabledDatesText,
			ddays = this.disabledDays ? this.disabledDays.join('') : false,
			ddaysText = this.disabledDaysText,
			format = this.format;

		if (this.showToday) {
			var td = new Date().clearTime(),
				disable = (td < min || td > max || (ddMatch && format && ddMatch.test(td.dateFormat(format))) || (ddays && ddays.indexOf(td.getDay()) != -1));

			if (!this.disabled) {
				this.todayBtn.setDisabled(disable);
				this.todayKeyListener[disable ? 'disable' : 'enable']();
			}
		}

		var setCellClass = function (cal, cell) {
			cell.title = '';
			var t = d.clearTime(true).getTime();
			cell.firstChild.dateValue = t;
			if (t == today) {
				cell.className += ' x-date-today';
				cell.title = cal.todayText;
			}
			if (t == sel) {
				cell.className += ' x-date-selected';
				if (vis) {
					Ext.fly(cell.firstChild).focus(50);
				}
			}

			if (t < min) {
				cell.className = ' x-date-disabled';
				cell.title = cal.minText;
				return;
			}
			if (t > max) {
				cell.className = ' x-date-disabled';
				cell.title = cal.maxText;
				return;
			}
			if (ddays) {
				if (ddays.indexOf(d.getDay()) != -1) {
					cell.title = ddaysText;
					cell.className = ' x-date-disabled';
				}
			}
			if (ddMatch && format) {
				var fvalue = d.dateFormat(format);
				if (ddMatch.test(fvalue)) {
					cell.title = ddText.replace('%0', fvalue);
					cell.className = ' x-date-disabled';
				}
			}

			cell.children[0].events = new Array();

			var begin = MySched.session["begin"].split(".");
			begin = new Date(begin[2], begin[1]-1, begin[0]);
			var end = MySched.session["end"].split(".");
			end = new Date(end[2], end[1]-1, end[0]);

			if (begin == d) {
				cell.className += ' x-date-highlight_semester';
				var len = cell.children[0].events.length;
				cell.children[0].events[len] = "Semesteranfang";
			}
			else if (end == d) {
				cell.className += ' x-date-highlight_semester';
				var len = cell.children[0].events.length;
				cell.children[0].events[len] = "Semesterende";
			}
			else if (begin <= d && end >= d) {
				cell.className += ' x-date-highlight_semester';
				var len = cell.children[0].events.length;
				cell.children[0].events[len] = "Semester";
			}


			var EL = MySched.eventlist.data;

			for (var ELindex = 0; ELindex < EL.length; ELindex++) {

				var startdate = EL.items[ELindex].data.startdate.split(".");
				startdate = new Date(startdate[2], startdate[1]-1, startdate[0]);
				var enddate = EL.items[ELindex].data.enddate.split(".");
				enddate = new Date(enddate[2], enddate[1]-1, enddate[0]);

				if (startdate <= d && enddate >= d) {
					if (cell.className.contains(" x-date-highlight_joomla") == false && cell.className.contains(" x-date-highlight_estudy") == false) cell.className += ' x-date-highlight_' + EL.items[ELindex].data.source;

					var len = cell.children[0].events.length;
					cell.children[0].events[len] = EL.items[ELindex];
				}
			}
			if (!cell.children[0].className.contains(" calendar_tooltip")) cell.children[0].className += " calendar_tooltip";
		};

		var i = 0;
		for (; i < startingPos; i++) {
			textEls[i].innerHTML = (++prevStart);
			d.setDate(d.getDate() + 1);
			cells[i].className = 'x-date-prevday';
			setCellClass(this, cells[i]);
		}
		for (; i < days; i++) {
			var intDay = i - startingPos + 1;
			textEls[i].innerHTML = (intDay);
			d.setDate(d.getDate() + 1);
			cells[i].className = 'x-date-active';
			setCellClass(this, cells[i]);
		}
		var extraDays = 0;
		for (; i < 42; i++) {
			textEls[i].innerHTML = (++extraDays);
			d.setDate(d.getDate() + 1);
			cells[i].className = 'x-date-nextday';
			setCellClass(this, cells[i]);
		}

		this.mbtn.setText(this.monthNames[date.getMonth()] + ' ' + date.getFullYear());

		if (!this.internalRender) {
			var main = this.el.dom.firstChild,
				w = main.offsetWidth;
			this.el.setWidth(w + this.el.getBorderWidth('lr'));
			Ext.fly(main).setWidth(w);
			this.internalRender = true;



			if (Ext.isOpera && !this.secondPass) {
				main.rows[0].cells[1].style.width = (w - (main.rows[0].cells[0].offsetWidth + main.rows[0].cells[2].offsetWidth)) + 'px';
				this.secondPass = true;
				this.update.defer(10, this, [date]);
			}
		}

		Ext.select('.calendar_tooltip', false, document).removeAllListeners();
		Ext.select('.calendar_tooltip', false, document).on({
			'mouseover': function (e) {
				e.stopEvent();
				calendar_tooltip(e);
			},
			'mouseout': function (e) {
				e.stopEvent();
				if (Ext.getCmp('mySched_calendar-tip')) Ext.getCmp('mySched_calendar-tip').destroy();
			},
			scope: this
		});


	}
}

calendar_tooltip = function (e) {
	var el = e.getTarget('.calendar_tooltip', 5, true);
	if (Ext.getCmp('mySched_calendar-tip')) Ext.getCmp('mySched_calendar-tip').destroy();
	var xy = el.getXY();
	xy[0] = xy[0] + el.getWidth() + 10;

	var events = el.dom.events;
	var htmltext = "";
	for (var i = 0; i < events.length; i++) {
		if (Ext.isObject(events[i])) {
			htmltext += events[i].data.title;
			var name = "";
			for(var obj in events[i].data.objects)
			{
				if(typeof obj != "function")
					if(name != "")
						name += ", ";
					if(obj.substring(0, 3) == "RM_") {
						name += MySched.Mapping.getName("room", obj);
					} else if(obj.substring(0, 3) == "TR_") {
						name += MySched.Mapping.getName("doz", obj);
					} else if(obj.substring(0, 3) == "CL_"){
						name += MySched.Mapping.getName("clas", obj);
					}
			}
			if(name != "")
				htmltext += " ("+ name + ")<br/>";
		}
		else {
			htmltext += events[i] + "<br/>";
		}
	}

	if (events.length > 0) {
		var ttInfo = new Ext.ToolTip({
			title: '<div class="mySched_tooltip_calendar_title">Termin(e):</div>',
			id: 'mySched_calendar-tip',
			target: 'leftCallout',
			anchor: 'left',
			autoWidth: true,
			autoHide: false,
			html: htmltext,
			cls: "mySched_tooltip_calendar"
		});

		ttInfo.showAt(xy);
	}
}

/**
 * Make changes to display the Columns correctly
 **/
Ext.grid.View.prototype.updateAllColumnWidths = function () {
	var tw = this.getTotalWidth(),
		clen = this.cm.getColumnCount(),
		ws = [],
		len, i;

	for (i = 0; i < clen; i++) {
		ws[i] = this.getColumnWidth(i);
	}

	this.innerHd.firstChild.style.width = this.getOffsetWidth();
	this.innerHd.firstChild.firstChild.style.width = tw;
	this.mainBody.dom.style.width = tw;

	for (i = 0; i < clen; i++) {
		var hd = this.getHeaderCell(i);
		hd.style.width = ws[i];
	}

	var ns = this.getRows(),
		row, trow;
	for (i = 0, len = ns.length; i < len; i++) {
		row = ns[i];
		row.style.width = tw;
		if (row.firstChild) {
			row.firstChild.style.width = tw;
			trow = row.firstChild.rows[0];
			for (var j = 0; j < clen; j++) {
				if (j < trow.childNodes.length) // Added this because of the "Mittagspause" row.
				trow.childNodes[j].style.width = ws[j];
			}
		}
	}

	this.onAllColumnWidthsUpdated(ws, tw);
}