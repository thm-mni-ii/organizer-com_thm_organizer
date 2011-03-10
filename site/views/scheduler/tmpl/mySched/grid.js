/**
 * Spezielles Grid
 * Vordefiniert fuer Wochenstruktur mit Veranstaltungen
 * @param {Object} schedObj
 * @param {Object} config
 */
SchedGrid = function (schedObj, config) {
	Ext.apply(this, config);

	this.store = new Ext.data.Store({
		proxy: new Ext.data.MemoryProxy(),
		reader: new Ext.data.JsonReader({
			root: 'items'
		}, [{
			name: 'time'
		},
		{
			name: 'monday'
		},
		{
			name: 'tuesday'
		},
		{
			name: 'wednesday'
		},
		{
			name: 'thursday'
		},
		{
			name: 'friday'
		},
		{
			name: 'saturday'
		},
		{
			name: 'sunday'
		}])
	});
	this.columns = new Array({
		id: 'time',
		dataIndex: 'time',
		header: "Zeit",
		sortable: false,
		renderer: MySched.lectureCellRenderer,
		resizable: false,
		width: 35
	});

	for (var i = 1; i < MySched.daytime.length; i++) {
		this.columns[this.columns.length] = {
			id: MySched.daytime[i].engName,
			dataIndex: MySched.daytime[i].engName,
			header: MySched.daytime[i].gerName,
			resizable: false,
			renderer: MySched.lectureCellRenderer,
			sortable: false
		};
	}

	SchedGrid.superclass.constructor.call(this, {
		loadMask: {
			msg: 'Stundenplan wird geladen...'
		},
		viewConfig: {
			forceFit: true
		},
		autoHeight: true,
		trackMouseOver: false,
		sm: new Ext.grid.CellSelectionModel({
			singleSelect: true
		})

	});
	this.selModel.lock(); // Keine Auswahl erlauben
	this.enableHdMenu = false;
};
Ext.extend(SchedGrid, Ext.grid.GridPanel, {
	loadData: function (data) {
		this.setSporadicLectures(data.sporadic);

		if (MySched.daytime.length > 0)
			for (var i = 0; i < MySched.daytime[1].length; i++) {
				if (i < 3)
					data.items[i].time = MySched.daytime[1][i + 1].stime + '<br/>-<br/>' + MySched.daytime[1][i + 1].etime;
				else if (i == 3)
					data.items[3].time = '<i style="padding-left:40px;">Mittagspause</i>';
				else
					data.items[i].time = MySched.daytime[1][i].stime + '<br/>-<br/>' + MySched.daytime[1][i].etime;
			}

		// Wenn das grid auch angezeigt ist, zeige die Sporatischen Veranstaltungen dazu an
		if (MySched.selectedSchedule.grid == this) {
			MySched.layout.viewport.doLayout(); /*this.showSporadics(this);*/
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

function showEventdesc(index) {
	if (Ext.ComponentMgr.get("datdescription") == null || typeof Ext.ComponentMgr.get("datdescription") == "undefined") {
		this.eventWindow = new Ext.Window({
			id: "datdescription",
			title: MySched.eventlist[index]['title'] + " - Beschreibung",
			bodyStyle: "background-color: #FFF; padding: 7px;",
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
	Ext.QuickTips.init();
	if (Ext.isObject(eventid) || eventid == null || typeof eventid == "undefined") {
		eventid = "0";
	}
	else {
		eventid = eventid.split("_");
		eventid = eventid[1];
	}

	var weekpointer = "";
	var datemenu = Ext.ComponentMgr.get('menuedatepicker');
	var weekpointer = null;
	if (typeof datemenu.menu == "undefined") weekpointer = datemenu.initialConfig.value;
	else weekpointer = datemenu.menu.picker.activeDate;

	if (Ext.isString(sdate)) {
		var daynumber = daytonumber(sdate);

		var adds = "";
		var date = null;

		if (weekpointer != "") {
			while (weekpointer.getDay() != 1) //Montag ermitteln
			{
				weekpointer.setDate(weekpointer.getDate() - 1);
			}
		}

		for (var i = 0; i < 7; i++) {
			if (weekpointer.getDay() == daynumber) {
				date = weekpointer.format("Y-m-d");
				break;
			}
			else weekpointer.setDate(weekpointer.getDate() + 1);
		}
	}
	else {
		var splitteddate = Ext.ComponentMgr.get('menuedatepicker').value.split(".");
		weekpointer = new Date(splitteddate[2], splitteddate[1] - 1, splitteddate[0]);
		date = weekpointer.format("Y-m-d");
	}

	if (typeof etime == "undefined") etime = "";
	if (typeof stime == "undefined") stime = "";

	adds = "&startdate=" + date + "&starttime=" + stime + "&endtime=" + etime;

	var wintitle = "Termin";
	if (eventid == 0) wintitle = "Termin hinzufügen";
	else wintitle = "Termin ändern";

	var win = new Ext.Window({
		layout: 'form',
		id: 'terminWin',
		width: 564,
		title: wintitle,
		height: 450,
		modal: true,
		closeAction: 'close',
		//src:'http://localhost/joomla/index.php?option=com_thm_organizer&view=event_edit&eventid='+eventid+'&tmpl=component'
		html: '<iframe onLoad="newEventonLoad(this)" id="iframeNewEvent" class="mysched_iframeNewEvent" src="http://localhost/joomla/index.php?option=com_thm_organizer&view=event_edit&eventid=' + eventid + '&tmpl=component' + adds + '"></iframe>'
		/*items:[
		 panel
		 ]*/
	});

	win.on("close", function (panel) {
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
				}
				win.close();
			}
		});
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
MySched.lectureCellRenderer = function (data, meta, record, rowIndex, colIndex, store, grid) {
	function cl(cl) {
		if (MySched.freeBusyState) return cl + ' ';
		return cl + '_DIS ';
	}

	var test = Ext.ComponentMgr.get('menuedatepicker');
	var splitteddate = Ext.ComponentMgr.get('menuedatepicker').value.split(".");
	var weekpointer = new Date(splitteddate[2], splitteddate[1] - 1, splitteddate[0]);
	if (weekpointer != "") {
		while (weekpointer.getDay() != 1) //Montag ermitteln
		{
			weekpointer.setDate(weekpointer.getDate() - 1);
		}
	}

	//var ele = grid.body.dom.querySelectorAll('.x-grid3-hd-'+this.id);
	var ele = Ext.DomQuery.select('.x-grid3-hd-' + this.id, grid.body.dom);
	for (var di = 1; di < 7; di++) {
		if (numbertoday(weekpointer.getDay()) == this.id) {
			var firstCh = ele[0].firstChild;
			firstCh.parentNode.style.fontWeight = "";
			firstCh.nodeValue = weekdayEtoD(this.id) + " (" + weekpointer.format("d.m.") + ")";
			if (Ext.ComponentMgr.get('menuedatepicker').value == weekpointer.format("d.m.Y")) firstCh.parentNode.style.fontWeight = "bold";
		}
		weekpointer.setDate(weekpointer.getDate() + 1)
	}

	// Spalte 0 -> Zeiten
	if (colIndex == 0 && rowIndex == 3) return '<div class="scheduleBox MySched_pause">' + data + '</div>';
	if (colIndex == 0) return '<div class="scheduleBox timeBox">' + data + '</div>';
	if (rowIndex > 3) rowIndex--;
	if (grid.id != 'mySchedule' && grid.id != 'delta') {
		if (MySched.Schedule.getBlockStatus(colIndex - 1, rowIndex + 1) == 1 && (data[0] != "<i>Mittagspause</i>" && data[0] != "<i> </i>")) {
			meta.css += cl('blockBusy');
			meta.css += cl('conMenu');
		} else if (MySched.Schedule.getBlockStatus(colIndex - 1, rowIndex + 1) > 1 && (data[0] != "<i>Mittagspause</i>" && data[0] != "<i> </i>")) {
			meta.css += cl('blockOccupied');
			meta.css += cl('conMenu');
		} else if (data == "<i>Mittagspause</i>" || data == "<i> </i>") {
			meta.css += cl('blockFree');
			meta.css += cl('MySched_pause');
		}
		else {
			meta.css += cl('blockFree');
			meta.css += cl('conMenu');
		}
	}
	else {

		if (data[0] == "<i>Mittagspause</i>" || data[0] == "<i> </i>") meta.css += cl('MySched_pause');
		else {
			if (grid.id == 'mySchedule') {
				meta.css += cl('conMenu');
			}
		}
	}
	if (Ext.isEmpty(data)) return '';
	return data.join("\n");
}