/**
 * mySched - Mainclass
 * by Thorsten Buss
 */

// Zeigt farbige Frei/Belegtzeiten an
MySched.freeBusyState = true;
// verweiss auf aktuell ausgewaehlten Stundenplan
MySched.selectedSchedule = null;
// Versionsnummer
MySched.version = '2.1.8';
// verweiss auf den Plan mit den Aenderungen
MySched.delta = null;
// verweiss auf den Plan mit dem ChangeLog
MySched.responsibleChanges = null;
MySched.session = new Array();
MySched.daytime = new Array();
MySched.loadedLessons = new Array();
MySched.mainPath = externLinks.mainPath;
//set ajax timeout to 10 seconds
Ext.Ajax.timeout = 30000;

// Setzte die initalwerte fuer das Konfigurationsobjekt
MySched.Config.addAll({
  // Bestimt die art und weise der Anzeige von Zusatzinfos
  infoMode: 'popup',
  // layout | popup
  ajaxHandler: '../index.php?option=com_thm_organizer&view=ajaxhandler&format=raw',
  estudycourse: MySched.mainPath + 'php/estudy_course.php',
  infoUrl: MySched.mainPath + 'php/info.php',
  showHeader: false,
  // soll der Headerbereich angezeigt werden?
  headerHTML: '<img src="http://www.mni.fh-giessen.de/templates/fh/Bilder/Header.png" title="fh-header" alt="fh-header"/>',
  enableSubscribing: false,
  // Aktiviert den Button und die Funktion 'Einschreiben'
  logoutTarget: 'http://www.mni.fh-giessen.de'
});


// Authorize wir initalisiert
// Mit dem Uebergebenen Array wird die Rolle auf rechte in MySched gemappt
// ACHTUNG!! Keys werden komplett lowercase gehalten
MySched.Authorize.init({
  // Welche Rolle hat ein nicht angemeldeter User?
  defaultRole: 'user',
  // ALL definiert den gemeinsammen Nenner aller Rollen
  ALL: {
    clas: '*',
    diff: '*',
    curtea: '*'
  },
  // jede Rolle kann ein Array mit Keys oder ein string mit '*' zugewiesen bekommen
  user: {
    room: '*'
  },
  registered: {
    room: '*'
  },
  author: {
    doz: '*',
    room: '*'
  },
  editor: {
    doz: '*',
    room: '*'
  },
  publisher: {
    doz: '*',
    room: '*'
  },
  administrator: {
    doz: '*',
    room: '*',
    respChanges: '*'
  },
  manager: {
    doz: '*',
    room: '*'
  },
  'super users': {
    doz: '*',
    room: '*',
    respChanges: '*'
  }
});

MySched.BlockMenu = [];
MySched.BlockMenu.Menu = [];

var addLesson = {
  text: "Veranstaltung hinzuf&uuml;gen",
  icon: MySched.mainPath + "images/icon-publish.png",
  handler: function () {
    newPEvent(MySched.BlockMenu.day, MySched.BlockMenu.stime, MySched.BlockMenu.etime);
  }
};

MySched.BlockMenu.Menu[MySched.BlockMenu.Menu.length] = addLesson;

/**
 * MainObjekt
 */
MySched.Base = function () {
  var schedule, sid, fertig = false;

  return {
    init: function () {
      if(Ext.isString(MySched.startup) === true)
	      try
	      {
          	MySched.startup = Ext.decode(decodeURIComponent(MySched.startup));
          }
          catch(e)
          {}
      if(checkStartup("Grid.load") === true)
      {
        MySched.Base.startMySched(MySched.startup["Grid.load"].data);
      }
      else
      Ext.Ajax.request({
        url: _C('ajaxHandler'),
        method: 'POST',
        params: {
          class_semester_id: MySched.class_semester_id,
          scheduletask: "Grid.load"
        },
        failure: function (response) {
        },
        success: function (response) {
        	try
        	{
	            var json = Ext.decode(response.responseText);
	            MySched.Base.startMySched(json);
	        }
	        catch(e)
	        {
	        	Ext.Msg.alert('Fehler beim Laden', response.responseText);
	        }
        }
      });
    },
    startMySched: function(json)
    {
      var length = 0;
      if(Ext.isNumber(json.length))
        length = json.length;
      else
        length = json.size;
      for (var i = 0; i < length; i++) {
        if (!MySched.daytime[json[i].day]) {
          MySched.daytime[json[i].day] = new Array();
          MySched.daytime[json[i].day]["engName"] = numbertoday(json[i].day);
          MySched.daytime[json[i].day]["gerName"] = weekdayEtoD(numbertoday(json[i].day));
          MySched.daytime[json[i].day]["localName"] = "day"
        }
        if (!MySched.daytime[json[i].day][json[i].period]) MySched.daytime[json[i].day][json[i].period] = new Array();
        MySched.daytime[json[i].day][json[i].period]["etime"] = json[i].endtime.substr(0, 5);
        MySched.daytime[json[i].day][json[i].period]["stime"] = json[i].starttime.substr(0, 5);
        MySched.daytime[json[i].day][json[i].period]["tpid"] = json[i].tpid;
        MySched.daytime[json[i].day][json[i].period]["localName"] = "block"
      }
      // Initalisieren des Baumes und der Auswahlsteuerung
      MySched.TreeManager.init();

      // Initalisiert das Namen/Kuerzel mapping
      MySched.Mapping.init();

      // Initialisert die Anzeige von Quicktips
      Ext.QuickTips.init();

      // Laed die XML Datei mit den Stundenplandaten
      MySched.Base.loadLectures(_C('scheduleXml'));
    },
    /**
     * Stundenplan Events registrieren.
     **/
    registerScheduleEvents: function () {
      // Steuerun der Anzeige des "leeren" Buttons - nur wenn Stundenplan nicht schon leer
      MySched.Schedule.on({
        'lectureAdd': function () {
          Ext.ComponentMgr.get('btnEmpty').enable();
        },
        'lectureDel': function () {
          if (MySched.Schedule.isEmpty()) {
            Ext.ComponentMgr.get('btnPdf').disable();
            if (_C('enableSubscribing')) Ext.ComponentMgr.get('btnSub').disable();
          }
        },
        'changed': function () {
          if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip').destroy();
          if (!MySched.Schedule.isEmpty()) {
            Ext.ComponentMgr.get('btnPdf').enable();
            if (_C('enableSubscribing')) Ext.ComponentMgr.get('btnSub').enable();
          }
          if (MySched.selectedSchedule.id == "mySchedule") Ext.ComponentMgr.get('btnSave').enable();
          var tab = MySched.layout.tabpanel.getItem('mySchedule');
          tab.mSchedule.status = "unsaved";
          tab = Ext.get(MySched.layout.tabpanel.getTabEl(tab)).child('.myScheduleIcon');
          if (tab) tab.replaceClass('myScheduleIcon', 'myScheduleIconSave');
        },
        'save': function (s) {
          var tab = MySched.layout.tabpanel.getItem('mySchedule');
          tab.mSchedule.status = "saved";
          if (Ext.get(MySched.layout.tabpanel.getTabEl(tab)).child('.myScheduleIconSave')) Ext.get(MySched.layout.tabpanel.getTabEl(tab)).child('.myScheduleIconSave').replaceClass('myScheduleIconSave', 'myScheduleIcon');
          Ext.ComponentMgr.get('btnSave').disable();
        },
        'load': function (s) {
          MySched.Base.createUserSchedule();
          Ext.ComponentMgr.get('btnSave').disable();
          var tab = MySched.layout.tabpanel.getItem('mySchedule');
          if (Ext.get(MySched.layout.tabpanel.getTabEl(tab)).child('.myScheduleIconSave')) Ext.get(MySched.layout.tabpanel.getTabEl(tab)).child('.myScheduleIconSave').replaceClass('myScheduleIconSave', 'myScheduleIcon');
        },
        'clear': function (s) {}
      });
    },
    /**
     * Stundenplan Events registrieren.
     **/
    regScheduleEvents: function (id) {
      MySched.selectedSchedule.on({
        'changed': function () {
          if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip').destroy();
          var tab = MySched.layout.tabpanel.getItem(id);
          if (id != "mySchedule") Ext.ComponentMgr.get('btnSave').enable();
          tab.mSchedule.status = "unsaved";
          tab = Ext.get(MySched.layout.tabpanel.getTabEl(tab)).child('.' + MySched.selectedSchedule.type + 'Icon');
          if (tab) tab.replaceClass(MySched.selectedSchedule.type + 'Icon', MySched.selectedSchedule.type + 'IconSave');
        },
        'save': function (s) {
          var tab = MySched.layout.tabpanel.getItem(id);
          tab.mSchedule.status = "saved";
          if (Ext.get(MySched.layout.tabpanel.getTabEl(tab)).child('.' + MySched.selectedSchedule.type + 'IconSave')) Ext.get(MySched.layout.tabpanel.getTabEl(tab)).child('.' + MySched.selectedSchedule.type + 'IconSave').replaceClass(MySched.selectedSchedule.type + 'IconSave', MySched.selectedSchedule.type + 'Icon');
          Ext.ComponentMgr.get('btnSave').disable();
        },
        'clear': function (s) {
          Ext.ComponentMgr.get('btnEmpty').disable();
        }
      });
    },
    /**
     * Laed die XML Datei und startet den Parsevorgang
     * @param {String} url XML-Datei
     */
    loadLectures: function (url) {
      // Stundenplandaten werden in einem 'gesamten' Stundenplan gespeichert
      this.schedule = new mSchedule();
      this.afterLoad();
    },
    /**
     * Aufgaben nachdem die XMLDaten erfolgreich geladen wurden
     * @param {Object} ret ReturnObjekt vom Ladevorgang (Im Endeffet this)
     */
    afterLoad: function (ret) {
      MySched.eventlist = new mEventlist();
      if(checkStartup("Events.load") === true)
      {
        MySched.TreeManager.afterloadEvents(MySched.startup["Events.load"].data);
        MySched.Base.myschedInit(ret);
      }
      else
      Ext.Ajax.request({
        url: _C('ajaxHandler'),
        method: 'POST',
        params: {
          jsid: MySched.SessionId,
          scheduletask: "Events.load"
        },
        failure: function (response, request) {
          MySched.Base.myschedInit(ret);
        },
        success: function (response, request) {
          try {
            var jsonData = new Array();

            if (response.responseText.length > 0) {
              jsonData = Ext.decode(response.responseText);
            }

            MySched.TreeManager.afterloadEvents(jsonData);

            MySched.Base.myschedInit(ret);
          }
          catch(e)
          {}
        }
      });
    },
    myschedInit: function (ret) {
      // Initialisiert "Mein Stundenplan"
      MySched.Schedule = new mSchedule('mySchedule', 'Mein Stundenplan');

      // Initialisiert "Änderungen der Verantwortlichen"
      MySched.responsibleChanges = new mSchedule("respChanges", "Änderungen (eigene)");

      // Lädt responsible Changes
      MySched.responsibleChanges.load(_C('ajaxHandler'), 'json', MySched.responsibleChanges.loadsavedLectures, function (params) {
        MySched.responsibleChanges.data.eachKey(function (k, v) {
          MySched.Base.schedule.addLecture(v);
        });
      }, this, "respChanges");

      // Registriert Events bei Aenderung des eigenen Stundenplans
      MySched.Base.registerScheduleEvents();

      // Initalisiert Infoanzeige
      MySched.InfoPanel.init();
      MySched.SelectionManager.init();

      // Erstellt das Layout
      MySched.layout.buildLayout();

      if(checkStartup("ScheduleDescription.load") === true)
      {
        MySched.Base.setScheduleDescription(MySched.startup["ScheduleDescription.load"].data);
      }
      else
      Ext.Ajax.request({
        url: _C('ajaxHandler'),
        method: 'POST',
        params: {
          username: MySched.Authorize.user,
          class_semester_id: MySched.class_semester_id,
          scheduletask: "ScheduleDescription.load"
        },
        failure: function (resp, req) {
          Ext.MessageBox.hide();
          Ext.Msg.alert("Stundenplan laden", 'Es ist ein Fehler beim Laden der Beschreibung des Stundenplans aufgetreten.');
        },
        success: function (resp) {
          // Zeigt das Erstellungsdatum der Stundenplandaten an
          var jsonData = new Array();
          if (resp.responseText.length > 0) {
          	try {
	            jsonData = Ext.decode(resp.responseText);
	            MySched.Base.setScheduleDescription(jsonData);
	        }
	        catch(e)
	        {}
          }
        }
      });
    },
    setScheduleDescription: function(jsonData)
    {
      if (Ext.isObject(jsonData) || Ext.isArray(jsonData)) {
        MySched.Tree.setTitle(jsonData[0], true);

        MySched.session["begin"] = jsonData[1];
        MySched.session["end"] = jsonData[2];
        MySched.session["creationdate"] = jsonData[3];
        Ext.ComponentMgr.get('leftMenu').setTitle("Stand vom " +  MySched.session["creationdate"]);
        // Managed die Sichtbarkeit der Add/Del Buttons in der Toolbar
        MySched.SelectionManager.on('select', function (el) {
          if (MySched.Schedule.lectureExists(el.id)) {
            Ext.ComponentMgr.get('btnDel').enable();
          } else {
            Ext.ComponentMgr.get('btnAdd').enable();
          }
        });
        MySched.SelectionManager.on('unselect', function () {
          Ext.ComponentMgr.get('btnDel').disable();
          Ext.ComponentMgr.get('btnAdd').disable();
        });
        MySched.SelectionManager.on('lectureAdd', function () {
          Ext.ComponentMgr.get('btnAdd').disable();
        });
        MySched.SelectionManager.on('lectureDel', function () {
          Ext.ComponentMgr.get('btnDel').disable();
        });

		MySched.Tree.refreshTreeData();

		var tree = MySched.Tree.tree;

        var id = tree.root.childNodes[0].attributes.id;

        id = id + ".delta";

        // Initialisiert "Änderungen"
        MySched.delta = new mSchedule(id, "Änderungen (zentral)");
     	MySched.delta.responsible = "delta";

		Ext.Msg.show({
          	id: 'ajaxloader',
            cls: 'mySched_noBackground',
            closable: false,
            msg: '<div class="ajaxloader"/>'
        });

        if (MySched.SessionId) {
          MySched.Authorize.verifyToken(MySched.SessionId, MySched.Authorize.verifySuccess, MySched.Authorize);
          // Lädt Delta Daten
          MySched.delta.load(_C('ajaxHandler'), 'json', MySched.delta.loadsavedLectures, function (params) {}, this, "delta");
        }
        else {
          MySched.delta.load(_C('ajaxHandler'), 'json', MySched.delta.loadsavedLectures, function (params) {
            var deltaSched = new mSchedule(id, "Änderungen (zentral)").init("delta", id);
            deltaSched.show();
            //MySched.selectedSchedule.grid.showSporadics();
            MySched.layout.viewport.doLayout();
            MySched.selectedSchedule.responsible = "delta";
            MySched.selectedSchedule.status = "saved";
          }, this, "delta");
        }
      }
      else {
        Ext.ComponentMgr.get('leftMenu').setTitle("ungültiger Stundenplan");
        Ext.ComponentMgr.get('topMenu').disable();
      }
    },
    /**
     * Erstellt den Tab "Mein Stundenplan"
     */
    createUserSchedule: function () {
      if (!MySched.layout.tabpanel.getItem('mySchedule')) {
        var grid = MySched.Schedule.show(true);
        Ext.apply(grid, {
          closable: false,
          tabTip: 'Mein Stundenplan',
          iconCls: 'myScheduleIcon'
        });

        MySched.layout.createTab('mySchedule', 'Mein Stundenplan', grid, "mySchedule");
        // tab 'Mein Stundenplan' wird DropArea
        var dropTarget = new Ext.dd.DropTarget(Ext.get('tabpanel__mySchedule'), this.getDropConfig());
      }
    },
    /**
     * Laed den von dem User definierten Stundenplan
     */
    loadUserSchedule: function () {
      MySched.Schedule.load(_C('ajaxHandler'), 'json', MySched.Schedule.preParseLectures, function (params) {
        // Alles OK
        if (params.result && params.result.success) {
          if (MySched.layout.tabpanel.getItem('mySchedule')) {
            MySched.Schedule.save(_C('ajaxHandler'), false, "UserSchedule.save");
	        var func = function () {
	        	MySched.SelectionManager.stopSelection();
	       		MySched.SelectionManager.startSelection();
	        }
	        func.defer(50);
            MySched.selectedSchedule.eventsloaded = null;
            MySched.selectedSchedule.refreshView();
            //MySched.Schedule.checkLectureVersion( MySched.Base.schedule );
          } else {
            var grid = MySched.Schedule.show(true);
            Ext.apply(grid, {
              closable: false,
              tabTip: 'Mein Stundenplan',
              iconCls: 'myScheduleIcon'
            });
            MySched.layout.createTab('mySchedule', 'Mein Stundenplan', grid, "mySchedule");
          }
          // Buttons aktivieren wenn nicht leer
          if (!MySched.Schedule.isEmpty()) {
            Ext.ComponentMgr.get('btnEmpty').enable();
            Ext.ComponentMgr.get('btnPdf').enable();
            if (_C('enableSubscribing')) Ext.ComponentMgr.get('btnSub').enable();
          }

          // tab 'Mein Stundenplan' wird DropArea
          var dropTarget = new Ext.dd.DropTarget(Ext.get('tabpanel__mySchedule'), this.getDropConfig());
        }
      }, this, MySched.Authorize.user);
      //MySched.selectedSchedule.grid.showSporadics();
      MySched.layout.viewport.doLayout();

	  Ext.MessageBox.hide();
    },
    /**
     * Gibt die Drop-Konfiguration fuer Drag'n'Drop zurueck
     */
    getDropConfig: function () {
      // Definiert Konfiguration fuer Drag'n'Drop
      return {
        ddGroup: 'lecture',
        // Akzeptiert lectures
        notifyDrop: function (dd, e, data) {
          if (data.node) {
            // Fuegt gesammten SemesterPlan dem eigenen Stundenplan hinzu
            var n = data.node;

            var key = n.attributes.id;
            var gpuntisID = n.attributes.gpuntisID;
            var semesterID = n.attributes.semesterID;
            var plantype = n.attributes.plantype;
            var type = n.attributes.type;

            if (MySched.loadedLessons[key] != true) {
              Ext.Ajax.request({
                url: _C('ajaxHandler'),
                method: 'POST',
                params: {
                  res: gpuntisID,
                  class_semester_id: semesterID,
                  scheduletask: "Ressource.load",
                  plantype: plantype,
                  type: type
                },
                failure: function (response) {},
                success: function (response) {
                  try {
                    var json = Ext.decode(response.responseText);
                    for (var item in json) {
                      if (Ext.isObject(json[item])) {
                        var record = new mLecture(json[item].key, json[item]);
                        MySched.Base.schedule.addLecture(record);
                        MySched.TreeManager.add(record);
                      }
                    }

                    if (typeof json["elements"] != "undefined") {
                      n.elements = json["elements"];
                      var s = new mSchedule(key, '_tmpSchedule').init(type, json["elements"]);
                    }
                    else var s = new mSchedule(key, '_tmpSchedule').init(type, gpuntisID);

                    Ext.each(s.getLectures(), function (e) {
                      MySched.Schedule.addLecture(e);
                    });
                    MySched.loadedLessons[key] = true;
                    MySched.selectedSchedule.eventsloaded = null;
                    MySched.Schedule.refreshView();
                  }
                  catch(e)
                  {}
                }
              });
            }
            else {

              if (typeof n.elements != "undefined")
              	var s = new mSchedule(key, '_tmpSchedule').init(type, n.elements);
              else
              	var s = new mSchedule(key, '_tmpSchedule').init(type, gpuntisID);

              Ext.each(s.getLectures(), function (e) {
                MySched.Schedule.addLecture(e);
              });
              MySched.selectedSchedule.eventsloaded = null;
              MySched.Schedule.refreshView();
            }
          } else {
            // Fuegt Veranstaltung zu eigenem Stundenplan hinzu
            MySched.Schedule.addLecture(MySched.Base.schedule.getLecture(data.id));
            MySched.selectedSchedule.eventsloaded = null;
            MySched.Schedule.refreshView();
          }
          return true;
        }
      };
    },
    /**
     * Gibt die Veranstaltung mit der id zurueck
     * @param {Object} id VeranstlatungsID
     */
    getLecture: function (id) {
      return this.schedule.getLecture(id);
    },
    /**
     * Gibt nur bestimmte Lectures zurueck
     * @param {Object} type Ueber welches Feld soll Selektiert werden
     * @param {Object} value Welchen Wert muss dieses Feld haben
     * @return {MySched.Collection}
     */
    getLectures: function (type, value) {
      return this.schedule.getLectures(type, value);
    },
    /**
     * Handelt den FreeBusy Zustand - wird beim schalten des Buttons aufgerufen
     * @param {Object} e Event welches ausgeloest wurde
     * @param {Object} state Zustand des Buttons
     */
    freeBusyHandler: function (e, state) {
      if (!state) {
        Ext.select('.blockFree').replaceClass('blockFree', 'blockFree_DIS');
        Ext.select('.blockBusy').replaceClass('blockBusy', 'blockBusy_DIS');
        Ext.select('.blockOccupied').replaceClass('blockOccupied', 'blockOccupied_DIS');
      } else {
        Ext.select('.blockFree_DIS').replaceClass('blockFree_DIS', 'blockFree');
        Ext.select('.blockBusy_DIS').replaceClass('blockBusy_DIS', 'blockBusy');
        Ext.select('.blockOccupied_DIS').replaceClass('blockOccupied_DIS', 'blockOccupied');
      }
      // Legt neuen State fest
      MySched.freeBusyState = state;
    }
  }
}();

