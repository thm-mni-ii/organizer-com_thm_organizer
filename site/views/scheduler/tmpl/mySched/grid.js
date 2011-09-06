/**
 * Spezielles Grid
 * Vordefiniert fuer Wochenstruktur mit Veranstaltungen
 * @param {Object} schedObj
 * @param {Object} config
 */

var hideHeaders = false;

Ext.define('SchedGrid', {
	extend: 'Ext.grid.Panel',

	loadData: function (data) {
		if (MySched.daytime.length > 0)
			for (var i = 1; i < MySched.daytime[1].length; i++) {
				var index = i - 1;
					data[index].time = MySched.daytime[1][i].stime + '<br/>-<br/>' + MySched.daytime[1][i].etime;
			}

		// Wenn das grid auch angezeigt ist, zeige die Sporatischen Veranstaltungen dazu an
		if (MySched.selectedSchedule.grid == this) {
			MySched.layout.viewport.doLayout();
		}
		return this.store.loadData(data);
	},
	/**
	 * Leert die aktuell vorhanden Sportaischen Veranstaltungen und setzt die uebergebenen
	 * @param {Object} data
	 */
	setSporadicLectures: function (data) {
		this.sporadics = [];
		if (!data || data.length == 0) return;
		Ext.each(data, function (e) {
			this.sporadics.push(e);
		}, this);
	}
});

