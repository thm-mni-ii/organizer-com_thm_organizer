var oldMainToolbar = MySched.layout.getMainToolbar;

MySched.layout.getMainToolbar = function () {
	var btnEvent = {
		// Event anlegen
		text: 'Termin anlegen',
		id: 'btnEvent',
		hidden: false,
		iconCls: 'tbEvent',
		handler: addNewEvent
	};
	var ToolbarObjects = oldMainToolbar();
	var newMainToolbar = ToolbarObjects.AddTo(3, btnEvent);
	return newMainToolbar;
};

var addEvent = {
	text: "Termin hinzuf&uuml;gen",
	icon: MySched.mainPath + "images/calendar_add.png",
	handler: function () {
		addNewEvent(null, MySched.BlockMenu.day, MySched.BlockMenu.stime, MySched.BlockMenu.etime);
	}
};

MySched.BlockMenu.Menu[MySched.BlockMenu.Menu.length] = addEvent;

var processResult = function (a, b, c, d, e) {
	alert(a);
};

window.onbeforeunload = function () {
	if(typeof MySched.layout.tabpanel === "undefined")
		return;
	var tabs = MySched.layout.tabpanel.items.items;
	var temptabs = tabs;
	var check = false;
	var tosave = false;
        var i = 0;
        var ti = 0;

	for (i = 0; i < tabs.length; i++) {
		if (tabs[i].mSchedule.status === "unsaved") {
			check = confirm("Sie haben Ihren Plan geändert.\nMöchten Sie die Änderungen speichern (OK) oder die Bearbeitung ohne Änderung abbrechen?");
			if (check === true) {
				for (ti = 0; ti < temptabs.length; ti++) {
					if (temptabs[ti].mSchedule.status === "unsaved") {
						if (temptabs[ti].mSchedule.id === "mySchedule") {
                        	temptabs[ti].mSchedule.save(_C('ajaxHandler'), false, "UserSchedule.save");
                        } else {
                        	temptabs[ti].mSchedule.save(_C('ajaxHandler'), false, "saveScheduleChanges");
                        }
						tosave = true;
					}
				}
				break;
			} else {
				break;
			}
		}
	}

	if (check === true && tosave === true) {
		var jetzt = new Date();
		var sek = jetzt.getSeconds();
		var undjetzt = new Date();
		var undsek = undjetzt.getSeconds();
		sek = sek + 3;
		while (sek % 60 > undsek) {
			undjetzt = new Date();
			undsek = undjetzt.getSeconds();
		}
	}
};