/**
 * Zeigt die Informationen zur augewaehlten Lecture an
 */
MySched.InfoPanel = function () {
  var el = null;
  return {
    init: function () {
      this.el = Ext.get('infoPanel');
    },
    /**
     * Zeigt eine Info in dem Info Panel unterhalb des Baumes an
     * @param {Object} el HTML Element welches selektiert wurde
     */
    showInfo: function (el) {
      var text = false;
      if (Ext.type(el) == 'object') {

        var l = MySched.Base.getLecture(el.id);
        if (l) {
          text = l.showInfoPanel();
        }
      } else {
        text = el;
      }
      if (!text) this.el.update("Fehler: Veranstaltung existiert nicht mehr!");
      else {
        this.el.update(text);
      }
      // Updated Handler fuer Detailinfos
      this.updateDetailInfoClickHandler();
    },
    /**
     * Erneuert die onClick events der InfoIcons innerhalb des InfoPanels
     */
    updateDetailInfoClickHandler: function () {
      this.el.select('.detailInfoBtn').on('click', this.detailInfoClick, this);
    },
    /**
     * Wird aufgerufen wenn ein blaues Informationsicon fuer Detailinfos geklickt wird
     * @param {Object} e Event welches ausgeloest wurde
     */
    detailInfoClick: function (e) {
      // Splitte Id - zb. info_room_i136
      var tmp = e.target.id.split('_');
      // Holt die geforderte Info vom Server ab.
      Ext.Ajax.request({
        url: _C('infoUrl'),
        params: {
          type: tmp[1],
          key: tmp[2],
          viewMode: _C('infoMode')
        },
        method: 'POST',
        failure: function () {
          Ext.Msg.alert("Hinweis", 'Es ist ein Fehler beim ermitteln der Information aufgetreten.');
        },
        scope: this,
        success: function (resp) {
	        try {
	          var json = Ext.decode(resp.responseText);
	          if (!json.success) {
	            if (!json.error) json.error = 'Unbekannter Fehler!';
	            this.showDetailInfo(json.error, 'Fehler');
	            return;
	          }
	          // Zeigt ermittelte Info an
	          this.showDetailInfo(new Ext.Template(json.template).apply(json.data), 'Information');
	        }
	        catch(e)
	        {}
        }
      });
    },
    /**
     * Zeigt Detailierte Info an
     * @param {Object} text
     * @param {Object} title
     */
    showDetailInfo: function (text, title) {
      var mode = _C('infoMode');
      // Je nach Mode wird es im normalen InfoFenster oder als Popup angezeigt.
      if (mode == 'layout') {
        this.showInfo(text);
      } else if (mode == 'popup') {
        Ext.Msg.show({
          title: title,
          buttons: {
            cancel: 'Schlie&szlig;en'
          },
          msg: text,
          width: 400,
          modal: false,
          closable: true
        });
      }
    },
    /**
     * Leert das InfoFenster
     */
    clearInfo: function () {
      Ext.get('infoPanel').update('');
    }
  }
}();


/**
 * Steuert die Auswahl der Veranstaltungen und die Hoverbuttons
 */