function getSchedGrid(){
	Ext.create('Ext.data.Store', {
	    storeId:'gridStore',
	    fields:['time', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
	    data:{'items':[]},
	    proxy: {
	        type: 'memory',
	        reader: {
	            type: 'json',
	            root: 'items'
	        }
	    }
	});

	var rowBodyFeature = Ext.create('Ext.grid.feature.RowBody', {
		    getAdditionalData: function(data, rowIndex, record, orig) {
		        var headerCt = this.view.headerCt,
		        colspan  = headerCt.getColumnCount();
		        if(rowIndex === 2)
		        {
			        return {
						rowBody: "Mittagspause", // do something with record
			            rowBodyCls: 'MySched_pause',
			            rowBodyColspan: colspan
			        };
		        }
		    }
		});

	var grid = Ext.create('SchedGrid', {
	    title: 'unknown',
	    store: Ext.data.StoreManager.lookup('gridStore'),
	    columns: [
	        {header: 'Zeit', menuDisabled:true, dataIndex: 'time', renderer: MySched.lectureCellRenderer, width: 35},
	        {header: 'Montag', menuDisabled:true, dataIndex: 'monday', renderer: MySched.lectureCellRenderer, flex: 1},
	        {header: 'Dienstag', menuDisabled:true, dataIndex: 'tuesday', renderer: MySched.lectureCellRenderer, flex: 1},
	        {header: 'Mittwoch', menuDisabled:true, dataIndex: 'wednesday', renderer: MySched.lectureCellRenderer, flex: 1},
	        {header: 'Donnerstag', menuDisabled:true, dataIndex: 'thursday', renderer: MySched.lectureCellRenderer, flex: 1},
	        {header: 'Freitag', menuDisabled:true, dataIndex: 'friday', renderer: MySched.lectureCellRenderer, flex: 1}
	    ],
	    viewConfig: {
				features: [rowBodyFeature]
		},
		cls: 'MySched_ScheduleGrid',
		scroll: 'vertical',
		disableSelection: true,
		overItemCls: ''
	});
	return grid;
}

function showEventdesc(index) {
	if (Ext.ComponentMgr.get("datdescription") == null || typeof Ext.ComponentMgr.get("datdescription") == "undefined") {
		this.eventWindow = Ext.create('Ext.Window', {
			id: "datdescription",
			title: MySched.eventlist[index]['title'] + " - Beschreibung",
			bodyStyle: "background-color: #FFF; padding: 7px;",
			frame:false ,
			buttons: [{
				text: "Schlie&szlig;en",
				handler: function () {
					this.eventWindow.close();
				},
				scope: this
			}],
			html: MySched.eventlist[index]['datdescription']
		});
		this.eventWindow.show();
	}
}

Ext.apply(Ext.form.VTypes, {
	daterange: function (val, field) {
		var date = field.parseDate(val);

		if (!date) {
			return;
		}
		if (field.startDateField && (!this.dateRangeMax || (date.getTime() != this.dateRangeMax.getTime()))) {
			var start = Ext.getCmp(field.startDateField);
			start.setMaxValue(date);
			start.validate();
			this.dateRangeMax = date;
		}
		else if (field.endDateField && (!this.dateRangeMin || (date.getTime() != this.dateRangeMin.getTime()))) {
			var end = Ext.getCmp(field.endDateField);
			end.setMinValue(date);
			end.validate();
			this.dateRangeMin = date;
		}
		/*
		 * Always return true since we're only using this vtype to set the
		 * min/max allowed values (these are tested for after the vtype test)
		 */
		return true;
	},

	password: function (val, field) {
		if (field.initialPassField) {
			var pwd = Ext.getCmp(field.initialPassField);
			return (val == pwd.getValue());
		}
		return true;
	},

	passwordText: 'Passwords do not match'
});

function addNewEvent(eventid, sdate, stime, etime) {

	if (Ext.isObject(eventid) || eventid == null || typeof eventid == "undefined") {
		eventid = "0";
	}
	else {
		eventid = eventid.split("_");
		eventid = eventid[1];
	}

	var weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker').value);

	if (Ext.isString(sdate)) {
		var daynumber = daytonumber(sdate);

		var adds = "";
		var date = null;

		weekpointer = getMonday(weekpointer);

		for (var i = 0; i < 7; i++) {
			if (weekpointer.getDay() == daynumber) {
				date = Ext.Date.format(weekpointer, "d.m.Y");
				break;
			}
			else weekpointer.setDate(weekpointer.getDate() + 1);
		}
	}
	else {
		weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker').value);
		date = Ext.Date.format(weekpointer, "d.m.Y");
	}

	if (typeof etime == "undefined") etime = "";
	if (typeof stime == "undefined") stime = "";

	adds = "&startdate=" + date + "&starttime=" + stime + "&endtime=" + etime;

	var win = Ext.create('Ext.window.Window', {
		layout: {
			type: 'fit'
		},
		id: 'terminWin',
		width: 800,
		title: "",
		height: 450,
		modal: true,
		frame:false,
		html: '<iframe width=100% height=100% onLoad="newEventonLoad(this)" id="iframeNewEvent" class="mysched_iframeNewEvent" src="' + externLinks.eventLink + eventid + '&tmpl=component' + adds + '"></iframe>'
	});

	win.on("beforeclose", function (panel, eOpts) {
		Ext.Ajax.request({
			url: _C('ajaxHandler'),
			method: 'POST',
			params: {
				jsid: MySched.SessionId,
				scheduletask: "Events.load"
			},
			success: function (response, request) {
				try {
					var jsonData = new Array();

					if (response.responseText.length > 0) {
						jsonData = Ext.decode(response.responseText);
					}
					MySched.selectedSchedule.eventsloaded = null;
					MySched.TreeManager.afterloadEvents(jsonData);
					MySched.selectedSchedule.refreshView();
				}
				catch(e)
				{
					return true;
				}
			}
		});
		return true;
	});

	win.show();
}

/**
 * This function add a hidden input field to the form in the passed iframe
 * @author Wolf
 * @param {object} The iframe which called this function
 */

function newEventonLoad(iframe) {
	var eventForm = Ext.DomQuery.select('form[id=eventForm]', iframe.contentDocument.documentElement);
	eventForm = eventForm[0];

	var cancel = Ext.DomQuery.select('button[id=btncancel]', eventForm);
	cancel = cancel[0];

	if (eventForm != null && cancel != null) {
		var formparent = eventForm.parentElement;
		if (!Ext.isObject(formparent)) {
			formparent = eventForm.getParent();
		}
		formparent.style.cssText = "";
		var input = document.createElement("input");
		var parent = cancel.parentElement;
		if (!Ext.isObject(parent)) {
			parent = cancel.getParent();
		}
		parent.removeChild(cancel);

		input.setAttribute("type", "hidden");
		input.setAttribute("name", "mysched");
		input.setAttribute("value", "1");

		eventForm.appendChild(input);
	}
}

/**
 * Spezieller Renderer fuer die Veranstaltungen
 * @param {Object} data
 * @param {Object} meta
 * @param {Object} record
 * @param {Object} rowIndex
 * @param {Object} colIndex
 * @param {Object} store
 * @param {Object} grid
 */
MySched.lectureCellRenderer = function (data, meta, record, rowIndex, colIndex, store) {
	function cl(cl) {
		if (MySched.freeBusyState) return cl + ' ';
		return cl + '_DIS ';
	}

	//show date behind the day
	if(colIndex > 0 && rowIndex === 0)
	{
		var weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker').value);

		weekpointer = getMonday(weekpointer);
		weekpointer.setDate(weekpointer.getDate() + (colIndex - 1));

		var headerCt = this.mSchedule.grid.getView().getHeaderCt();

		var header = headerCt.getHeaderAtIndex(colIndex);

		var bla = Ext.ComponentMgr.get('menuedatepicker');

		if(Ext.Date.format(Ext.ComponentMgr.get('menuedatepicker').value, "d.m.Y") == Ext.Date.format(weekpointer, "d.m.Y"))
			header.setText("<b>" + weekdayEtoD(numbertoday(colIndex)) + " (" + Ext.Date.format(weekpointer, "d.m.") + ")</b>");
		else
			header.setText(weekdayEtoD(numbertoday(colIndex)) + " (" + Ext.Date.format(weekpointer, "d.m.") + ")");
	}

	// Spalte 0 -> Zeiten
	//if (colIndex == 0 && rowIndex == 3) return '<div class="scheduleBox MySched_pause">' + data + '</div>';
	if (colIndex == 0) return '<div class="scheduleBox timeBox">' + data + '</div>';

	if (this.id != 'mySchedule' && this.mSchedule.type != 'delta') {
		if (MySched.Schedule.getBlockStatus(colIndex - 1, rowIndex + 1) == 1 && (data[0] != "<i>Mittagspause</i>" && data[0] != "<i> </i>")) {
			meta.tdCls += cl('blockBusy');
			meta.tdCls += cl('conMenu');
		} else if (MySched.Schedule.getBlockStatus(colIndex - 1, rowIndex + 1) > 1 && (data[0] != "<i>Mittagspause</i>" && data[0] != "<i> </i>")) {
			meta.tdCls += cl('blockOccupied');
			meta.tdCls += cl('conMenu');
		} else if (data == "<i>Mittagspause</i>" || data == "<i> </i>") {
			meta.tdCls += cl('blockFree');
			meta.tdCls += cl('MySched_pause');
		}
		else {
			meta.tdCls += cl('blockFree');
			meta.tdCls += cl('conMenu');
		}
	}
	else {
		if(isset(data[0]))
		{
			if (data[0] == "<i style='padding-left:40px;'>Mittagspause</i>" || data[0] == "<i></i>")
				meta.tdCls += cl('MySched_pause');
		}
		else {
			if (this.id == 'mySchedule') {
				meta.tdCls += cl('conMenu');
			}
		}
	}
	if (Ext.isEmpty(data)) return '';
	return data.join("\n");
}
