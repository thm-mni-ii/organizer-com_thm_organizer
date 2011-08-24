/**
 * Spezielles Grid
 * Vordefiniert fuer Wochenstruktur mit Veranstaltungen
 * @param {Object} schedObj
 * @param {Object} config
 */

var hideHeaders = false;

Ext.define('SchedGrid', {
	extend: 'Ext.grid.Panel',

	/*constructor: function (schedObj, config) {
		Ext.applyIf(this, config);

		this.store = new Ext.data.Store({
		    model: 'Grid'
		});

		this.columns = new Array({
			id: 'time',
			dataIndex: 'time',
			header: "Zeit",
			sortable: false,
			renderer: MySched.lectureCellRenderer,
			resizable: false,
			width: 35,
			menuDisabled: true
		});

		for (var i = 1; i < MySched.daytime.length; i++) {
			this.columns[this.columns.length] = {
				id: MySched.daytime[i].engName,
				dataIndex: MySched.daytime[i].engName,
				header: MySched.daytime[i].gerName,
				resizable: false,
				renderer: MySched.lectureCellRenderer,
				sortable: false,
				menuDisabled: true
			};
		}

		var rowBodyFeature = Ext.create('Ext.grid.feature.RowBody', {
		    getAdditionalData: function(data, rowIndex, record, orig) {
		        var headerCt = this.view.headerCt,
		        colspan  = headerCt.getColumnCount();
		        if(rowIndex === 3)
		        {
			        return {
						rowBody: "Mittagspause", // do something with record
			            rowBodyCls: 'MySched_pause',
			            rowBodyColspan: colspan
			        };
		        }
		    }
		});

		SchedGrid.superclass.constructor.call(this, {
			loadMask: {
				msg: 'Stundenplan wird geladen...'
			},
			viewConfig: {
				features: [rowBodyFeature],
				forceFit: true,
				trackOver: false
			},
			selType: 'cellmodel',
			height: 420,
			layoutOnTabChange: true,
			enableColumnHide: false,
			enableColumnMove: false,
			enableColumnResize: false
			//hideHeaders: hideHeaders,
		});

		if(hideHeaders === false)
			hideHeaders = true;

		//this.selModel.setLocked(true); // Keine Auswahl erlauben
		//this.enableHdMenu = false;
	},*/
	loadData: function (data) {
		if (MySched.daytime.length > 0)
			for (var i = 0; i < MySched.daytime[1].length; i++) {
				if (i < 3)
					data[i].time = MySched.daytime[1][i + 1].stime + '<br/>-<br/>' + MySched.daytime[1][i + 1].etime;
				/*else if (i == 3)
					data[3].time = '<i style="padding-left:40px;">Mittagspause</i>';*/
				else
					data[i].time = MySched.daytime[1][i].stime + '<br/>-<br/>' + MySched.daytime[1][i].etime;
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
		        if(rowIndex === 3)
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
	        {header: 'Zeit',  dataIndex: 'time', renderer: MySched.lectureCellRenderer},
	        {header: 'Montag',  dataIndex: 'monday', renderer: MySched.lectureCellRenderer},
	        {header: 'Dienstag',  dataIndex: 'tuesday', renderer: MySched.lectureCellRenderer},
	        {header: 'Mittwoch',  dataIndex: 'wednesday', renderer: MySched.lectureCellRenderer},
	        {header: 'Donnerstag',  dataIndex: 'thursday', renderer: MySched.lectureCellRenderer},
	        {header: 'Freitag',  dataIndex: 'friday', renderer: MySched.lectureCellRenderer}
	    ],
	    height: 400,
	    width: 600,
	    viewConfig: {
				features: [rowBodyFeature]
		}/*
	    plugins: [
	    	Ext.create('Ext.grid.feature.RowBody', {
			    getAdditionalData: function(data, rowIndex, record, orig) {
			        var headerCt = this.view.headerCt,
			        colspan  = headerCt.getColumnCount();
			        if(rowIndex === 3)
			        {
				        return {
							rowBody: "Mittagspause", // do something with record
				            rowBodyCls: 'MySched_pause',
				            rowBodyColspan: colspan
				        };
			        }
			    }
			})
	    ]*/
	});

	return grid;
}

function showEventdesc(index) {
	if (Ext.ComponentMgr.get("datdescription") == null || typeof Ext.ComponentMgr.get("datdescription") == "undefined") {
		this.eventWindow = new Ext.Window({
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
				date = weekpointer.format("d.m.Y");
				break;
			}
			else weekpointer.setDate(weekpointer.getDate() + 1);
		}
	}
	else {
		weekpointer = Ext.ComponentMgr.get('menuedatepicker').value
		date = weekpointer.format("d.m.Y");
	}

	if (typeof etime == "undefined") etime = "";
	if (typeof stime == "undefined") stime = "";

	adds = "&startdate=" + date + "&starttime=" + stime + "&endtime=" + etime;

	var wintitle = "";

	var win = new Ext.Window({
		layout: 'form',
		id: 'terminWin',
		width: 800,
		title: wintitle,
		height: 450,
		modal: true,
		frame:false,
		closeAction: 'close',
		html: '<iframe onLoad="newEventonLoad(this)" id="iframeNewEvent" class="mysched_iframeNewEvent" src="' + externLinks.eventLink + eventid + '&tmpl=component' + adds + '"></iframe>'
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
MySched.lectureCellRenderer = function (data, meta, record, rowIndex, colIndex, store) {
	function cl(cl) {
		if (MySched.freeBusyState) return cl + ' ';
		return cl + '_DIS ';
	}

	var weekpointer = Ext.ComponentMgr.get('menuedatepicker').value;
	if (weekpointer != "") {
		while (weekpointer.getDay() != 1) //Montag ermitteln
		{
			weekpointer.setDate(weekpointer.getDate() - 1);
		}
	}

	//var ele = grid.body.dom.querySelectorAll('.x-grid3-hd-'+this.id);
	var ele = Ext.DomQuery.select('.x-grid3-hd-' + this.id, this.container.dom);
	for (var di = 1; di < 7; di++) {
		if (numbertoday(weekpointer.getDay()) == this.id) {
			var firstCh = ele[0].firstChild;
			firstCh.parentNode.style.fontWeight = "";
			if (Ext.ComponentMgr.get('menuedatepicker').value == weekpointer.format("d.m.Y"))
				this.colModel.config[daytonumber(this.id)].header = "<b>"+weekdayEtoD(this.id) + " (" + weekpointer.format("d.m.") + ")</b>";
			else
				this.colModel.config[daytonumber(this.id)].header = weekdayEtoD(this.id) + " (" + weekpointer.format("d.m.") + ")";
		}
		weekpointer.setDate(weekpointer.getDate() + 1)
	}

	// Spalte 0 -> Zeiten
	//if (colIndex == 0 && rowIndex == 3) return '<div class="scheduleBox MySched_pause">' + data + '</div>';
	if (colIndex == 0) return '<div class="scheduleBox timeBox">' + data + '</div>';
	if (rowIndex > 3) rowIndex--;
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