MySched.SelectionManager = Ext.apply(new Ext.util.Observable(), {
  selectEl: null,
  hoverEl: new MySched.Collection(),
  selectButton: null,
  selectLectureId: null,
  lectureAddButton: externLinks.lectureAddButton,
  lectureRemoveButton: externLinks.lectureRemoveButton,
  /**
   * Initalisierung
   */
  init: function () {
    // Definierten welche Events geworfen werden
    this.addEvents({
      beforeSelect: true,
      select: true,
      beforeUnselect: true,
      unselect: true,
      lectureAdd: true,
      lectureDel: true
    });
    // Erstellt den Hoverbutton fuer + und -
    this.selectButton = Ext.DomHelper.append(Ext.getBody(), {
      tag: 'img',
      src: this.lectureAddButton,
      id: 'lectureSelectButton',
      style: 'z-index: 1000;',
      qtip: 'Veranstaltung Ihrem Stundenplan hinzuf&uuml;gen'
    }, true);
    this.selectButton.on('click', this.lecture2ScheduleHandler, this);
  },
  /**
   * Stoppt die Selektierung
   * @param {Object} o
   * o == leer  => fuer den aktiven Tab
   * o==true => dann fuer document, und wenn
   * o == Ext.Element|Node => nur fuer dieses
   */
  stopSelection: function (o) {
    if (Ext.type(o) == 'object') { // Nur unterhalb uebergebenen Objekt
      var dom = o.dom || Ext.get(o).dom;
      Ext.select('.status_icons_delete', false, dom).removeAllListeners();
      Ext.select('.status_icons_info', false, dom).removeAllListeners();
      Ext.select('.status_icons_edit', false, dom).removeAllListeners();
      Ext.select('.dozname', false, dom).removeAllListeners();
      Ext.select('.lectureBox', false, dom).removeAllListeners();
      Ext.select('.conMenu', false, dom).removeAllListeners();
      Ext.select('.MySchedEvent_joomla', false, dom).removeAllListeners();
      Ext.select('.lecturename', false, dom).removeAllListeners();
      Ext.select('.roomshortname', false, dom).removeAllListeners();
      Ext.select('.classhorter', false, dom).removeAllListeners();
      Ext.select('.status_icons_add', false, dom).removeAllListeners();
      Ext.select('.status_icons_info', false, dom).removeAllListeners();
      Ext.select('.status_icons_estudy', false, dom).removeAllListeners();
    } else if (o === true) { // Alle
      Ext.select('.status_icons_delete').removeAllListeners();
      Ext.select('.status_icons_info').removeAllListeners();
      Ext.select('.status_icons_edit').removeAllListeners();
      Ext.select('.dozname').removeAllListeners();
      Ext.select('.lectureBox').removeAllListeners();
      Ext.select('.conMenu').removeAllListeners();
      Ext.select('.MySched_event_joomla').removeAllListeners();
      Ext.select('.lecturename').removeAllListeners();
      Ext.select('.roomshortname').removeAllListeners();
      Ext.select('.classhorter').removeAllListeners();
      Ext.select('.status_icons_add').removeAllListeners();
      Ext.select('.status_icons_info').removeAllListeners();
      Ext.select('.status_icons_estudy').removeAllListeners();
    } else if (MySched.layout.tabpanel.items.getCount() > 0) { // Nur Aktiven Tab
      Ext.select('.status_icons_delete', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.status_icons_info', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.status_icons_edit', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.dozname', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.lectureBox', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.conMenu', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.MySchedEvent_joomla', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.lecturename', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.roomshortname', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.classhorter', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.status_icons_add', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.status_icons_info', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
      Ext.select('.status_icons_estudy', false, MySched.layout.tabpanel.getActiveTab().getEl().dom).removeAllListeners();
    }
  },
  /**
   * Startet die Selektierung
   * @param {Object} el Tab fuer den die Selektierung gestartet werden soll
   * wenn leer, dann fuer den aktiven Tab
   */
  startSelection: function (el) {
    var tab = el || MySched.layout.tabpanel.getActiveTab().getEl();
    if (!tab) return;
    this.stopSelection(tab);

    Ext.select('.status_icons_estudy', false, tab.dom).on({
      'click': function (e) {
        if (e.button == 0) //links Klick
        {
          e.stopEvent();
          this.showModuleInformation(e);
        }
      },
      scope: this
    });

    Ext.select('.status_icons_delete', false, tab.dom).on({
      'click': function (e) {
        if (e.button == 0) //links Klick
        {
          e.stopEvent();
          MySched.SelectionManager.deleteLesson();
        }
      },
      scope: this
    });

    Ext.select('.status_icons_info', false, tab.dom).on({
      'click': function (e) {
        if (e.button == 0) //links Klick
        {
          e.stopEvent();
          this.showModuleInformation(e);
        }
      },
      scope: this
    });

    Ext.select('.status_icons_edit', false, tab.dom).on({
      'click': function (e) {
        if (e.button == 0) //links Klick
        {
          e.stopEvent();
          MySched.SelectionManager.editLesson();
        }
      },
      scope: this
    });

    // Aboniert Events für Dozentennamen
    Ext.select('.dozname', false, tab.dom).on({
      'click': function (e) {
        if (e.button == 0) //links Klick
        this.showschedule(e, 'doz');
      },
      scope: this
    });

    // Aboniert Events für Lecturenamen
    Ext.select('.lecturename', false, tab.dom).on({
      'mouseover': function (e) {
        e.stopEvent();
        this.showInformation(e);
      },
      'mouseout': function () {
        if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip').destroy();
      },
      scope: this
    });

    // Aboniert Events f�r Dozentennamen
    Ext.select('.MySchedEvent_joomla', false, tab.dom).on({
      'mouseover': function (e) {
        if (e.button == 0) //links Klick
        {
          e.stopEvent();
          this.showEventInformation(e);
        }
      },
      'mouseout': function () {
        if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip').destroy();
      },
      'click': function (e) {
        if (e.button == 0) //links Klick
        e.stopEvent();
        if (MySched.Authorize.user != null && MySched.Authorize.role != 'user' && MySched.Authorize.role != 'registered') addNewEvent(e.target.id);
      },
      scope: this
    });

    // Aboniert Events für Dozentennamen
    Ext.select('.roomshortname', false, tab.dom).on({
      'click': function (e) {
        if (e.button == 0) //links Klick
        this.showschedule(e, 'room');
      },
      scope: this
    });

    // Aboniert Events f�r Dozentennamen
    Ext.select('.classhorter', false, tab.dom).on({
      'click': function (e) {
        this.showschedule(e, 'clas');
      },
      scope: this
    });

    // Aboniert Events der Veranstaltungsboxen
    Ext.select('.lectureBox', false, tab.dom).on({
      'mousedown': this.onMouseDown,
      'dblclick': this.ondblclick,
      'contextmenu': function (e) {
        showLessonMenu(e);
      },
      scope: this
    });

    // Aboniert Events der Veranstaltungsboxen
    Ext.select('.status_icons_add', false, tab.dom).on({
      'click': function (e) {
        e.stopEvent();
        this.lecture2ScheduleHandler()
      },
      scope: this
    });

    Ext.select('.conMenu', false, tab.dom).on({
      'contextmenu': function (e) {
        showBlockMenu(e);
      },
      scope: this
    });
  },
  showschedule: function (e, type) {
    var id = "";
    var name = e.target.firstChild.nodeValue.replace(/^\s+/, '').replace(/\s+$/, '');
    var parentid = e.target.parentNode.id;
    var lessonid = parentid.split("##");
    var l = null;
    l = MySched.Base.getLecture(lessonid[1]);
    if (typeof l == "undefined") l = MySched.Schedule.getLecture(lessonid[1]);

    if (type == "doz") {
      for (var i = 0; i < l.doz.keys.length; i++) {
        if (name == MySched.Mapping.getDozName(l.doz.keys[i])) {
          id = l.doz.keys[i];
          break;
        }
      }
    }
    else if (type == "room") {
      for (var i = 0; i < l.room.keys.length; i++) {
        var room = MySched.Mapping.getRoomName(l.room.keys[i]).split("/");
        if (name == room[room.length - 1].replace(/^\s+/, '').replace(/\s+$/, '')) {
          id = l.room.keys[i];
          break;
        }
      }
    }
    else {
      for (var i = 0; i < l.clas.keys.length; i++) {
        if (name == l.clas.keys[i].replace("CL_", "")) {
          id = l.clas.keys[i];
          break;
        }
      }
    }
    var title = MySched.Mapping.getName(type, id);
    if (MySched.loadedLessons[id] != true) {
      Ext.Ajax.request({
        url: _C('ajaxHandler'),
        method: 'POST',
        params: {
          res: id,
          type: type,
          plantype: 1,
          class_semester_id: MySched.class_semester_id,
          scheduletask: "Ressource.load"
        },
        failure: function (response) {},
        success: function (response) {
          try {
            var json = Ext.decode(response.responseText);
            for (var item in json) {
              if (Ext.isObject(json[item])) {
                var record = new mLecture(json[item].key, json[item]);
                MySched.Base.schedule.addLecture(record);
                MySched.TreeManager.add(record);
              }
            }
            new mSchedule(id, title).init(type, id).show();
            MySched.loadedLessons[id] = true;
          }
          catch(e)
          {}
        }
      });
    }
    else {
      new mSchedule(id, title).init(type, id).show();
    }
  },
  showEventInformation: function (e) {
    var el = e.getTarget('.MySchedEvent_joomla', 5, true);
    if (!el) el = e.getTarget('.MySchedEvent_name', 5, true);
    if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip').destroy();
    var xy = el.getXY();
    xy[0] = xy[0] + el.getWidth() + 10;
    var l = MySched.eventlist.getEvent(el.id);
    var ttInfo = new Ext.ToolTip({
      title: '<div class="MySchedEvent_tooltip"> ' + l.data.title + " " + '</div>',
      id: 'content-anchor-tip',
      target: 'leftCallout',
      anchor: 'left',
      autoHide: false,
      html: l.getEventInfoView(),
      cls: "mySched_tooltip_index"
    });

    ttInfo.showAt(xy);
  },
  showModuleInformation: function (e) {
    if (typeof e == "undefined") {
      var id = "";
      if (this.selectLectureId) {
        id = this.selectLectureId;
        var el = Ext.get(id);
      } else {
        var el = this.selectEl;
        id = el.id;
      }
    }
    else {
      var el = e.getTarget('.lectureBox', 5, true);
    }

    var l = MySched.selectedSchedule.getLecture(el.id);
    l = l.data;
    if (typeof l.moduleID == "undefined") {
      Ext.Msg.alert('Hinweis', 'Für diese Veranstaltung ist keine Modulnummer hinterlegt');
      return;
    }

    if (l.moduleID == "" || l.moduleID == null) {
      Ext.Msg.alert('Hinweis', 'Für diese Veranstaltung ist keine Modulnummer hinterlegt');
      return;
    }

    var modulewin = new Ext.Window({
      layout: 'form',
      id: 'moduleWin',
      width: 564,
      height: 450,
      modal: true,
	  frame:false,
	  hideLabel: true,
      closeable: true,
      html: '<iframe id="iframeModule" class="mysched_iframeModule" src="' + externLinks.lsfLink + '&nrmni=' + l.moduleID.toUpperCase() + '"></iframe>'
    });

    modulewin.show();

  },
  showInformation: function (e) {
    if (typeof e == "undefined") {
      var id = "";
      if (this.selectLectureId) {
        id = this.selectLectureId;
        var el = Ext.get(id);
      } else {
        var el = this.selectEl;
        id = el.id;
      }
    }
    else {
      var el = e.getTarget('.lectureBox', 5, true);
    }

    if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip').destroy();

    var xy = el.getXY();
    xy[0] = xy[0] + el.getWidth() + 10;

    var l = MySched.selectedSchedule.getLecture(el.id);
    var title = l.data.desc;
    if(l.longname != "")
    	title = l.longname
    var ttInfo = new Ext.ToolTip({
      title: '<div class="mySched_lesson_tooltip"> ' + l.data.desc + " " + '</div>',
      id: 'content-anchor-tip',
      target: 'leftCallout',
      anchor: 'left',
      html: l.showInfoPanel(),
      autoHide: false,
      cls: "mySched_tooltip_index",
      listeners: {

      }
    });

    ttInfo.showAt(xy);
  },
  /**
   * Wenn das MouseOver Event ausgeloest wurde
   * @param {Object} e Event
   */
  onMouseOver: function (e) {
    // Ermittelt Aktive Veranstaltung
    var el = e.getTarget('.lectureBox', 5, true);
    if (el.id.substr(0, 4) != "delta" && MySched.Authorize.user != null && MySched.Authorize.role != "user") {
      this.selectLectureId = el.id;
      // Wenn Veranstaltung vorhanden, setze HoverButton auf Entfernen
      if (MySched.Schedule.lectureExists(el.id)) {
        this.selectButton.dom.src = this.lectureRemoveButton;
        this.selectButton.dom.qtip = 'Veranstaltung aus Ihrem Stundenplan entfernen';
      }
      // Zeige HoverButton an
      this.selectButton.show().alignTo(el, 'tr-tr', [-4, 5]);
    }
  },
  /**
   * Wenn das MouseOut Event ausgeloest wurde
   * @param {Object} e Event
   */
  onMouseOut: function (e) {
    var el = Ext.get(e.getRelatedTarget());
    // Blendet HoverButton aus, und resetet ihn auf hinzufuegen
    if (!el || el.id != 'lectureSelectButton') {
      this.selectButton.hide();
      this.selectButton.dom.src = this.lectureAddButton;
      this.selectLectureId = null;
      this.selectButton.dom.qtip = 'Veranstaltung Ihrem Stundenplan hinzuf&uuml;gen';
    }
  },
  /**
   * Beim klick auf dem HoverButton ausgeloest,
   * be DD oder Klick auf einen Button
   * Entfernt oder Fuegt Veranstaltung hinzu
   */
  lecture2ScheduleHandler: function () {
    // Aktion ueber HoverButton ausgeloest
    if (this.selectLectureId) {
      var id = this.selectLectureId;
      var el = Ext.get(id);
      // oder ueber DD oder ButtonLeiste
    } else {
      var el = this.selectEl;
      var id = el.id;
    }
    if (el.id.substr(0, 4) != "delta" && MySched.Authorize.user != null && MySched.Authorize.role != "user") {
      // Entfernt Veranstaltung
      if (el.hasClass('lectureBox_cho') || id.split('##')[0] == 'mySchedule') {
        if (typeof MySched.Base.getLecture(id) != "undefined") MySched.Schedule.removeLecture(MySched.Base.getLecture(id));
        else MySched.Schedule.removeLecture(MySched.Schedule.getLecture(id));
        // Minus Icon kann ueber mouseout nicht mehr ausgeblendet werden -> Also Manuell
        this.selectButton.hide();
        this.selectButton.dom.src = this.lectureAddButton;
        this.selectLectureId = null;
        this.selectButton.dom.qtip = 'Veranstaltung Ihrem Stundenplan hinzuf&uuml;gen';
        this.fireEvent("lectureDel", el);
        // Fuegt Veranstaltung hinzu
      } else {
        MySched.Schedule.addLecture(MySched.Base.getLecture(id));
        this.fireEvent("lectureAdd", el);
      }

      el.toggleClass('lectureBox_cho');

      // Refresh
      MySched.selectedSchedule.refreshView();
      MySched.Schedule.refreshView();
    }
  },
  /**
   * Edit a Lesson
   */
  editLesson: function () {
    if (this.selectLectureId) {
      var id = this.selectLectureId;
      var el = Ext.get(id);
      // oder ueber DD oder ButtonLeiste
    } else {
      var el = this.selectEl;
      var id = el.id;
    }
    var lesson = MySched.Base.getLecture(id);
    newPEvent(numbertoday(lesson.data.dow), lesson.data.stime, lesson.data.etime, lesson.data.subject, lesson.data.doz.replace(/\s+/g, ','), lesson.data.clas.replace(/\s+/g, ','), lesson.data.room.replace(/\s+/g, ','), lesson.data.lock, lesson.data.key);
  },
  /**
   * Delete a Lesson
   */
  deleteLesson: function (id) {
    if (!id) {
      if (this.selectLectureId) {
        id = this.selectLectureId;
        var el = Ext.get(id);
        // oder ueber DD oder ButtonLeiste
      } else {
        var el = this.selectEl;
        id = el.id;
      }
    }

    var tab = MySched.layout.tabpanel.getItem(MySched.Base.schedule.getLecture(id).data.responsible);
    if (tab) tab.mSchedule.removeLecture(tab.mSchedule.getLecture(id));
    MySched.selectedSchedule.removeLecture(MySched.selectedSchedule.getLecture(id));
    MySched.Schedule.removeLecture(MySched.Schedule.getLecture(id));
    MySched.responsibleChanges.removeLecture(MySched.responsibleChanges.getLecture(id));
    MySched.Base.schedule.removeLecture(MySched.Base.schedule.getLecture(id));

    // Minus Icon kann ueber mouseout nicht mehr ausgeblendet werden -> Also Manuell
    this.selectButton.hide();
    this.selectButton.dom.src = this.lectureAddButton;
    this.selectLectureId = null;
    this.selectButton.dom.qtip = 'Veranstaltung Ihrem Stundenplan hinzuf&uuml;gen';
    this.fireEvent("lectureDel", el);

    // Refresh
    MySched.selectedSchedule.refreshView();
    MySched.Schedule.refreshView();
  },
  /**
   * MouseDown Event ausgeloest
   * @param {Object} e Event
   */
  onMouseDown: function (e) {
    var el = e.getTarget('.lectureBox', 5, true)
    if (el == null) return; // Element ist schon selektiert
    // Selektiere Element
    if (!Ext.isEmpty(this.selectEl)) this.unselect(this.selectEl);
    this.select(el)
    this.selectEl = el;

  },
  ondblclick: function (e) {
    this.lecture2ScheduleHandler();
  },
  /**
   * Waehlt Veranstaltung aus
   * @param {Object} el Veranstaltungselement
   */
  select: function (el) {
    if (this.fireEvent("beforeSelect", el) === false) return el.addClass('lectureBox_sel');

    this.fireEvent("select", el); // Aboniert Events f�r Dozentennamen
  },
  /**
   * Waehlt Veranstaltung ab
   * @param {Object} el Veranstaltungselement
   */
  unselect: function (el) {
    if (el == null) if (this.selectEl) el = this.selectEl;
    else return false;
    if (this.fireEvent("beforeUnselect", el) === false) return el.removeClass('lectureBox_sel');
    this.fireEvent("unselect", el);
  }
});

function stripHTML(oldString) {

  var newString = "";
  var inTag = false;
  for (var i = 0; i < oldString.length; i++) {

    if (oldString.charAt(i) == '<') inTag = true;
    if (oldString.charAt(i) == '>') {
      if (oldString.charAt(i + 1) == "<") {
        //dont do anything
      }
      else {
        inTag = false;
        i++;
      }
    }

    if (!inTag) newString += oldString.charAt(i);

  }

  return newString;
}

function gotoExtURL(url, text) {
  if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip').destroy();
  var tabs = MySched.layout.tabpanel.items.items;
  var tosave = false;
  var bla = document;

  var myschedextwin = Ext.DomQuery.select('iframe[id=MySchedexternURL]', document);
  var myschedmainwin = Ext.DomQuery.select('div[id=MySchedMainW]', document);

  for (var i = 0; i < tabs.length; i++) {
    if (tabs[i].mSchedule.status == "unsaved") {
      tosave = true;
      Ext.Msg.show({
        title: "",
        buttons: Ext.Msg.YESNOCANCEL,
        buttonText: {
          cancel: 'Abbrechen'
        },
        msg: text + "<br/>M&ouml;chten sie ihre &Auml;nderungen speichern?",
        width: 400,
        modal: true,
        cls: "mySched_gotoMessage_index",
        fn: function (btn) {
          if (btn == "cancel") {
            Ext.MessageBox.hide();
            return;
          }
          if (btn == "yes") {
            var temptabs = MySched.layout.tabpanel.items.items;
            for (var ti = 0; ti < temptabs.length; ti++) {
              if (temptabs[ti].mSchedule.status == "unsaved") {
                if(temptabs[ti].mSchedule.id == "mySchedule")
                  temptabs[ti].mSchedule.save(_C('ajaxHandler'), false, "UserSchedule.save");
                else
                  temptabs[ti].mSchedule.save(_C('ajaxHandler'), false, "ScheduleChanges.save");
              }
            }
          }
          myschedextwin[0].src = url;
          myschedextwin[0].className = "MySchedexternURLClass";
          myschedmainwin[0].style.display = "none";
        },
        icon: Ext.MessageBox.QUESTION
      });
      break;
    }
  }
  if (!tosave) {
    Ext.Msg.show({
      title: "",
      buttons: Ext.Msg.OKCANCEL,
      buttonText: {
        cancel: 'Abbrechen'
      },
      msg: text,
      width: 400,
      modal: true,
      cls: "mySched_gotoMessage_index",
      fn: function (btn) {
        if (btn == "cancel") {
          Ext.MessageBox.hide();
          return;
        }
        myschedextwin[0].src = url;
        myschedextwin[0].className = "MySchedexternURLClass";
        myschedmainwin[0].style.display = "none";
      },
      icon: Ext.MessageBox.QUESTION
    });
  }
}

function showLessonMenu(e) {
  e.stopEvent();
  var el = e.getTarget('.lectureBox', 5, true);
  var lesson = MySched.Base.getLecture(el.id);
  if (typeof lesson == "undefined") lesson = MySched.Schedule.getLecture(el.id);

  var rMenu = Ext.getCmp('responsibleMenu');
  var oMenu = Ext.getCmp('ownerMenu');
  if (rMenu) rMenu.destroy();
  if (oMenu) oMenu.destroy();

  var editLesson = {
    text: "&Auml;ndern",
    icon: MySched.mainPath + "images/icon-edit.png",
    handler: function () {
      MySched.SelectionManager.editLesson();
    }
  }

  var deleteLesson = {
    text: "L&ouml;schen",
    icon: MySched.mainPath + "images/icon-delete.png",
    handler: function () {
      MySched.SelectionManager.deleteLesson();
    }
  }

  var addLesson = {
    text: "Hinzuf&uuml;gen",
    icon: MySched.mainPath + "images/add.png",
    handler: function () {
      MySched.SelectionManager.selectEl = el;
      MySched.SelectionManager.lecture2ScheduleHandler();
    }
  }

  var delLesson = {
    text: "Entfernen",
    icon: MySched.mainPath + "images/delete.png",
    handler: function () {
      MySched.SelectionManager.selectEl = el;
      MySched.SelectionManager.lecture2ScheduleHandler();
    }
  }

  var estudyLesson = {
    text: "eStudy",
    icon: MySched.mainPath + "images/estudy_logo.jpg",
    handler: function () {
      MySched.SelectionManager.showModuleInformation();
    }
  }

  var infoLesson = {
    text: "Informationen",
    icon: MySched.mainPath + "images/information.png",
    handler: function () {
      MySched.SelectionManager.showModuleInformation();
    }
  }

  var menuItems = [];

  if (MySched.Authorize.role != "user") {
    //menuItems[menuItems.length] = estudyLesson;
    if (MySched.selectedSchedule.id == "mySchedule" || el.hasClass('lectureBox_cho')) {
      menuItems[menuItems.length] = delLesson;
    }
    else {
      if (MySched.selectedSchedule.type != 'delta') {
        menuItems[menuItems.length] = addLesson;
      }
    }

  }
  if (((lesson.data.owner == MySched.Authorize.user || MySched.Authorize.isClassSemesterAuthor()) && lesson.data.owner != null) && lesson.data.owner != "gpuntis") {
    menuItems[menuItems.length] = editLesson;
    menuItems[menuItems.length] = deleteLesson;
  }

  menuItems[menuItems.length] = infoLesson;

  var menu = new Ext.menu.Menu({
    id: 'ownerMenu',
    items: menuItems
  });

  if (menuItems.length > 0) menu.showAt(e.getXY());
}

function showBlockMenu(e) {
  e.stopEvent();

  if ((MySched.selectedSchedule.responsible == MySched.Authorize.user && MySched.selectedSchedule.responsible != null) || MySched.Authorize.isClassSemesterAuthor()) {
    var rMenu = Ext.getCmp('responsibleMenu');
    var oMenu = Ext.getCmp('ownerMenu');
    if (typeof rMenu != "undefined") rMenu.destroy();
    if (typeof oMenu != "undefined") oMenu.destroy();

    var el = e.getTarget('.conMenu', 5, true);
    MySched.BlockMenu.stime = el.getAttribute("stime");
    MySched.BlockMenu.etime = el.getAttribute("etime");
    MySched.BlockMenu.day = numbertoday(el.dom.cellIndex);

    var menu = new Ext.menu.Menu({
      id: 'responsibleMenu',
      items: MySched.BlockMenu.Menu
    });

    if (MySched.BlockMenu.Menu.length > 0) menu.showAt(e.getXY());
  }

}

/**
 * Ist fuer die Verwaltung und Erstellung der Uebersichtslisten zustaendig
 */
MySched.TreeManager = function () {
  var dozTree, roomTree, clasTree, curteaTree; //neu

  return {
    /**
     * Initialisierung
     */
    init: function () {
      this.dozTree = new MySched.Collection();
      this.roomTree = new MySched.Collection();
      this.clasTree = new MySched.Collection();
      this.curteaTree = new MySched.Collection();//neu
    },
    afterloadEvents: function (arr, refresh) {
      for (var e in arr) {
        if (Ext.isObject(arr[e])) {
          var event = new mEvent(arr[e].eid, arr[e]);
          MySched.eventlist.addEvent(event);
        }
      }
      //MySched.eventlist = arr;
    },
    /**
     * Fuegt den Listen eine Veranstaltung hinzu
     * @param {Object} lecture Veranstaltung
     */
    add: function (lecture) {
      if (Ext.isObject(lecture)) {
        this.dozTree.addAll(lecture.getDoz().asArray());
        this.roomTree.addAll(lecture.getRoom().asArray());
        this.clasTree.addAll(lecture.getClas().asArray());
      }
    },
    /**
     * Erstellt die Dozenten Uebersichtsliste
     * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
     */
    createDozTree: function (tree) {
      return this.createTree(tree, 'doz', this.dozTree, 'Dozentenplan');
    },
    /**
     * Erstellt die Raum Uebersichtsliste
     * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
     */
    createRoomTree: function (tree) {
      return this.createTree(tree, 'room', this.roomTree, 'Raumplan');
    },
    /**
     * Erstellt die Studiengang Uebersichtsliste
     * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
     */
    createClasTree: function (tree) {
      return this.createTree(tree, 'clas', this.clasTree, 'Semesterplan');
    },
    /**
     * Erstellt die �nderungen
     * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
     */
    createDiffTree: function (tree) {
      return this.createTree(tree, 'diff');
    },
    /**
     * Erstellt die �nderungen von Verantwortlichen
     * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
     */
    createrespChangesTree: function (tree) {
      return this.createTree(tree, 'respChanges');
    },
     /**
     * Sucht alle Lessons
     * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
     */
    createCurteaTree: function (tree) {//neu->
      return this.createTree(tree, 'curtea', this.curteaTree, 'Lehrplan');//UnsetTimes
    },

    processTreeData: function(json, type, accMode, name, baseTree)
    {
    	var children = json["tree"];
    	var treeData = json["treeData"];
    	if (accMode != 'none')
    	{
			baseTree.root.appendChild(children);
		}

		baseTree.root.renderChildren();
		baseTree.root.childNodes[0].renderChildren();

		for(var item in treeData)
			if(Ext.isObject(treeData[item]))
				for(var childitem in treeData[item])
					if(Ext.isObject(treeData[item][childitem]))
						for(var value in treeData[item][childitem])
							MySched.Mapping[item].add(treeData[item][childitem][value].id, treeData[item][childitem][value]);

    },
    /**
     * Erstellt eine Uebersichtsliste
     * @param {Object} baseTree Baum dem die Liste hinzugefuegt wird
     * @param {Object} type Typ der Liste (doz|clas|room)
     * @param {Object} data Daten Baum mit Elementen zum Hinzufuegen
     * @param {Object} name Name der Listengruppe
     */
    createTree: function (baseTree, type, data, name) {

      // Generelle Rechteuberpruefung auf diese Uebersichtsliste
      var accMode = MySched.Authorize.checkAccessMode(type);

      if (type != "diff" && type != "respChanges" && type != "curtea") {
        if(checkStartup("TreeView.load") === true)
        {
          MySched.TreeManager.processTreeData(MySched.startup["TreeView.load"].data, type, accMode, name, baseTree);
        }
        else
        Ext.Ajax.request({
          url: _C('ajaxHandler'),
          method: 'POST',
          params: {
            type: type,
            sid: MySched.class_semester_id,
            scheduletask: "TreeView.load"
          },
          failure: function (response) {
            var bla = response;
          },
          success: function (response) {
            try {
              var json = Ext.decode(response.responseText);
              MySched.TreeManager.processTreeData(json, type, accMode, name, baseTree);
            }
            catch(e)
            {}
          }
        });
      }

      if (type == "curtea") {//neu->
          MySched.TreeManager.processTreeData(MySched.startup["TreeView.curiculumTeachers"].data, type, accMode, name, baseTree);
        return ret;
      }
      // Keine Rechte, also nicht anzeigen
      if (accMode == 'none') return null;

      if (type == "diff") {
        // Fuegt die Liste der Uebersicht an
        var ret = baseTree.root.appendChild(
        new Ext.tree.TreeNode({
          text: 'Änderungen (zentral)',
          id: 'delta',
          cls: type + '-root',
          draggable: false,
          leaf: true
        }));
        return ret;
      }

      if (type == "respChanges") {
        // Fuegt die Liste der Uebersicht an
        var ret = baseTree.root.appendChild(
        new Ext.tree.TreeNode({
          text: 'Änderungen (eigene)',
          id: 'respChanges',
          cls: type + '-root',
          draggable: false,
          leaf: true
        }));
        return ret;
      }
    }
  }
}();


/**
 * Layouterstellung und Verwaltung
 */
MySched.layout = function () {
  var tabpanel, selectedTab, w_leftMenu, w_topMenu, w_infoPanel, infoWindow;

  return {
    /**
     * Gibt den Ausgewaehlten Tab zurueck
     */
    getSelectedTab: function () {
      return this.selectedTab;
    },
    /**
     * Erstellt das Grundlayout
     */
    buildLayout: function () {
      // Erstellt TabPanel
      this.tabpanel = new Ext.TabPanel({
        resizeTabs: true,
        // turn on tab resizing
        minTabWidth: 155,
        tabWidth: 155,
        enableTabScroll: true,
        //enableTabScroll: true,
        //autoScroll: true,
        id: 'tabpanel',
        region: 'center',
        //style: 'overflow-y: auto; overflow-x: hidden;',
        bodyStyle: 'overflow-y: auto; overflow-x: hidden;'
      });

      // Setzt Events fuer Aenderung des Tabpanels
      this.tabpanel.on('remove', function (panel, o) {
        MySched.SelectionManager.selectButton.hide();
        MySched.SelectionManager.unselect();
      });

      this.tabpanel.on('tabchange', function (panel, o) {
        if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip').destroy();
        MySched.selectedSchedule = o.mSchedule;
        // Aufgerufener Tab wird neu geladen
        if (MySched.selectedSchedule.status == "unsaved") {
          Ext.ComponentMgr.get('btnSave').enable();
        }
        else {
          Ext.ComponentMgr.get('btnSave').disable();
        }

        MySched.selectedSchedule.data.eachKey(function (k, v) {
          if (typeof v.setCellTemplate != "undefined") v.setCellTemplate(MySched.selectedSchedule.type);
        });

        MySched.selectedSchedule.eventsloaded = null;
        o.mSchedule.refreshView();

        // Evtl. irgendwo haengender AddLectureButton wird ausgeblendet
        MySched.SelectionManager.selectButton.hide();
        MySched.SelectionManager.unselect();
        this.selectedTab = o;

        var func = function () {
        	MySched.SelectionManager.stopSelection();
       		MySched.SelectionManager.startSelection();
        }
        func.defer(50);
      }, this);

      // Wenn der Header der FH angezeigt werden soll
      if (_C('showHeader')) {
        this.w_topMenu = new Ext.Panel({
          id: 'topMenu',
          region: 'north',
          height: 18,
          bodyStyle: 'text-align:center;',
          html: _C('headerHTML'),
          bbar: this.getMainToolbar()
        });
        // ..und wenn nicht
      } else {
        this.w_topMenu = new Ext.Panel({
          id: 'topMenu',
          region: 'north',
          height: 18,
          bbar: this.getMainToolbar()
        });
      }

      // Linker Bereich der Info und Ubersichtsliste enthaelt
      this.w_leftMenu = new Ext.Panel({
        id: 'leftMenu',
        title: ' ',
        region: 'west',
        layout: 'border',
        split: false,
        floatable: false,
        width: 242,
        minSize: 242,
        maxSize: 242,
        collapsible: true,
        collapsed: false,
        headerCfg: {
          tag: '',
          cls: 'x-panel-header mySched_techheader',
          // Default class not applied if Custom element specified
          html: ''
        },
        items: [
        MySched.Tree.init()
        //this.w_infoPanel
        ]
      });

      this.w_leftMenu.on("expand", function () {
        if (MySched.selectedSchedule) {
          MySched.selectedSchedule.eventsloaded = null;
          MySched.selectedSchedule.refreshView();
        }
      });
      this.w_leftMenu.on("collapse", function () {
        if (MySched.selectedSchedule) {
          MySched.selectedSchedule.eventsloaded = null;
          MySched.selectedSchedule.refreshView();
        }
      });

      // und schliesslich erstellung des gesamten Layouts
      this.viewport = new Ext.Panel({
        layout: "border",
        renderTo: "MySchedMainW",
        plugins: ['fittoparent'],
        items: [
        this.w_topMenu, this.w_leftMenu, this.tabpanel
        ]
      });
      var calendar = Ext.ComponentMgr.get('menuedatepicker');
      if (calendar) var imgs = Ext.DomQuery.select('img[class=x-form-trigger x-form-date-trigger]', calendar.container.dom);
      for (var i = 0; i < imgs.length; i++) {
        imgs[i].alt = "calendar";
      }
    },
    /**
     * Zeigt das Infofenster von MySched an
     */
    showInfoWindow: function () {
      if (Ext.ComponentMgr.get("infoWindow") == null || typeof Ext.ComponentMgr.get("infoWindow") == "undefined") {

        this.infoWindow = new Ext.Window({
          id: 'infoWindow',
          title: 'MySched - Studentische Stundenplanverwaltung',
          width: 675,
          height: 380,
          autoScroll: true,
          frame:false,
          bodyStyle: 'background-color: #FFF; padding: 7px;',
          buttons: [{
            text: 'Schlie&szlig;en',
            handler: function () {
              this.infoWindow.close();
            },
            scope: this
          }],
          html: "<small style='float:right; font-style:italic;'>Version " + MySched.version + "</small>" + "<p style='font-weight:bold;'>&Auml;nderungen sind farblich markiert: <br /> <p style='padding-left:10px;'> <span style='background-color: #00ff00;' >Neue Veranstaltung</span></p> <p style='padding-left:10px;'><span style='background-color: #ff4444;' >Gel&ouml;schte Veranstaltung</span></p> <p style='padding-left:10px;'><span style='background-color: #ffff00;' >Ge&auml;nderte Veranstatung (neuer Raum, neuer Dozent)</span> </p><p style='padding-left:10px;'> <span style='background-color: #ffaa00;' >Ge&auml;nderte Veranstaltung (neue Zeit:von)</span>, <span style='background-color: #ffff00;' >Ge&auml;nderte Veranstaltung (neue Zeit:zu)</span></p></p>" + "<b>Version: 2.1.6:</b>" + "<ul>" + "<li style='padding-left:10px;'>NEU: Hinzuf&uuml;gen der Veranstaltungen &uuml;ber Kontextmenu (Rechtsklick auf Veranstaltung).</li>" + "<li style='padding-left:10px;'>NEU: Hinzuf&uuml;gen von eigenen Veranstaltungen &uuml;ber Kontextmenu (Rechtsklick in einen Block).</li>" + "<li style='padding-left:10px;'>NEU: Navigation &uuml;ber den Dozent, Raum, Fachbereich einer Veranstaltung.</li>" + "<li style='padding-left:10px;'>NEU: Navigation durch einzelne Wochen &uuml;ber einen Kalender (Men&uuml;leiste).</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Anzeige von Terminen.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Informationen zu Terminen &uuml;ber Termintitel (Mauszeiger &uuml;ber Titel).</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Informationen zu Veranstaltungen &uuml;ber Veranstaltungstitel (Klick auf den Titel).</li>" + "</ul>" + "<br/>" + "<b>Version: 2.1.5:</b>" + "<ul>" + "<li style='padding-left:10px;'>NEU: Pers&ouml;nliche Termine k&ouml;nnen &uuml;ber den Men&uuml;punkt 'Neuer Termin' oder per Klick in einen Block angelegt werden.</li>" + "<li style='padding-left:10px;'>NEU: Berechtigte Benutzer d&uuml;rfen im Panel 'Einzel Termine' neue Termine anlegen oder alte editieren.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: &Auml;nderungen werden wie ein Stundenplan aufgerufen.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Nur registrierte Benutzer haben Zugriff auf alle Funktionen.</li>" + "</ul>" + "<br/>" + "<b>Version: 2.1.4:</b>" + "<li style='padding-left:10px;'>NEU: In der Infoanzeige von Veranstaltungen kann die Modulbeschreibung abgerufen werden.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Termine werden nur noch an betroffenen Tagen angezeigt.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Termine werden bei Klick auf das orangene Ausrufezeichen angezeigt.</li>" + "<br/>" + "<b>Version: 2.1.3:</b>" + "" + "<li style='padding-left:10px;'>NEU: Stundenpl&auml;ne k&ouml;nnen als Terminkalendar heruntergeladen werden. (Men&uuml;punkt ICal Download)</li>" + "<li style='padding-left:10px;'>NEU: Navigationsleiste kann eingeklappt werden.</li>" + "<li style='padding-left:10px;'>NEU: Veranstaltungen k&ouml;nnen per Doppelklick hinzugef&uuml;gt / entfernt werden.</li>" + "<li style='padding-left:10px;'>NEU: Bei &Auml;nderungen zu Ihrem abgespeicherten Plan werden jetzt sinnvolle Vorschl&auml;ge gemacht.</li>" + "<li style='padding-left:10px;'>NEU: Kontrastreiche Men&uuml;s, sinnvollere Neuanordnung des Men&uuml;s.</li>" + "<li style='padding-left:10px;'>NEU: Seitentitel auch als Titel des pdf-download.</li>" + "<li style='padding-left:10px;'>NEU: Kleinere Texte bei den Einzelterminen.</li>" + "<br/>" + "<b>Version: 2.1.2:</b>" + "" + "<li style='padding-left:10px;'>NEU: MNI Style</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: PDF Download und PDF Dateiname bezieht sich auf den aktiven Tab</li>"
        });
        this.infoWindow.show();
      }
    },
    /**
     * Erstellt einen neuen Stundenplan Tab
     * @param {Object} id ID des Tabs
     * @param {Object} title Title des Tabs
     * @param {Object} grid Grid das angezeigt werden soll
     */
    createTab: function (id, title, grid, type) {

      if (MySched.Authorize.role == "user" && id == "mySchedule") {
        //DO NOTHING
      }
      else {
        if (!(tab = this.tabpanel.getItem(id))) {
          if (type) {
            grid.mSchedule.data.eachKey(function (k, v) {
              if (typeof v.setCellTemplate != "undefined") v.setCellTemplate(type);
            });
          }
          if (MySched.Authorize.role == "user" && type == "delta") {
            var tab = Ext.apply(
            // Defaultwerte - wenn schon gesetzt bleiben sie
            Ext.applyIf(grid, {
              cls: 'schedule-tab',
              tabTip: title,
              closable: false,
              layout: 'fit'
            }), {
              // Diese werden Ueberschrieben, falls sie Existieren
              id: id,
              title: title
            });
          }
          else {
            var tab = Ext.apply(
            // Defaultwerte - wenn schon gesetzt bleiben sie
            Ext.applyIf(grid, {
              cls: 'schedule-tab',
              tabTip: title,
              closable: true,
              layout: 'fit',
              //iconCls: type + 'Icon',
              width: 'auto'
            }), {
              // Diese werden Ueberschrieben, falls sie Existieren
              id: id,
              title: title
            });
          }
          this.tabpanel.add(tab);
        }
        // Wechselt zum neu erstellten Tab
        this.tabpanel.setActiveTab(tab);
        MySched.Base.regScheduleEvents(id);
        // Startet den Auswahlmanagervar
        var func = function () {
        	MySched.SelectionManager.stopSelection();
       		MySched.SelectionManager.startSelection();
        }
        func.defer(50);
      }
    },
    /**
     * Gibt die Toolbar zurueck
     */
    getMainToolbar: function () {
      var btnSave = {
        text: 'Speichern',
        id: 'btnSave',
        iconCls: 'tbSave',
        disabled: true,
        hidden: true,
        handler: MySched.Authorize.saveIfAuth,
        scope: MySched.Authorize,
        tooltip: {
          text: 'Speichern des angezeigten Planes'
        }
      }
      var btnEmpty = {
        text: 'Leeren',
        id: 'btnEmpty',
        iconCls: 'tbEmpty',
        hidden: true,
        disabled: false,
        tooltip: {
          text: 'L&ouml;schen der eingetragenen Veranstaltungen'
        },
        scope: MySched.selectedSchedule,
        handler: function () {
          Ext.Msg.confirm('Veranstaltungen l&ouml;schen', 'M&ouml;chten Sie die von Ihnen eingetragenen Veranstaltungen f&uuml;r ' + MySched.selectedSchedule.title + ' l&ouml;schen?', function (r) {
            if (r == 'yes') {
              var lessons = MySched.selectedSchedule.getLectures();
              var toremove = [];
              for (var i = 0; i < lessons.length; i++) {
                if ((lessons[i].data.type == "personal" && ((lessons[i].data.owner == MySched.Authorize.user && lessons[i].data.responsible == MySched.selectedSchedule.id) || MySched.Authorize.isClassSemesterAuthor())) || MySched.selectedSchedule.id == "mySchedule") {
                  toremove[toremove.length] = lessons[i].data.key;
                }
              }
              for (var i = 0; i < toremove.length; i++) {
                if (MySched.selectedSchedule.id == "mySchedule") {
                  MySched.selectedSchedule.removeLecture(MySched.selectedSchedule.getLecture(toremove[i]));
                }
                else {
                  var tab = MySched.layout.tabpanel.getItem(MySched.Base.schedule.getLecture(toremove[i]).data.responsible);
                  if (tab) tab.mSchedule.removeLecture(tab.mSchedule.getLecture(toremove[i]));
                  MySched.selectedSchedule.removeLecture(MySched.selectedSchedule.getLecture(toremove[i]));
                  MySched.Schedule.removeLecture(MySched.Schedule.getLecture(toremove[i]));
                  MySched.responsibleChanges.removeLecture(MySched.responsibleChanges.getLecture(toremove[i]));
                  MySched.Base.schedule.removeLecture(MySched.Base.schedule.getLecture(toremove[i]));
                }
              }
              MySched.selectedSchedule.eventsloaded = null;
              MySched.selectedSchedule.refreshView();
            }
          });
        }
      }
      var btnSavePdf = {
        // PDF DownloadButton
        text: 'PDF',
        id: 'btnPdf',
        iconCls: 'tbSavePdf',
        disabled: false,
        tooltip: {
          text: 'Download des angezeigten Planes als PDF'
        },
        handler: function () {
          var pdfwait = Ext.MessageBox.wait('Ihr Stundenplan wird generiert', 'PDF wird erstellt', {
            interval: 100,
            duration: 2000
          });

          Ext.Ajax.request({
            url: _C('ajaxHandler'),
            jsonData: MySched.selectedSchedule.exportData(),
            method: 'POST',
            params: {
              username: MySched.Authorize.user,
              title: MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' '),
              what: "pdf",
              scheduletask: "Schedule.export"
            },
            scope: pdfwait,
            failure: function () {
              Ext.MessageBox.hide();
              Ext.Msg.alert("PDF download", 'Es ist ein Fehler beim Erstellen der PDF aufgetreten.');
            },
            success: function (response) {
              Ext.MessageBox.hide();
              if (response.responseText != "Permission Denied!") {
                // IFrame zum downloaden wird erstellt
                Ext.DomHelper.append(Ext.getBody(), {
                  tag: 'iframe',
                  id: 'downloadIframe',
                  src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=pdf&save=false&scheduletask=Download.schedule",
                  style: 'display:none;z-index:10000;'
                });
                // Iframe wird nach 2 Sec geloescht.
                var func = function () {
                  Ext.get('downloadIframe').remove();
                }
                func.defer(2000);
              }
              else {
                Ext.Msg.alert("PDF download", 'Es ist ein Fehler beim Erstellen der PDF aufgetreten.');
              }
            }
          })
        }
      }
      var btnICal = {
        // ICal DownloadButton
        text: 'ICal',
        id: 'btnICal',
        iconCls: 'tbSaveICal',
        disabled: false,
        tooltip: {
          text: 'Download des angezeigten Planes als ICal'
        },
        handler: function () {
          var icalwait = Ext.MessageBox.wait('Ihr Terminplan wird generiert', 'ICal wird erstellt', {
            interval: 100,
            duration: 2000
          });
          Ext.Ajax.request({
            url: _C('ajaxHandler'),
            jsonData: MySched.selectedSchedule.exportData(),
            method: 'POST',
            params: {
              username: MySched.Authorize.user,
              title: MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' '),
              what: "ical",
              scheduletask: "Schedule.export"
            },
            scope: icalwait,
            failure: function (response, ret) {
              Ext.MessageBox.hide();
              Ext.Msg.alert("ICal download", 'Es ist ein Fehler beim Erstellen des ICal aufgetreten.');
            },
            success: function (response, ret) {
              Ext.MessageBox.hide();
              try {
                var responseData = new Array();
                responseData = Ext.decode(response.responseText);
                if (responseData['url'] != "false") {
                  Ext.MessageBox.show({
                    minWidth: 500,
                    title: "Synchronisieren",
                    msg: '<strong style="font-weight:bold">Link</strong>:<br/>' + responseData['url'] + '<br/>Wollen Sie den Terminkalendar ersetzen?',
                    buttons: Ext.Msg.YESNO,
                    fn: function (btn, text) {
                      if (btn == "yes") {
                        // IFrame zum downloaden wird erstellt
                        Ext.DomHelper.append(Ext.getBody(), {
                          tag: 'iframe',
                          id: 'downloadIframe',
                          src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=ics&save=true&scheduletask=Download.schedule",
                          style: 'display:none;z-index:10000;'
                        });
                        // Iframe wird nach 2 Sec geloescht.
                        var func = function () {
                          Ext.get('downloadIframe').remove();
                        }
                        func.defer(2000);
                      }
                      else {
                        // IFrame zum downloaden wird erstellt
                        Ext.DomHelper.append(Ext.getBody(), {
                          tag: 'iframe',
                          id: 'downloadIframe',
                          src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=ics&save=false&scheduletask=Download.schedule",
                          style: 'display:none;z-index:10000;'
                        });
                        // Iframe wird nach 2 Sec geloescht.
                        var func = function () {
                          Ext.get('downloadIframe').remove();
                        }
                        func.defer(2000);
                      }
                    }
                  });
                }
                else {
                  // IFrame zum downloaden wird erstellt
                  Ext.DomHelper.append(Ext.getBody(), {
                    tag: 'iframe',
                    id: 'downloadIframe',
                    src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=ics&save=false&scheduletask=Download.schedule",
                    style: 'display:none;z-index:10000;'
                  });
                  // Iframe wird nach 2 Sec geloescht.
                  var func = function () {
                    Ext.get('downloadIframe').remove();
                  }
                  func.defer(2000);
                }
              }
              catch(e) {
                Ext.Msg.alert("ICal download", 'Es ist ein Fehler beim Erstellen des ICal aufgetreten.');
              }
            }
          })
        }
      }
      var btnSaveTxt = {
        // TxT DownloadButton
        text: 'Excel',
        id: 'btnTxt',
        iconCls: 'tbSaveTxt',
        disabled: false,
        tooltip: {
          text: 'Download des angezeigten Planes als Excel'
        },
        handler: function () {
          var txtwait = Ext.MessageBox.wait('Ihr Stundenplan wird generiert', 'Txt wird erstellt', {
            interval: 100,
            duration: 2000
          });
          var blablub = MySched.selectedSchedule.exportData();
          Ext.Ajax.request({
            url: _C('ajaxHandler'),
            jsonData: MySched.selectedSchedule.exportData(),
            method: 'POST',
            params: {
              username: MySched.Authorize.user,
              title: MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' '),
              what: "ics",
              scheduletask: "Schedule.export"
            },
            scope: txtwait,
            failure: function () {
              Ext.MessageBox.hide();
              Ext.Msg.alert("Hinweis", 'Es ist ein Fehler beim Erstellen aufgetreten.');
            },
            success: function (response) {
              Ext.MessageBox.hide();
              if (response.responseText != "Permission Denied!") {
                // IFrame zum downloaden wird erstellt
                Ext.DomHelper.append(Ext.getBody(), {
                  tag: 'iframe',
                  id: 'downloadIframe',
                  src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=xls&save=false&scheduletask=Download.schedule",
                  style: 'display:none;z-index:10000;'
                });
                // Iframe wird nach 2 Sec geloescht.
                var func = function () {
                  Ext.get('downloadIframe').remove();
                }
                func.defer(2000);
              }
              else {
                Ext.Msg.alert("Hinweis", 'Es ist ein Fehler beim Erstellen aufgetreten.');
              }
            }
          })
        }
      }

      var btnAdd = {
        // HinzufuegenButton
        text: 'Hinzuf&uuml;gen',
        id: 'btnAdd',
        iconCls: 'tbAdd',
        disabled: true,
        hidden: true,
        handler: MySched.SelectionManager.lecture2ScheduleHandler,
        scope: MySched.SelectionManager,
        tooltip: {
          text: 'F&uuml;gt die aktuell ausgew&auml;hlte Veranstaltung Ihrem Stundenplan hinzu.'
        }
      }

      var btnMenu = {
        //MenuButton
        text: 'Download',
        id: 'btnMenu',
        iconCls: 'tbDownload',
        disabled: false,
        menu: [btnSavePdf, btnICal, btnSaveTxt]
      }

      var btnDel = {
        // EntfernenButton
        text: 'Entfernen',
        id: 'btnDel',
        iconCls: 'tbTrash',
        hidden: true,
        disabled: true,
        handler: MySched.SelectionManager.lecture2ScheduleHandler,
        scope: MySched.SelectionManager,
        tooltip: {
          text: 'Entfernt die aktuell ausgew&auml;hlte Veranstaltung aus Ihrem Stundenplan.'
        }
      }

      var btnInfo = {
        // InfoButton
        text: 'Info',
        id: 'btnInfo',
        iconCls: 'tbInfo',
        handler: MySched.layout.showInfoWindow,
        scope: MySched.layout
      }

      var tbFreeBusy = {
        // Frei/Belegt Button
        text: 'Frei/Belegt',
        id: 'btnFreeBusy',
        iconCls: 'tbFreeBusy',
        hidden: true,
        enableToggle: true,
        pressed: MySched.freeBusyState,
        toggleHandler: MySched.Base.freeBusyHandler,
        tooltip: {
          text: 'Zeigt in allen Stundenpl&auml;nen die im eigenen Stundenplan belegten Bl&ouml;cke farblich hinterlegt an.'
        }
      }

      Ext.DatePicker.prototype.startDay = 1;

      var inidate = new Date();

      var menuedatepicker = new Ext.form.DateField({
        id: 'menuedatepicker',
        showWeekNumber: true,
        format: 'd.m.Y',
        useQuickTips: false,
        editable: false,
        value: inidate,
        listeners: {
          'valid': function () {
            if (MySched.selectedSchedule != null) {
              MySched.selectedSchedule.eventsloaded = null;
              MySched.selectedSchedule.refreshView();
            }
          }
        }
      });


      return [
      menuedatepicker, btnSave, btnMenu, '->', btnInfo,
      btnEmpty,
      btnAdd, btnDel
      ];
    }
  };
}();

Ext.form.VTypes['ValidTimeText'] = 'Startzeit muss kleiner als die Endzeit sein.';
Ext.form.VTypes['ValidTime'] = function (arg, field) {
  if (field.id == "starttiid") {
    if (!Ext.getCmp('endtiid').getValue()) return true;
    if (Ext.getCmp('starttiid').getValue() < Ext.getCmp('endtiid').getValue()) {
      return true;
    }
    return false;
  }
  else {
    if (!Ext.getCmp('starttiid').getValue()) Ext.getCmp('starttiid').validate();
    if (Ext.getCmp('starttiid').getValue() < Ext.getCmp('endtiid').getValue()) {
      Ext.getCmp('starttiid').validate();
      return true;
    }
    return false;
  }

}

function newPEvent(pday, pstime, petime, title, doz_name, clas_name, room_name, l, key) {
  if (l) var lock = l;
  else var lock = MySched.selectedSchedule.type;
  var titel = {
    layout: 'form',
    width: 550,
    labelAlign: 'top',
    items: [{
      xtype: 'textfield',
      fieldLabel: 'Titel',
      width: 525,
      name: 'titel',
      id: 'titelid',
      value: title,
      emptyText: 'Trage hier einen Titel fuer die Veranstaltung ein',
      blankText: 'Bitte trage hier einen Titel ein',
      allowBlank: false
    }]
  };

  //Wird erstmal nicht mehr verwendet
  var notice = {
    layout: 'form',
    defaultType: 'htmleditor',
    width: 550,
    height: 160,
    hidden: true,
    //Verstecken
    items: [{
      fieldLabel: 'Beschreibung',
      labelSeparator: '',
      width: 420,
      height: 170,
      name: 'notice',
      id: 'noticeid'
    }]
  };

  var datedata = new Array();
  for (var ddi = 1; ddi < MySched.daytime.length; ddi++) {
    datedata[datedata.length] = [ddi, MySched.daytime[ddi].gerName];
  }

  var date = {
    columnWidth: .33,
    layout: 'form',
    labelAlign: 'top',
    items: [{
      fieldLabel: 'Tag',
      labelStyle: 'padding:0px;',
      name: 'cbday',
      id: 'cbdayid',
      readOnly: true,
      xtype: 'combo',
      mode: 'local',
      store: new Ext.data.ArrayStore({
        id: 0,
        fields: ['myId', 'displayText'],
        data: datedata
      }),
      valueField: 'myId',
      displayField: 'displayText',
      minChars: 0,
      triggerAction: 'all',
      blankText: 'Bitte w&auml;hle einen Wochentag aus',
      allowBlank: false,
      width: 170
    }]
  }

  var stime = {
    columnWidth: .33,
    layout: 'form',
    labelAlign: 'top',
    items: [{
      fieldLabel: 'Startzeit',
      labelStyle: 'padding:0px;',
      name: 'startti',
      id: 'starttiid',
      xtype: 'timefield',
      value: pstime,
      blankText: 'Format hh:mm',
      emptyText: 'hh:mm',
      minValue: '8:00',
      maxValue: '19:00',
      format: 'H:i',
      vtype: 'ValidTime',
      allowBlank: false,
      width: 170
    }]
  }

  var etime = {
    columnWidth: .33,
    layout: 'form',
    labelAlign: 'top',
    items: [{
      fieldLabel: 'Endzeit',
      labelStyle: 'padding:0px;',
      name: 'endti',
      id: 'endtiid',
      xtype: 'timefield',
      blankText: 'Format hh:mm',
      emptyText: 'hh:mm',
      value: petime,
      minValue: '8:00',
      maxValue: '19:00',
      format: 'H:i',
      vtype: 'ValidTime',
      allowBlank: false,
      width: 170
    }]
  }

  var roomstore = new Array();
  for (var i = 0; i < MySched.Mapping.room.length; i++) {
    roomstore.push(new Array(MySched.Mapping.room.items[i].id, MySched.Mapping.room.items[i].name.replace(/^\s+/, '').replace(/\s+$/, '')));
  }

  var dozstore = new Array();
  for (var i = 0; i < MySched.Mapping.doz.length; i++) {
    dozstore.push(new Array(MySched.Mapping.doz.items[i].id, MySched.Mapping.doz.items[i].name));
  }

  var classstore = new Array();

  for (var i = 0; i < MySched.Mapping.clas.length; i++) {
    classstore.push(new Array(MySched.Mapping.clas.items[i].id, MySched.Mapping.clas.items[i].department + " - " + MySched.Mapping.clas.items[i].name));
  }

  var pwin;

  var roomitem = {
    columnWidth: .33,
    layout: 'form',
    labelAlign: 'top',
    items: [{
      xtype: "multiselect",
      fieldLabel: "Ort",
      name: 'room',
      id: 'roomid',
      title: '',
      store: new Ext.data.ArrayStore({
        fields: ['myId', 'displayText'],
        data: roomstore
      }),
      width: 170,
      height: 80,
      cls: "ux-mselect",
      valueField: "myId",
      displayField: "displayText",
      ddReorder: true
    }]
  };

  var roomfield = {
    columnWidth: .33,
    layout: 'form',
    labelAlign: 'top',
    items: [{
      xtype: 'textfield',
      fieldLabel: '',
      name: 'roomfield',
      id: 'roomfieldid',
      emptyText: 'Raum eintragen',
      labelStyle: 'padding:0px;',
      width: 170
    }]
  };

  var dozitem = {
    columnWidth: .33,
    layout: 'form',
    labelAlign: 'top',
    items: [{
      xtype: "multiselect",
      fieldLabel: "Dozent",
      name: 'doz',
      id: 'dozid',
      title: '',
      store: new Ext.data.ArrayStore({
        fields: ['myId', 'displayText'],
        data: dozstore
      }),
      width: 170,
      height: 80,
      cls: "ux-mselect",
      valueField: "myId",
      displayField: "displayText",
      ddReorder: true
    }]
  }

  var dozfield = {
    columnWidth: .33,
    layout: 'form',
    labelAlign: 'top',
    items: [{
      xtype: 'textfield',
      fieldLabel: '',
      name: 'dozfield',
      id: 'dozfieldid',
      emptyText: 'Dozent eintragen',
      labelStyle: 'padding:0px;',
      width: 170
    }]
  };

  var clasitem = {
    columnWidth: .33,
    layout: 'form',
    labelAlign: 'top',
    items: [{
      xtype: "multiselect",
      fieldLabel: "Semester",
      name: 'clas',
      id: 'clasid',
      title: '',
      store: new Ext.data.ArrayStore({
        fields: ['myId', 'displayText'],
        data: classstore
      }),
      width: 170,
      height: 80,
      cls: "ux-mselect",
      valueField: "myId",
      displayField: "displayText",
      ddReorder: true
    }]
  }

  var clasfield = {
    columnWidth: .33,
    layout: 'form',
    labelAlign: 'top',
    items: [{
      xtype: 'textfield',
      fieldLabel: '',
      name: 'clasfield',
      id: 'clasfieldid',
      emptyText: 'Semester eintragen',
      labelStyle: 'padding:0px;',
      width: 170
    }]
  };

  var addterminpanel = new Ext.FormPanel({
    frame: true,
    bodyStyle: 'padding:5px',
    width: 550,
    height: 305,
    layout: 'form',
    id: 'addterminpanel',
    defaults: {
      msgTarget: 'side'
    },
    items: [
    titel, notice, //Wird erstmal nicht mehr verwendet
    {
      xtype: 'fieldset',
      hideLabel: true,
      width: 540,
      autoHeight: true,
      hideBorders: true,
      layout: 'column',
      items: [roomitem, dozitem, clasitem]
    },
    {
      xtype: 'fieldset',
      hideLabel: true,
      width: 540,
      autoHeight: true,
      hideBorders: true,
      layout: 'column',
      items: [roomfield, dozfield, clasfield]
    },
    {
      xtype: 'fieldset',
      hideLabel: true,
      width: 540,
      autoHeight: true,
      hideBorders: true,
      layout: 'column',
      items: [date, stime, etime]
    },
    {
      xtype: 'hidden',
      id: "hiddenowner",
      hidden: true
    },
    {
      xtype: 'hidden',
      id: "hiddenkey",
      hidden: true
    }],
    buttonAlign: 'center',
    buttons: [{
      text: 'Hinzufügen',
      scope: this,
      handler: function () {
        var titel = Ext.getCmp('titelid').isValid(false);
        var day = Ext.getCmp('cbdayid').isValid(false);
        var stime = Ext.getCmp('starttiid').isValid(false);
        var etime = Ext.getCmp('endtiid').isValid(false);

        if (titel && day && stime && etime) {
          var blocks = timetoblocks(Ext.getCmp('starttiid').getValue(), Ext.getCmp('endtiid').getValue());
          var date = new Date().format('d.m.Y H:i:s');
          var dozs = Ext.getCmp('dozid').getValue();
          var rooms = Ext.getCmp('roomid').getValue();
          var classes = Ext.getCmp('clasid').getValue();

          if (Ext.getCmp('dozfieldid').getValue().replace(/^\s+/, '').replace(/\s+$/, '') != "") dozs = dozs + "," + Ext.getCmp('dozfieldid').getValue();
          if (Ext.getCmp('roomfieldid').getValue().replace(/^\s+/, '').replace(/\s+$/, '') != "") rooms = rooms + "," + Ext.getCmp('roomfieldid').getValue();
          if (Ext.getCmp('clasfieldid').getValue().replace(/^\s+/, '').replace(/\s+$/, '') != "") classes = classes + "," + Ext.getCmp('clasfieldid').getValue();

          dozs = dozs.split(",");
          rooms = rooms.split(",");
          classes = classes.split(",");

          var doz = "";
          var room = "";
          var clas = "";

          for (var a = 0; a < rooms.length; a++) {
            var found = false;
            if (rooms[a] != "") {
              for (var i = 0; i < MySched.Mapping.room.length; i++) {
                if (MySched.Mapping.room.items[i].name.replace(/^\s+/, '').replace(/\s+$/, '') == rooms[a].replace(/^\s+/, '').replace(/\s+$/, '')) {
                  if (!room.contains(MySched.Mapping.room.items[i].id)) {
                    if (room == "") room = MySched.Mapping.room.items[i].id;
                    else room = room + " " + MySched.Mapping.room.items[i].id;
                  }
                  found = true;
                  break;
                }
              }
              if (!found) {
                if (room == "") room = rooms[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
                else room = room + " " + rooms[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
              }
            }
          }

          for (var a = 0; a < dozs.length; a++) {
            var found = false;
            if (dozs[a] != "") {
              for (var i = 0; i < MySched.Mapping.doz.length; i++) {
                if (MySched.Mapping.doz.items[i].name == dozs[a].replace(/^\s+/, '').replace(/\s+$/, '')) {
                  if (!doz.contains(MySched.Mapping.doz.items[i].id)) {
                    if (doz == "") doz = MySched.Mapping.doz.items[i].id;
                    else doz = doz + " " + MySched.Mapping.doz.items[i].id;
                  }
                  found = true;
                  break;
                }
              }
              if (!found) {
                if (doz == "") doz = dozs[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
                else doz = doz + " " + dozs[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
              }
            }
          }

          for (var a = 0; a < classes.length; a++) {
            var found = false;
            if (classes[a] != "") {
              for (var i = 0; i < MySched.Mapping.clas.length; i++) {
                if ((MySched.Mapping.clas.items[i].department + " - " + MySched.Mapping.clas.items[i].name) == classes[a].replace(/^\s+/, '').replace(/\s+$/, '')) {
                  if (!clas.contains(MySched.Mapping.clas.items[i].id)) {
                    if (clas == "") clas = MySched.Mapping.clas.items[i].id;
                    else clas = clas + " " + MySched.Mapping.clas.items[i].id;
                  }
                  found = true;
                  break;
                }
              }
              if (!found) {
                if (clas == "") clas = classes[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
                else clas = clas + " " + classes[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
              }
            }
          }

          for (var i = 0; i < blocks['size']; i++) {
            var tkey = "";
            if (Ext.getCmp('hiddenkey').getValue() == "") {
              tkey = ("PE_" + Ext.getCmp('hiddenowner').getValue() + "_" + Ext.getCmp('cbdayid').getValue() + "_" + blocks[i] + "_" + date).toLowerCase();
            }
            else {
              tkey = Ext.getCmp('hiddenkey').getValue();
            }

            if (blocks['size'] == 1) {
              var values = {
                block: blocks[i],
                clas: clas,
                dow: Ext.getCmp('cbdayid').getValue(),
                doz: doz,
                id: Ext.getCmp('titelid').getValue(),
                key: tkey,
                room: room,
                subject: Ext.getCmp('titelid').getValue(),
                type: "personal",
                desc: Ext.getCmp('noticeid').getValue(),
                owner: Ext.getCmp('hiddenowner').getValue(),
                stime: Ext.getCmp('starttiid').getValue(),
                etime: Ext.getCmp('endtiid').getValue(),
                showtime: "full",
                lock: lock,
                responsible: MySched.selectedSchedule.id
              };
            }
            else if (i == 0) {
              var blotimes = blocktotime(blocks[i]);
              if (Ext.getCmp('endtiid').getValue() != blotimes[1]) blotimes = blotimes[1];
              else blotimes = Ext.getCmp('endtiid').getValue();
              var values = {
                block: blocks[i],
                clas: clas,
                dow: Ext.getCmp('cbdayid').getValue(),
                doz: doz,
                id: Ext.getCmp('titelid').getValue(),
                key: tkey,
                room: room,
                subject: Ext.getCmp('titelid').getValue(),
                type: "personal",
                desc: Ext.getCmp('noticeid').getValue(),
                owner: Ext.getCmp('hiddenowner').getValue(),
                stime: Ext.getCmp('starttiid').getValue(),
                etime: blotimes,
                showtime: "first",
                lock: lock,
                responsible: MySched.selectedSchedule.id
              };
            }
            else if ((i + 1) == blocks['size']) {
              var blotimes = blocktotime(blocks[i]);
              if (Ext.getCmp('starttiid').getValue() != blotimes[0]) blotimes = blotimes[0];
              else blotimes = Ext.getCmp('starttiid').getValue();
              var values = {
                block: blocks[i],
                clas: clas,
                dow: Ext.getCmp('cbdayid').getValue(),
                doz: doz,
                id: Ext.getCmp('titelid').getValue(),
                key: tkey,
                room: room,
                subject: Ext.getCmp('titelid').getValue(),
                type: "personal",
                desc: Ext.getCmp('noticeid').getValue(),
                owner: Ext.getCmp('hiddenowner').getValue(),
                stime: blotimes,
                etime: Ext.getCmp('endtiid').getValue(),
                showtime: "last",
                lock: lock,
                responsible: MySched.selectedSchedule.id
              };
            }
            else {
              var blotimes = blocktotime(blocks[i]);
              var values = {
                block: blocks[i],
                clas: clas,
                dow: Ext.getCmp('cbdayid').getValue(),
                doz: doz,
                id: Ext.getCmp('titelid').getValue(),
                key: tkey,
                room: room,
                subject: Ext.getCmp('titelid').getValue(),
                type: "personal",
                desc: Ext.getCmp('noticeid').getValue(),
                owner: Ext.getCmp('hiddenowner').getValue(),
                stime: blotimes[0],
                etime: blotimes[1],
                showtime: "none",
                lock: lock,
                responsible: MySched.selectedSchedule.id
              };
            }

            var record = new mLecture(values.key, values);

            if (MySched.selectedSchedule.id != "mySchedule") {
              //Änderungen den Stammdaten hinzufügen
              MySched.Base.schedule.addLecture(record);
              //Änderungen den Gesamt�nderungen der Responsibles hinzufügen
              MySched.responsibleChanges.addLecture(record);
            }

            var lessons = MySched.Schedule.getLectures();
            for (var a = 0; a < lessons.length; a++)
            if (lessons[a].data.key == values.key) {
              MySched.Schedule.addLecture(record);
              break;
            }

            //Änderungen dem aktuellen Stundenplan hinzufügen
            MySched.selectedSchedule.addLecture(record);
          }
          MySched.selectedSchedule.eventsloaded = null;
          MySched.selectedSchedule.refreshView();
          if (pwin != null) pwin.close();

        }
      }
    },
    {
      text: 'Abbrechen',
      handler: function (b, e) {
        if (pwin != null) pwin.close();
      }
    }]
  });

  pwin = new Ext.Window({
    layout: 'form',
    id: 'terminWin',
    width: 560,
    iconCls: 'lesson_add',
    title: 'Veranstaltung hinzufügen',
    height: 337,
    modal: true,
    frame:false,
    closeAction: 'close',
    items: [
    addterminpanel]
  });

  if (l) {
    pwin.setIconClass("lesson_edit");
    pwin.setTitle("Veranstaltung ändern");
    addterminpanel.buttons[0].text = "Ändern";
  }

  pwin.show();
  Ext.getCmp('cbdayid').setValue(daytonumber(pday));
  Ext.getCmp('hiddenowner').setValue(MySched.Authorize.user);

  if (key) {
    Ext.getCmp('hiddenkey').setValue(key);
  }

  Ext.getCmp('clasid').setValue(clas_name);
  Ext.getCmp('roomid').setValue(room_name);
  Ext.getCmp('dozid').setValue(doz_name);

  if (clas_name) {
    setFieldValue("clas", clas_name);
  }

  if (room_name) {
    setFieldValue("room", room_name);
  }

  if (doz_name) {
    setFieldValue("doz", doz_name);
  }

  if (lock == "doz") {
    if (!doz_name) setFieldValue("doz", MySched.selectedSchedule.id);
    Ext.getCmp('dozid').disable();
    Ext.getCmp('dozfieldid').disable();

  }
  else if (lock == "room") {
    if (!room_name) setFieldValue("room", MySched.selectedSchedule.id);
    Ext.getCmp('roomid').disable();
    Ext.getCmp('roomfieldid').disable();
  }
  else if (lock == "clas") {
    if (!clas_name) setFieldValue("clas", MySched.selectedSchedule.id);
    Ext.getCmp('clasid').disable();
    Ext.getCmp('clasfieldid').disable();
  }
}

function setFieldValue(type, str) {
  var tempidarr = Ext.getCmp(type + 'id').getValue().split(",");
  var temparr = str.split(",");
  var tempstr = "";
  for (var tai = 0; tai < temparr.length; tai++) {
    var objtemp = MySched.Mapping.getObject(type, temparr[tai]);
    var strtemp = "";
    if (Ext.isObject(objtemp)) {
      if (type == "clas") strtemp = objtemp.department + " - " + objtemp.name;
      else strtemp = objtemp.name;
    }
    else strtemp = temparr[tai];
    if (tempstr == "") {
      tempstr = strtemp;
    }
    else tempstr = tempstr + "," + strtemp;
  }
  Ext.getCmp(type + 'fieldid').setValue(tempstr);
}

function daytonumber(day) {
  switch (day) {
  case "sunday":
    return 0;
  case "monday":
    return 1;
  case "tuesday":
    return 2;
  case "wednesday":
    return 3;
  case "thursday":
    return 4;
  case "friday":
    return 5;
  case "saturday":
    return 6;
  default:
    return false;
  }
}

/**
 * Wandelt englische Tagesname in deutsche um.
 * @param {String} week_day englischer Tagesname
 **/

function weekdayEtoD(week_day) {
  switch (week_day) {
  case "monday":
    return "Montag";
    break;
  case "tuesday":
    return "Dienstag";
    break;
  case "wednesday":
    return "Mittwoch";
    break;
  case "thursday":
    return "Donnerstag";
    break;
  case "friday":
    return "Freitag";
    break;
  case "saturday":
    return "Samstag";
    break;
  case "sunday":
    return "Sonntag";
    break;
  default:
    return false;
  }
}

function timetoblocks(stime, etime) {
  var blocks = [];
  counter = 0;
  for (var i = 1; i <= 6; i++) {
    var times = blocktotime(i);
    if ((stime <= times[0] && etime >= times[1]) || (stime >= times[0] && etime <= times[1]) || (times[0] <= stime && times[1] > stime) || (times[0] < etime && times[1] >= etime)) {
      blocks[counter] = i;
      counter++;
    }
  }
  blocks['size'] = counter;
  return blocks;
}

function blocktotime(block) {
  if (Ext.isNumber(block) && typeof block != "undefined" && MySched.daytime[1] != null) return {
    0: MySched.daytime[1][block]["stime"],
    1: MySched.daytime[1][block]["etime"]
  };
  return {
    0: null,
    1: null
  };
}

Ext.ux.collapsedPanelTitlePlugin = function () {
  this.init = function (p) {
    if (p.collapsible) {
      var r = p.region;
      if ((r == 'north') || (r == 'south')) {
        p.on('render', function () {
          var ct = p.ownerCt;
          ct.on('afterlayout', function () {
            if (ct.layout[r].collapsedEl) {
              p.collapsedTitleEl = ct.layout[r].collapsedEl.createChild({
                tag: 'span',
                cls: 'x-panel-header-text',
                html: p.title,
                style: "margin-left:5px; color:#15428B; font-family:tahoma; font-size:11px; font-weight:bold; line-height:18px;"
              });
              p.setTitle = Ext.Panel.prototype.setTitle.createSequence(function (t) {
                p.collapsedTitleEl.dom.innerHTML = t;
              });
            }
          }, false, {
            single: true
          });
          p.on('collapse', function () {
            if (ct.layout[r].collapsedEl && !p.collapsedTitleEl) {
              p.collapsedTitleEl = ct.layout[r].collapsedEl.createChild({
                tag: 'span',
                cls: 'x-panel-header-text',
                html: p.title,
                style: "margin-left:5px; color:#15428B; font-family:tahoma; font-size:11px; font-weight:bold; line-height:18px;"
              });
              p.setTitle = Ext.Panel.prototype.setTitle.createSequence(function (t) {
                p.collapsedTitleEl.dom.innerHTML = t;
              });
            }
          }, false, {
            single: true
          });
        });
      }
    }
  };
}

/**
 * Baumobjekt fuer Stundenplanlisten
 */
MySched.Tree = function () {
  var tree, doz, room, clas, diff, dragNode, respChanges, curtea;

  return {
    init: function () {
      this.root = new Ext.tree.TreeNode({
      	id: 'rootTreeNode',
        text: 'root',
        expanded: true
      });
      this.tree = new Ext.tree.TreePanel({
        region: 'center',
        title: " ",
        id: 'selectTree',
        header: true,
        bodyStyle: 'background-color:#E6E6E6; bottom no-repeat; border-left:6px; padding-left:6px',
        autoScroll: true,
        rootVisible: false,
        root: this.root,
        collapseFirst: false,
        enableDrag: true,
        ddGroup: 'lecture',
        headerCfg: {
          tag: '',
          cls: 'x-panel-header mySched_treeheader',
          // Default class not applied if Custom element specified
          html: ''
        }

      });

      // Sortierung der Liste
      new Ext.tree.TreeSorter(this.tree, {
        folderSort: true,
        dir: "asc"
      });

      // Bei Klick Stundenplan oeffnen
      this.tree.on({
        'click': function (n) {
          var img = n.ui.ecNode;
          if (!n.isLeaf()) {
            if (!n.expanded) img.alt = "collapsed";
            else if (n.expanded) img.alt = "expanded";
            else if (img.alt == "") img.alt = "expanded";
          }
          if (n.isLeaf()) {
            var title = "";
            var key = n.attributes.id;
            var res = n.attributes.gpuntisID;
            var semesterID = n.attributes.semesterID;
            var plantype = n.attributes.plantype;
            var type = n.attributes.type;
            if(type === null)
            	type = res;
            var department = null;
            if (res == "delta")
              title = "Änderungen (zentral)";
            else if (res == "respChanges")
              title = "Änderungen (eigene)";
            else
            {
              department = MySched.Mapping.getObjectField(type, res, "department");
              if (typeof department == "undefined" || department == "none" || department == null || department == res)
                title = MySched.Mapping.getName(type, res);
              else
                title = MySched.Mapping.getName(type, res) + " - " + MySched.Mapping.getObjectField(type, res, "department");
            }

            if (MySched.loadedLessons[key] != true) {
              Ext.Ajax.request({
                url: _C('ajaxHandler'),
                method: 'POST',
                params: {
                  res: res,
                  class_semester_id: semesterID,
                  scheduletask: "Ressource.load",
                  plantype: plantype,
                  type: type
                },
                failure: function (response) {},
                success: function (response) {
                  try {
                    var json = Ext.decode(response.responseText);
                    for (var item in json) {
                      if (Ext.isObject(json[item])) {
                        var record = new mLecture(json[item].key, json[item]);
                        MySched.Base.schedule.addLecture(record);
                        MySched.TreeManager.add(record);
                      }
                    }
                    if (typeof json["elements"] != "undefined") {
                      n.elements = json["elements"];
                      new mSchedule(key, title).init(type, json["elements"]).show();
                    }
                    else new mSchedule(key, title).init(type, res).show();
                    MySched.loadedLessons[key] = true;
                  }
                  catch(e)
                  {}
                }
              });
            }
            else {
              if (typeof n.elements != "undefined") new mSchedule(key, title).init(type, n.elements).show();
              else new mSchedule(key, title).init(type, res).show();
            }

          }
        }
      });
      return this.tree;
    },
    /**
     * Setzt den Titel des Listenfelds
     * @param {Object} title
     * @param {Object} append
     */
    setTitle: function (title, append) {
      if (append == true) this.tree.setTitle(this.tree.title + title);
      else this.tree.setTitle(title);
    },
    /**
     * Refresht die Daten der Liste
     */
    refreshTreeData: function () {
      if (this.doz) this.root.removeChild(this.doz);
      if (this.room) this.root.removeChild(this.room);
      if (this.clas) this.root.removeChild(this.clas);
      if (this.diff) this.root.removeChild(this.diff);
      if (this.respChanges) this.root.removeChild(this.respChanges);
      if (this.curtea) this.root.removeChild(this.curtea);
      this.loadTreeData();
    },
    /**
     * Fuellt den Baum mit den Daten von Dozenten, Raeumen und Studiengaengen/Semestern,
     * je nach berechtigung
     */
    loadTreeData: function () {
      MySched.TreeManager.processTreeData(MySched.startup["TreeView.load"].data, null, null, null, this.tree);
    },
    /**
     * Setzt die Daten im Baum
     **/
    setTreeData: function (data) {
      var type = data.id
      this[type] = data;
      var imgs = Ext.DomQuery.select('img[class=x-tree-ec-icon x-tree-elbow-end-plus]', MySched.Tree.tree.body.dom);
      for (var i = 0; i < imgs.length; i++) {
        imgs[i].alt = "collapsed";
      }
      var imgs = Ext.DomQuery.select('img[class=x-tree-ec-icon x-tree-elbow-plus]', MySched.Tree.tree.body.dom);
      for (var i = 0; i < imgs.length; i++) {
        imgs[i].alt = "collapsed";
      }
    }
  };
}();

function checkStartup(checkfor, type)
{
  if(!Ext.isString(type))
    type = null;
  if(Ext.isObject(MySched.startup) === true)
    if(Ext.isObject(MySched.startup[checkfor]))
    {
      if(type === null)
      {
        if(MySched.startup[checkfor].success === true)
        {
          return true;
        }
      }
      else
      {
        if(MySched.startup[checkfor][type].success === true)
        {
          return true;
        }
      }
    }
  return false;
}

/**
 * Subscribe Handler fuer Einschreiben in Kurse
 */
MySched.Subscribe = function () {
  var data, grid, store, window;
  var grid1;
  return {
    /**
     * Speichert die uebergebenen Daten
     * @param {Object} data
     */
    setData: function (data) {
      this.data = new Array();
      Ext.each(data, function (v) {
        if (v.subscribe_possible) this.push(v);
      }, this.data);
    },
    /**
     * Zeigt das Fenster zur Auswahl der Veranstaltungen
     * in die Eingeschriebene werden soll an
     * @param {Object} data Aktueller "Mein Stundenplan"
     */
    show: function (data) {
      this.setData(data);

      // Erstellt Fenstern
      this.window = new Ext.Window({
        title: 'Einschreiben',
        id: 'subscribeWindow',
        width: 410,
        height: 250,
        modal: true,
        plain: true,
        resizable: false,
        layout: 'fit',
        items: this.buildGrid()
      });
      this.window.show();
    },
    /**
     * Erstellt die Tabelle zur Auswahl
     */
    buildGrid: function () {
      var sm = new Ext.grid.CheckboxSelectionModel();
      // Daten zum Einschreiben holen
      this.store = new Ext.data.JsonStore({
        fields: [{
          name: 'name'
        }, 'subscribe', 'subscribe_info', 'subscribe_type']
      });
      this.store.loadData(this.data);

      // Erstellt die Tabelle
      this.grid = new Ext.grid.GridPanel({
        store: this.store,
        columns: [
        sm,
        {
          dataIndex: 'name',
          header: "Veranstaltung",
          width: 120,
          align: 'left'
        },
        {
          dataIndex: 'subscribe_type',
          header: "Typ",
          width: 40,
          align: 'left'
        }],
        stripeRows: true,
        selModel: sm,
        height: 250,
        width: 400,
        viewConfig: {
          forceFit: true,
          enableRowBody: true,
          showPreview: true,
          getRowClass: function (record, rowIndex, p, store) {
            if (this.showPreview) {
              p.body = '<p style="padding-left:25px; text-decoration:italic;">' + record.data.subscribe_info + '</p>';
              return 'x-grid3-row-expanded';
            }
            return 'x-grid3-row-collapsed';
          }
        },
        bbar: [{
          text: 'Speichern',
          id: 'btnSave',
          iconCls: 'tbSave',
          handler: this.save,
          scope: this
        }],
        title: 'Bei Veranstaltungen Einschreiben'
      });

      return this.grid;
    },
    save: function () {
      // Sendet die ausgewaehlten Veranstaltungen an den Server
      // Muss noch implementiert werden
      // Selctions des Grids ermitteln, Elemente des Stores an subscibe.php senden
    }
  };
}();