/**
 * Models von MySched
 * @author thorsten
 */

/**
 * Model als Grundform
 * @param {Object} id ID des Models
 * @param {Object} d DatenObjekt des Models
 */
Ext.define('MySched.Model', {
  extend: 'Ext.util.Observable',

  constructor: function (id, d) {
    var id, data, parent, responsible, object1, object2;

    this.id = id;
    this.data = {};
    this.responsible = null;
    this.object1 = null;
    this.object2 = null;
    // WICHTIG!! Tiefe Kopie erzeugen, da sonst nur Referenzen kopiert werden.
    if (Ext.type(d) == 'object' || Ext.type(d) == 'array') {
      Ext.apply(this.data, d);
    }
    else {
      this.data = d;
    }
  },
  getId: function () {
    return this.id;
  },
  getData: function (addData) {
    if (Ext.type(addData) != 'object') return this.data;
    return Ext.applyIf(addData, this.data)
  },
  setParent: function (p) {
    this.parent = p;
  },
  getParent: function () {
    return this.parent;
  },
  asArray: function () {
    var ret = [];
    var d = this.data;
    if (d.asArray) d = d.asArray();
    Ext.each(d, function (e) {
      if (Ext.isEmpty(e)) return;
      if (e.asArray) e = e.asArray();
      this[this.length] = e;
    }, ret);
    if (ret.length == 1) {
      return ret[0];
    }
    return ret;
  },
  exportData: function (type, pers) {

    if (pers == "personal") var d = this.asPersArray();
    else {
      var d = this.asArray();
      var len = d.length;
      d[len] = new Object();
      d[len]["sdate"] = MySched.session["begin"];
      d[len]["edate"] = MySched.session["end"];
    }

    switch (type) {
    case 'arr':
    case 'array':
      return d;
      break;
    case 'xml':
      alert("XML ist noch nicht implementiert!");
      exit();
      break;
    default:
    case 'json':
      return Ext.encode(d);
      break;
    }
  },
  exportAllData: function () {

    var d = [];
    d[0] = new Object();
    d[0]["htmlView"] = this.htmlView;
    d[0]["lessons"] = this.visibleLessons;
    d[0]["events"] = this.visibleEvents;
    d[0]["session"] = new Object();
    d[0]["session"]["sdate"] = MySched.session["begin"];
    d[0]["session"]["edate"] = MySched.session["end"];

    return Ext.encode(d);
  }
});

/**
 * Model zur Darstellung eines Stundenplans
 * @author thorsten
 */
Ext.define('mSchedule', {
  extend: 'MySched.Model',

  constructor: function (id, title, config) {
    var grid, blockCache, changed, proxy, reader, status;
    var type = "";
    this.blockCache = null;
    this.title = title || id;
    this.status = "saved";
    this.id = id;
    this.title = title;
    this.visibleLessons = new Array();
    this.visibleEvents = new Array();
    mSchedule.superclass.constructor.call(this, id, new MySched.Collection());
    if (config && config.type && config.value)
      this.init(config.type, config.value);
    this.addEvents({
      beforeLectureAdd: true,
      lectureAdd: true,
      beforeLectureRemove: true,
      lectureRemove: true,
      beforeClear: true,
      clear: true,
      beforeSave: true,
      save: true,
      changed: true
    });
  },
  init: function (type, value) {

    if (type == "delta")
      this.data = MySched.delta.data;
    else if (type == "respChanges")
      this.data = MySched.responsibleChanges.data;
    else {
      var valuearr = value.split(";");
      for (var i = 0; i < valuearr.length; i++) {
        if (this.data.length == 0) {
          var datatemp = MySched.Base.getLectures(type, valuearr[i].toLowerCase());
          if (datatemp.length > 0) this.data = datatemp;
        }
        else {
          var datatemp = MySched.Base.getLectures(type, valuearr[i].toLowerCase());
          if (datatemp.length > 0) this.data.addAll(datatemp.items)
        }
        this.data.addAll(MySched.eventlist.getEvents());
      }
    }

    this.changed = false;
    this.type = type;
    this.gpUntisID = value;

    if (type == "clas" && MySched.Mapping.clas.map[value]) {
      this.responsible = MySched.Mapping.clas.map[value].manager;
      this.object1 = MySched.Mapping.clas.map[value].department;
      this.object2 = "";
    }
    else if (type == "room" && MySched.Mapping.room.map[value]) {
      this.responsible = MySched.Mapping.room.map[value].manager;
      this.object1 = MySched.Mapping.room.map[value].department;
      this.object2 = MySched.Mapping.room.map[value].rtype;
    }
    else if (type == "doz" && MySched.Mapping.doz.map[value]) {
      this.responsible = MySched.Mapping.doz.map[value].manager;
      this.object1 = MySched.Mapping.doz.map[value].department;
      this.object2 = "";
    }

    return this;
  },
  addLecture: function (l) {
    if (this.fireEvent("beforeLectureAdd", l) === false) return
    // Fuegt die lecture hinzu
    this.data.add(l.data.key, l);

    // blockCache wird dadurch unkonsistent
    this.blockCache = null;
    this.markChanged();

    this.fireEvent("lectureAdd", l);
  },
  clear: function () {
    if (this.fireEvent("beforeClear", this) === false) return this.data.clear();
    this.blockCache = null;
    this.markChanged();
    this.fireEvent("clear", this);
  },
  removeLecture: function (l) {
    if (this.fireEvent("beforeLectureRemove", l) === false)
      return;
    if (this.blockCache && Ext.type(l) == 'object')
      this.blockCache[l.getWeekDay()][l.getBlock() - 1]--;
    if (Ext.type(l) == 'object') {
      this.data.removeAtKey(l.getId());
    } else {
      this.data.removeAtKey(l);
    }
    this.markChanged();
    this.fireEvent("lectureRemove", l);
  },
  /**
   * Gibt die lecture mit der id zurueck
   * @param {Object} id
   */
  getLecture: function (id) {
    if (id.match('##')) id = id.split('##')[1];
    if (MySched.selectedSchedule.type == "delta") return MySched.delta.data.get(id);
    var Plesson = MySched.Schedule.data.get(id);
    if (Plesson != null) if (Plesson.data != null) if (Plesson.data.type == "personal") return MySched.Schedule.data.get(id);
    return this.data.get(id);
  },
  isEmpty: function () {
    return this.data.isEmpty();
  },
  /**
   * Gibt nur bestimmte Lectures zurueck
   * @param {Object} type
   * @param {Object} value
   * @return {MySched.Collection}
   */
  getLectures: function (type, value) {
    if (Ext.isEmpty(type) && Ext.isEmpty(value)) return this.data.items;
    return this.data.filterBy(function (o, k) {
      if (o.has(type, value)) return true;
      return false;
    }, this);
  },
  getGridData: function () {
    // 0-5 => Blocke am Tag
    var ret = [{},
    {},
    {},
    {},
    {},
    {}]; // Muss fuer Grid festes Format haben
    // Sporatisch, nicht regelmaessige Veranstaltungen
    var sp = [];
    var wpMO = null;
    var cd = Ext.ComponentMgr.get('menuedatepicker');
    var wp = null;
    this.visibleLessons = new Array();
    this.visibleEvents = new Array();

    wp = Ext.Date.clone(cd.value);

    wpMO = getMonday(wp);

    var begin = MySched.session["begin"].split(".");
    begin = new Date(begin[2], begin[1]-1, begin[0]);

    if (wp < begin && cd.menu == null) {
      Ext.MessageBox.show({
        title: "Semester hat noch nicht angefangen",
        cls: "mysched_semesterbegin",
        buttons: Ext.MessageBox.YESNO,
        msg: "Wollen Sie zum Semesteranfang springen?",
        width: 400,
        modal: true,
        closable: false,
        fn: function (btn) {
          if (btn == "yes") {
            var cd = Ext.ComponentMgr.get('menuedatepicker');
                  var begindate = MySched.session["begin"].split(".");
                  var inidate = new Date(begindate[2], begindate[1]-1, begindate[0]);

            if (typeof cd.menu == "undefined")
              cd.initialConfig.value = inidate;
            else {
              cd.menu.picker.value = inidate;
              cd.menu.picker.activeDate = inidate;
            }

            cd.setValue(MySched.session["begin"]);
            if (typeof cd.menu != "undefined")
              cd.menu.picker.update();
          }
                Ext.MessageBox.hide();
        }
      });
    }

    //sporadische Termine hinzufügen
    this.data.eachKey(function (k, v) {
      if (v.data.type != "cyclic" && v.data.type != "personal") {
        //sporadischer Termin
        var wd = null;
        var bl = null;
        var clickeddate = Ext.ComponentMgr.get('menuedatepicker');
        var weekpointer = null;
        weekpointer = Ext.Date.clone(clickeddate.value);

        weekpointer = getMonday(weekpointer);

        for (var counter = 0; counter < 5; counter++) {
          var startdate = v.data.startdate.split(".");
          startdate = new Date(startdate[2], startdate[1]-1, startdate[0]);
          var enddate = v.data.enddate.split(".");
          enddate = new Date(enddate[2], enddate[1]-1, enddate[0]);

          if (startdate <= weekpointer && enddate >= weekpointer) {
            wd = numbertoday(weekpointer.getDay());
            for (var i = 0; i < 6; i++) {
              var blotimes = blocktotime(i+1);
              if(v.data.recurrence_type == 1) //täglich
              {
                if (v.data.starttime <= blotimes[0] && v.data.endtime >= blotimes[1]) {
                  bl = i;
                }
                else if ((v.data.starttime >= blotimes[0] && v.data.starttime < blotimes[1]) && (blotimes[1] >= blotimes[0] && blotimes[1] <= blotimes[0])) {
                  bl = i;
                }
                else if (v.data.starttime >= blotimes[0] && v.data.starttime < blotimes[1]) {
                  bl = i;
                }
                else if (v.data.endtime >= blotimes[0] && v.data.endtime <= blotimes[1]) {
                  bl = i;
                }
              }
              else
                if(v.data.recurrence_type == 0) //durchgängig
                {
                  if( startdate == weekpointer )
                  {
                    if ( v.data.starttime <= blotimes[0] )
                      bl = i;
                    else
                    if (v.data.starttime >= blotimes[0] && v.data.starttime < blotimes[1])
                      bl = i;
                  }
                  else if( enddate == weekpointer)
                  {
                    if ( v.data.endtime >= blotimes[1] )
                      bl = i;
                    else
                    if (v.data.endtime >= blotimes[0] && v.data.endtime <= blotimes[1])
                      bl = i;
                  }
                  else
                    if (v.data.starttime >= blotimes[0] && v.data.starttime < blotimes[1])
                      bl = i;
                    else if (v.data.starttime <= blotimes[0] && v.data.endtime > blotimes[1])
                      bl = i;
                      else if (v.data.endtime > blotimes[0] && v.data.endtime <= blotimes[1])
                        bl = i;
                }

              if (bl != null) {
                if (!ret[bl][wd])
                  ret[bl][wd] = [];

                var begin = MySched.session["begin"].split(".");
                begin = new Date(begin[2], begin[1]-1, begin[0]);
                var end = MySched.session["end"].split(".");
                end = new Date(end[2], end[1]-1, end[0]);

                var lessonResult = [];

                var lessonResult = this.data.filterBy(function (o, k) {
                  if (o.data.type == "cyclic" || o.data.type == "personal")
                    for(var eventObjects in v.data.objects)
                    {
                      if(this.gpUntisID === eventObjects)
                        return true;
                      if (o.doz.containsKey(eventObjects) || o.room.containsKey(eventObjects))
                        if(o.data.block == (bl+1) && numbertoday(o.data.dow) == wd)
                          if(startdate >= begin && enddate <= end)
                            return true;
                    }
                  return false;
                }, this);

                if(lessonResult.length > 0)
                {
                  lessonResult = lessonResult.filterBy(function (o, k) {
                  if (o.data.type == "cyclic" || o.data.type == "personal")
                    for(var eventObjects in v.data.objects)
                    {
                      if (o.doz.containsKey(eventObjects) || o.room.containsKey(eventObjects))
                        if(o.data.block == (bl+1) && numbertoday(o.data.dow) == wd)
                          if(startdate >= begin && enddate <= end)
                            return true;
                    }
                  return false;
                  }, this);
                  var collision = false;
                  if(lessonResult.length > 0)
                    collision = true;
                  ret[bl][wd].push(v.getEventView(this.type, bl, collision));
                  this.visibleEvents.push(v.data);
                }
              }
              bl = null;
            }
          }
          weekpointer.setDate(weekpointer.getDate() + 1);
        }
      }
    }, this);

    //zyklische Termine hinzufügen
    this.data.eachKey(function (k, v) {
      if (v.data.type == "cyclic" || v.data.type == "personal") {
        //zyklischer Termin

        if(MySched.eventlist.checkRessource(v.data.room + " " + v.data.doz + " " + v.data.clas, v.data.dow, v.data.block, true) != "")
          return;

        var wd = v.getWeekDay(),
          bl = v.getBlock() - 1,
          date = null;
        if (v.isSporadic()) {
          sp.push(v.getSporadicView(this));
        } else {
          if (wd) if (typeof bl != "undefined") {
            if (!ret[bl][wd]) ret[bl][wd] = [];

            wp = Ext.Date.clone(wpMO);

            var dow = daytonumber(wd) - 1;

            wp.setDate(wp.getDate() + dow);

            date = wp;

            var begin = MySched.session["begin"].split(".");
            begin = new Date(begin[2], begin[1]-1, begin[0]);
            var end = MySched.session["end"].split(".");
            end = new Date(end[2], end[1]-1, end[0]);

            if ((date >= begin && date <= end) || (this.type == "delta" || this.id == "respChanges"))
            {
              ret[bl][wd].push(v.getCellView(this));
              this.visibleLessons.push(v.data);
            }
          }
        }
      }
    }, this);

    this.htmlView = Ext.clone(ret);

    return ret;
  },
  load: function (url, type, cb, scope, username, tmi) {
    var scheduleTask = 'UserSchedule.load';
    if(username == "delta")
      scheduleTask = 'Delta.load';

    var defaultParams = {
      username: username,
      jsid: MySched.SessionId,
      semesterID: MySched.class_semester_id,
      scheduletask: scheduleTask
    };

    if (type == 'json') this.reader = new SchedJsonReader();
    else this.reader = new SchedXmlReader();

    this.proxy = Ext.create('Ext.data.proxy.Rest', {
      url: url,
      extraParams: defaultParams,
      reader: this.reader
    });

    this.proxy.read(new Ext.data.Operation({action: 'read'}), cb, scope);
  },
  /**
   * Ueberprueft ob existierende Veranstaltungen noch existieren
   * @param {Object} against Summe aller existierender Veranstaltungen
   */
  checkLectureVersion: function (against) {
    var ret = {};
    var newdatas = {};
    this.data.each(function (v) {
      v.data["css"] = "";
    });
    var newdatas = this.data.clone();
    var funcsort = function numsort(a, b) {
      if (a.data.subject.toString() > b.data.subject.toString()) {
        return 1; // a steht vor b
      }
      else if (a.data.subject.toString() < b.data.subject.toString()) {
        return -1; // b steht vor a
      }
      return 0; // nix passiert
    }

    newdatas.sort("ASC", funcsort);
    var keystoremove = Array();
    var counter = 0;
    ret.data = against.data;
    against.data.sort("ASC", funcsort);
    ret.showMsg = false;
    ret.ret = "";
    for (var i = 0; i < this.data.length; i++) {
      if (against.data.containsKey(this.data.items[i].id)) {
        // Veranstaltung existiert :)
        if (against.data.get(this.data.items[i].id).clas.keys.toString() != this.data.items[i].clas.keys.toString() || against.data.get(this.data.items[i].id).doz.keys.toString() != this.data.items[i].doz.keys.toString() || against.data.get(this.data.items[i].id).room.keys.toString() != this.data.items[i].room.keys.toString()) {
          // Es hat sich etwas ge�ndert
          newdatas.removeAtKey(this.data.items[i].id);
          newdatas.add(this.data.items[i].id, against.data.get(this.data.items[i].id));
          newdatas.get(this.data.items[i].id).data["css"] = "movedto";
        }
      }
      else {
        // Veranstaltung existiert nicht mehr :(
        if (this.data.items[i].data.type != "personal" || (this.data.items[i].data.responsible != "mySchedule" && this.data.items[i].data.type == "personal")) {
          keystoremove.push(this.data.items[i].id);
          for (var n = 0; n < against.data.length; n++) {
            if (against.data.items[n].data.subject.toString() == this.data.items[i].data.subject.toString()) {
              if (!newdatas.containsKey(against.data.items[n].data.key.toLowerCase())) {
                newdatas.add(against.data.items[n].data.key, against.data.items[n]);
                newdatas.get(against.data.items[n].data.key).data["css"] = "movedto";
              }
            }
            if (against.data.items[n].data.subject.toString() > this.data.items[i].data.subject.toString()) {
              break;
            }
          }
        }
      }
    }

    for (i = 0; i < keystoremove.length; i++) {
      newdatas.removeAtKey(keystoremove[i]);
    }

    this.data.clear();
    this.data.addAll(newdatas.items);

    MySched.Authorize.saveIfAuth(false);

    MySched.selectedSchedule.eventsloaded = null;
    MySched.selectedSchedule.refreshView();

        var func = function () {
          MySched.SelectionManager.stopSelection();
           MySched.SelectionManager.startSelection();
        }
        Ext.defer(func, 50);
  },
  /**
   * Prueft verschiedene Vorbedinungen
   * @param {Object} o
   * @param {Object} arg
   */
  preParseLectures: function (o, arg) {
    // Funktion nach dem Auth ausfuehren und loeschen -> SPeichern geklickt
    if (MySched.Authorize.afterAuthCallback) {
      MySched.Authorize.afterAuthCallback();
      MySched.Authorize.afterAuthCallback = null;
    }
    return this.parseLectures(o);
  },
  loadsavedLectures: function (o, arg) {

    if (o.resultSet != null) {
      var r = o.resultSet.records;

      var l, key, e;

      //this.data.clear();
      for (var i = 0, len = r.length; i < len; i++) {
        e = r[i];
        // Filtert Veranstaltungen ohne Datum aus
        if (Ext.isEmpty(e.data.dow)) {
          continue;
        }
        this.data.add(e.data.key, e);
      }
    }

    var semesterbegin = Ext.select(".mysched_semesterbegin");

    if(!MySched.SessionId)
    {
      var tree = MySched.Tree.tree;

        var treeRoot = tree.getRootNode();

          var semid = treeRoot.firstChild.data.id;

      semid = semid.split(".")

      semid = semid[0];

          var deltaid = semid + ".1.delta";

          var deltaSched = new mSchedule(deltaid, "Änderungen (zentral)").init("delta", deltaid);
            deltaSched.show();
            //MySched.selectedSchedule.grid.showSporadics();
            MySched.layout.viewport.doLayout();
            MySched.selectedSchedule.responsible = "delta";
            MySched.selectedSchedule.status = "saved";
        }

    return;
  },
  /**
   * Callback zum parsen der XML Datei in mLecture
   * @param {Object} o
   * @param {Object} arg
   */
  parseLectures: function (o) {
    this.fireEvent('load', this);
    var r = o.resultSet.records;
    var l, key;
    for (var i = 0, len = r.length; i < len; i++) {
      var e = r[i];
      // Filtert Veranstaltungen ohne Datum aus
      if (e.data != null) if (Ext.isEmpty(e.data.subject) || Ext.isEmpty(e.data.dow)) {
        continue;
      }
      if (e.data != null)
        this.data.add(e.data.key, e);
    }

    if (MySched.layout.tabpanel.getComponent('mySchedule')) {
            //MySched.Schedule.save(_C('ajaxHandler'), false, "UserSchedule.save");
          var func = function () {
            MySched.SelectionManager.stopSelection();
             MySched.SelectionManager.startSelection();
          }
          Ext.defer(func, 50);
            this.eventsloaded = null;
            this.refreshView();
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
          var tabID = MySched.layout.tabpanel.getComponent('mySchedule').tab.el.dom;
          var dropTarget = new Ext.dd.DropTarget(tabID, MySched.Base.getDropConfig());

    this.markUnchanged();
  },
  /**
   * Callback zum parsen der XML Datei in mLecture
   * @param {Object} o
   * @param {Object} arg
   */
  parseLecturesdiff: function (o, arg) {
    // Fuegt dem Uebergabeparameter das Result hinzu
    Ext.applyIf(arg.params, {
      result: o
    });
    var r = o.records;

    var l, key;
    for (var i = 0, len = r.length; i < len; i++) {
      e = r[i];
      // Filtert Veranstaltungen ohne Datum aus
      if (Ext.isEmpty(e.data.subject) || Ext.isEmpty(e.data.dow)) {
        continue;
      }
      MySched.selectedSchedule.data.add(e.data.key, e);
    };
    if (arg.callback) arg.callback.createDelegate(arg.scope)(arg.params);
  },
  show: function (ret, closeable) {
    if(closeable != false)
      closeable = true;
    this.grid = getSchedGrid(this.getGridData());
    this.grid.mSchedule = this;
    if (ret) return this.grid;
    var name = this.title.replace(/\s*\/\s*/g, ' ');
    MySched.layout.createTab(this.getId(), name, this.grid, this.type, closeable);

    if (this.type === "delta") {
      MySched.selectedSchedule.data = MySched.delta.data;
    }
    else {
      if (MySched.Authorize.role == "user" && this.getId() == "mySchedule") {
        //DO NOTHING
      }
      else {
        this.dragzone = new Ext.dd.DragZone(this.getId(), {
          containerScroll: true,
          ddGroup: 'lecture'
        });
      }
    }
    if(MySched.loadMask)
      MySched.loadMask.destroy();
  },
  refreshView: function () {
    if (!this.grid) return this.show();
    if(this.type != "delta")
      this.data.addAll(MySched.eventlist.getEvents());
    this.grid.loadData(this.getGridData());
    var func = function () {
          MySched.SelectionManager.stopSelection();
           MySched.SelectionManager.startSelection();
        }
        Ext.defer(func, 50);
  },
  getBlockStatus: function (wd, block) {
    var weekdays = {
      1: "monday",
      2: "tuesday",
      3: "wednesday",
      4: "thursday",
      5: "friday",
      6: "saturday",
      7: "saturday"
    };

    var wpMO = null;
    var cd = Ext.ComponentMgr.get('menuedatepicker');
    var wp = null;

    wp = Ext.Date.clone(cd.value);

    wpMO = getMonday(wp);

    var dow = wd;

    wpMO.setDate(wpMO.getDate() + dow);

    var date = wpMO;

    var begin = MySched.session["begin"].split(".");
    begin = new Date(begin[2], begin[1]-1, begin[0]);
    var end = MySched.session["end"].split(".");
    end = new Date(end[2], end[1]-1, end[0]);

    if ((date >= begin && date <= end)) {
      // Numerischer Index erlaubt
      if (weekdays[wd]) wd = weekdays[wd];
      if (this.getBlockCache()[wd])
        if (this.blockCache[wd][block])
          return this.blockCache[wd][block];
    }
    return 0;
  },
  getBlockCache: function (forceGenNew) {
    // Generiere den BlockCache neu falls notwendig
    if (forceGenNew || Ext.isEmpty(this.blockCache)) {
      this.blockCache = {
        monday: [],
        tuesday: [],
        wednesday: [],
        thursday: [],
        friday: [],
        saturday: [],
        sunday: []
      };
      this.data.each(function (l) {
        if(l.data.type)
        {
          var wd = l.getWeekDay();
          var b = l.getBlock();
          b = b - 1;
          if (!this.blockCache[wd][b]) this.blockCache[wd][b] = 1;
          else this.blockCache[wd][b]++;
        }
      }, this);
    }

    return this.blockCache;
  },
  lectureExists: function (l) {
    if (l.getId) l = l.getId();
    if (l.match('##')) l = l.split('##')[1];
    return this.data.containsKey(l);
  },
  markChanged: function () {
    if (this.changed) return;
    this.fireEvent("changed", this);
    this.changed = true;
  },
  markUnchanged: function () {
    if (!this.changed) return;
    this.changed = false;
  },
  save: function (url, success, scheduletask) {
    if (MySched.Authorize.user != null) {
      if (this.fireEvent("beforeSave", this, url) === false) return

      if (scheduletask == "UserSchedule.save") {
        var defaultParams = {
          jsid: MySched.SessionId,
          sid: MySched.Base.sid,
          scheduletask: scheduletask
        };
        var data = this.exportData();
      }
      else {
        var defaultParams = {
          jsid: MySched.SessionId,
          sid: MySched.Base.sid,
          semesterID: MySched.class_semester_id,
          id: this.id,
          scheduletask: scheduletask
        }
        var data = this.exportData("json", "personal");
      }
      if (success != false)
        var savewait = Ext.MessageBox.wait('Ihr Stundenplan wird gespeichert', 'Bitte warten...');
      else var savewait = null;
      Ext.Ajax.request({
        url: url,
        jsonData: data,
        scope: savewait,
        method: 'POST',
        params: defaultParams,
        success: function (resp, ret) {
          if (savewait != null)
            Ext.MessageBox.hide();
          try {
            var json = Ext.decode(resp.responseText);
            if (json["code"]) {
              if (json["code"] != 1) {
                Ext.Msg.show({
                  title: 'Error',
                  msg: json["reason"],
                  buttons: Ext.Msg.OK,
                  minWidth: 400
                });
                MySched.selectedSchedule.status = "unsaved";
                Ext.ComponentMgr.get('btnSave').enable();
                var tab = MySched.layout.tabpanel.getComponent(MySched.selectedSchedule.id);
                tab.mSchedule.status = "unsaved";
                tab = Ext.get(MySched.layout.tabpanel.getTabEl(tab)).child('.' + MySched.selectedSchedule.type + 'Icon');
                if (tab) tab.replaceClass('' + MySched.selectedSchedule.type + 'Icon', '' + MySched.selectedSchedule.type + 'IconSave');
              }
            }
          }
          catch(e)
          {}
        }
      });
      this.fireEvent("save", this, url);
      this.markUnchanged();
    }
    else {
      Ext.Msg.show({
        title: 'Error',
        msg: "Bitte melde dich zuerst an!",
        buttons: Ext.Msg.OK,
        minWidth: 400
      });
    }
  },
  asArray: function () {
    this.asArrRet = [];
    var d = this.data;
    if (d.asArray) d = d.asArray();
    Ext.each(d, function (e) {
      if (Ext.isEmpty(e)) return;
      if (typeof e.getCellView == "undefined") return;
      var cell = e.getCellView(this);
      if (e.asArray) e = e.asArray();
      e.cell = cell;
      this.asArrRet[this.asArrRet.length] = e;
    }, this);
    //if (this.asArrRet.length == 1) return this.asArrRet[0];
    return this.asArrRet;
  },
  asPersArray: function () {
    this.asArrRet = [];
    var d = this.data;
    if (d.asArray) d = d.asArray();
    Ext.each(d, function (e) {
      if (Ext.isEmpty(e)) return;
      if (e.data.type == "personal") {
        var cell = e.getCellView(this);
        if (e.asArray) e = e.asArray();
        e.cell = cell;
        this.asArrRet[this.asArrRet.length] = e;
      }
    }, this);
    //if (this.asArrRet.length == 1) return this.asArrRet[0];
    return this.asArrRet;
  }
});

/**
 * LectureModel
 * @param {Object} lecture
 */
Ext.define('mLecture', {
  extend: 'MySched.Model',

  constructor: function (id, data, semesterID, plantypeID) {
    var doz, clas, room, cellTemplate, infoTemplate;
    var owner = data.owner;
    var stime = data.stime;
    var etime = data.etime;
    var showtime = data.showtime;
    this.doz = new MySched.Collection();
    this.clas = new MySched.Collection();
    this.room = new MySched.Collection();
    this.subject = new MySched.Collection();
    mLecture.superclass.constructor.call(this, id, data);
    this.loadDoz(data.doz);
    this.loadClas(data.clas);
    this.loadRoom(data.room);
    this.loadSubject(data.subject);
    this.data = data;
    this.semesterID = semesterID;
    this.plantypeID = plantypeID;

    if(this.data.moduleID == MySched.searchModuleID && !Ext.isEmpty(MySched.searchModuleID))
    {
    	this.data.css = this.data.css+" searchSubject";
    }

    //New CellStyle
    this.setCellTemplate();

    var infoTemplateString = '<div>' + '<small><span class="def">Raum:</span> {room_shortname}<br/>' + '<span class="def">Dozent:</span><big> {doz_name}</big><br/>' + '<span class="def">Semester:</span> <br/>{clas_full}<br/>';
    if (this.data.changes) infoTemplateString += '<span class="def">Changes:</span> {changes_all}';
    infoTemplateString += '</small></div>';

    this.infoTemplate = new Ext.Template(infoTemplateString);

    this.sporadicTemplate = new Ext.Template('<div id="{parentId}##{key}" class="{css} sporadicBox lectureBox">' + '<b>{desc}</b> <small><i>({desc:defaultValue("Keine Beschreibung")})</i> Raum: {room_short} - Dozent: {doz_name} - {clas_short}</small>' + '</div>');
  },
  getDetailData: function (d) {
    return Ext.apply(this.getData(d), {
      'doz_name': this.getDozNames(this.getDoz(), true, d),
      'clas_full': this.getClassFull(this.getClas()),
      'clas_short': this.getClasShorter(this.getClas(), true, d),
      'room_shortname': this.getRoomShort(this.getRoom(), true, d),
      'week_day': weekdayEtoD(this.getWeekDay()),
      'block': this.getBlock(),
      'category': this.getCategory(),
      'changes_all': this.getChanges(d),
      'status_icons': this.getStatus(d),
      'top_icon': this.getTopIcon(d),
      'comment': this.getComment(d)/*,
      'events': this.getEvents(d)*/
    });
  },
  getComment: function (d) {
	if(!Ext.isEmpty(d.comment) && Ext.isString(d.comment))
		return "<br/>("+d.comment+")";
	else
		return "";
  },
  getEvents: function (d) {
    var ret = "";
    ret = MySched.eventlist.checkRessource(this.data.room + " " + this.data.doz + " " + this.data.clas, this.data.dow, this.data.block);
    return ret;
  },
  getTopIcon: function (d) {
  	if(isset(this.data.lessonChanges))
    	if (this.data.lessonChanges.status == "new")
    		return '<div data-qtip="Diese Veranstaltung ist neu hinzugekommen" class="top_icon_image">Neu</div>';

    if(isset(this.data.periodChanges))
    	if (this.data.periodChanges.status == "new")
    		return '<div data-qtip="Diese Veranstaltung ist neu hinzugekommen" class="top_icon_image">Neu</div>';
    	else if(this.data.periodChanges.status == "moved")
    		return '<div data-qtip="Diese Veranstaltung wurde verschoben" class="top_icon_image">Verschoben</div>';

   	if (this.data.css == "mysched_proposal")
   		return '<div data-qtip="Diese Veranstaltung wurde ihnen vorgeschlagen weil die ursprüngliche Veranstaltung nicht mehr vorhanden ist" class="top_icon_image">Vorschlag</div><br/>';

    return "";
  },
  getStatus: function (d) {
    var ret = '<div class="status_icons"> ';
    
    if(this.data.ecollaborationLink != null)
    	 ret += '<a target="_blank" href="' + this.data.ecollaborationLink + '"><img data-qtip="Moodlekurs aufrufen" class="status_icons_ecollabLink" src="' + MySched.mainPath + 'images/collab.png" width="12" heigth="12"/></a>';

    if (MySched.Authorize.user != null && MySched.Authorize.user != "" && typeof d.parentId != "undefined") {
      var parentIDArr = d.parentId.split(".");
      parentIDArr = parentIDArr[(parentIDArr.length-1)];
      if (parentIDArr != 'delta') {
        if (d.parentId == 'mySchedule') ret += '<img data-qtip="Veranstaltung aus Ihrem Stundenplan entfernen" class="status_icons_add" src="' + MySched.mainPath + '/images/delete.png" width="12" heigth="12"/>';
        else if (d.parentId != 'mySchedule' && MySched.Schedule.lectureExists(this)) ret += '<img data-qtip="Veranstaltung aus Ihrem Stundenplan entfernen" class="status_icons_add" src="' + MySched.mainPath + '/images/delete.png" width="12" heigth="12"/>';
        else ret += '<img data-qtip="Veranstaltung Ihrem Stundenplan hinzuf&uuml;gen" class="status_icons_add" src="' + MySched.mainPath + '/images/add.png" width="12" heigth="12"/>';
        //ret += '<img data-qtip="eStudy aufrufen" class="status_icons_estudy" src="' + MySched.mainPath + '/images/estudy_logo.jpg" width="12" heigth="12"/>';
      }
    }

    if ((d.owner == MySched.Authorize.user || (MySched.Authorize.user == MySched.class_semester_author && d.type == "personal")) && MySched.Authorize.user != null && MySched.Authorize.user != "") {
      ret += '<img data-qtip="Veranstaltung ändern" class="status_icons_edit" src="' + MySched.mainPath + 'images/icon-edit.png" width="12" heigth="12"/>';
      ret += '<img data-qtip="Veranstaltung löschen" class="status_icons_delete" src="' + MySched.mainPath + 'images/icon-delete.png" width="12" heigth="12"/>';
    }

    //ret += '<img data-qtip="Informationen anzeigen" class="status_icons_info" src="' + MySched.mainPath + '/images/information.png" width="12" heigth="12"/>';

    return ret + ' </div>';
  },
  getChanges: function (lec) {
    var r = "";
    var t = "";
    var c = "";


    return "";


    if (lec) if (lec.changes) {
      if (lec.changes.rooms) {
        var rooms = lec.changes.rooms;
        r += "<span>R&auml;ume:<br/>";
        for (var room in rooms) {
          if (room != "") {
            var temp = MySched.Mapping.getObject("room", room);
            if (!temp) r += '<small class="' + rooms[room] + '"> ' + room + ' </small>, ';
            else r += '<small class="' + rooms[room] + '"> ' + temp.name + ' </small>, ';
            r += "<br/>";
          }
        }
        if (r != "") {
          var l = r.length - 2;
          r = r.substr(0, l);
        }
        r += "</span><br/>";
      }
      if (lec.changes.teachers) {
        var teachers = lec.changes.teachers;
        t += "<span>Dozenten:<br/>";
        for (var teacher in teachers) {
          if (teacher != "") {
            var temp = MySched.Mapping.getObject("doz", teacher);
            if (!temp) t += '<small class="' + teachers[teacher] + '"> ' + teacher + ' </small>, ';
            else t += '<small class="' + teachers[teacher] + '"> ' + temp.name + ' </small>, ';
            t += "<br/>";
          }
        }
        if (t != "") {
          var l = t.length - 2;
          t = t.substr(0, l);
        }
        t += "</span><br/>";
      }
      if (lec.changes.classes) {
        var classes = lec.changes.classes;
        c += "<span>Semester:<br/>";
        for (var clas in classes) {
          if (clas != "") {
            var temp = MySched.Mapping.getObject("clas", clas);
            if (!temp) c += '<small class="' + classes[clas] + '"> ' + clas + ' </small>, ';
            else c += '<small class="' + classes[clas] + '"> ' + temp.department + " - " + temp.name + ' </small>, ';
            c += "<br/>";
          }
        }
        if (c != "") {
          var l = c.length - 2;
          c = c.substr(0, l);
        }
        c += "</span><br/>";
      }
    }
    return r + t + c;
  },
  loadDoz: function (arr) {
    if (arr) {
      var mydoz = arr.split(" ");
      Ext.each(mydoz, function (e) {
        var ndoz = new mDozent(e);
        this.doz.add(ndoz);
      }, this)
    }
  },
  loadRoom: function (arr) {
    if (arr) {
      var myroom = arr.split(" ");
      Ext.each(myroom, function (e) {
        this.room.add(new mRoom(e));
      }, this)
    }
  },
  loadSubject: function (arr) {
    if (arr) {
      var mySubject = arr.split(" ");
      Ext.each(mySubject, function (e) {
        this.subject.add(new mSubject(e));
      }, this)
    }
  },
  loadClas: function (arr) {
    if (arr) {
      var myclas = arr.split(" ");
      Ext.each(myclas, function (e) {
        this.clas.add(new mClas(e));
      }, this)
    }
  },
  getData: function (addData) {
    if (!this.data.name) this.data.name = this.getName();
    if (!this.data.desc) this.data.desc = this.getDesc();
    return mLecture.superclass.getData.call(this, addData);
  },
  getRoomShort: function (col, tag, lec) {
  	var removed = [];
    var ret = [];

	if(isset(this.data.periodChanges))
	     	if (this.data.periodChanges.status == "changed") {
	     		if(isset(this.data.periodChanges.roomIDs))
	     		{
	     			for(var roomID in this.data.periodChanges.roomIDs)
	     			{
	     				var roomIDValue = this.data.periodChanges.roomIDs[roomID];
	     				if(roomIDValue == "removed")
	     				{
		     				var roomNameHTML = '<small class="oldroom">' + MySched.Mapping.getRoomName(roomID) + '</small>';
		   	 				removed.push(roomNameHTML);
	   	 				}
	     			}
	     		}
	     	}

    for (var n = 0; n < col.length; n++) {
		var roomName = col.items[n].getName();
		var roomNameHTML = "";
		if(isset(this.data.periodChanges))
			if(isset(this.data.periodChanges.roomIDs))
		    	if(isset(this.data.periodChanges.roomIDs[col.items[n].getId()]))
		    		if(this.data.periodChanges.roomIDs[col.items[n].getId()] == "new")
						roomNameHTML = '<em class="roomshortname">' + roomName + '</em>';
		if(roomNameHTML == "")
    		roomNameHTML = '<small class="roomshortname">' + roomName + '</small>';

    	ret.push(roomNameHTML);
    }

    return ret.join(', ')+" "+removed.join(', ');
  },
  getDozNames: function (col, tag, lec) {
    var ret = [];
    var removed = [];

    if(isset(this.data.lessonChanges))
    	if(this.data.lessonChanges.status == "changed")
    		if(isset(this.data.lessonChanges.teacherIDs))
    			for(var teacherID in this.data.lessonChanges.teacherIDs)
	     			{
	     				var teacherIDValue = this.data.lessonChanges.teacherIDs[teacherID];
	     				if(teacherIDValue == "removed")
	     				{
		     				var teacherNameHTML = '<small class="olddoz">' + MySched.Mapping.getDozName(teacherID) + '</small>';
		   	 				removed.push(teacherNameHTML);
	   	 				}
	     			}


	for (var n = 0; n < col.length; n++) {
    	var dozName = col.items[n].getName();
		var dozNameHTML = "";
		if(isset(this.data.lessonChanges))
			if(isset(this.data.lessonChanges.teacherIDs))
		    	if(isset(this.data.lessonChanges.teacherIDs[col.items[n].getId()]))
		    		if(this.data.lessonChanges.teacherIDs[col.items[n].getId()] == "new")
						dozNameHTML = '<em class="dozname">' + dozName + '</em>';
		if(dozNameHTML == "")
    		dozNameHTML = '<small class="dozname">' + dozName + '</small>';
    	ret.push(dozNameHTML);
    }

    return ret.join(', ')+" "+removed.join(', ');
  },
  getNames: function (col, shortVersion) {
    var ret = [];
    col.each(function (e) {
      // Abkuerzung anstatt Ausgeschrieben
      var temproom = "";
      if (shortVersion) temproom = e.getId();
      else temproom = e.getName();
      this.push(temproom);
    }, ret);
    // Bei der kurzen Varianten ohne BLANK
    if (shortVersion) return ret.join(',');
    return ret.join(', ');
  },
  getClassFull: function (col) {
    var ret = [];
    col.each(function (e) {
      // Abkuerzung anstatt Ausgeschrieben
      temproom = e.getFullName();
      this.push(temproom);
    }, ret);
    // Bei der kurzen Varianten ohne BLANK
    return ret.join(',<br/>');
  },
  getClasShorter: function (col, tag, lec) {
    var ret = [];
    var removed = [];

    if(isset(this.data.lessonChanges))
    	if(this.data.lessonChanges.status == "changed")
    		if(isset(this.data.lessonChanges.classIDs))
    			for(var classID in this.data.lessonChanges.classIDs)
	     			{
	     				var classIDValue = this.data.lessonChanges.classIDs[classID];
	     				if(classIDValue == "removed")
	     				{
		     				var classNameHTML = '<small class="oldclass">' + MySched.Mapping.getObjectField("clas", classID, "shortname") + '</small>';
		   	 				removed.push(classNameHTML);
	   	 				}
	     			}


	for (var n = 0; n < col.length; n++) {
    	var className = MySched.Mapping.getObjectField("clas", col.items[n].getId(), "shortname");

    	var classNameHTML = "";
		if(isset(this.data.lessonChanges))
		if(isset(this.data.lessonChanges.classIDs))
			if(isset(this.data.lessonChanges.classIDs[col.items[n].getId()]))
				if(this.data.lessonChanges.classIDs[col.items[n].getId()] == "new")
					classNameHTML = '<em class="classhorter">' + className + '</em>';
		if(classNameHTML == "")
			classNameHTML = '<small class="classhorter">' + className + '</small>';

		ret.push(classNameHTML);
    }

    return ret.join(', ')+" "+removed.join(', ');
  },
  getName: function () {
    return MySched.Mapping.getLectureName(this.data.id);
  },
  getDesc: function () {
    return MySched.Mapping.getLectureDescription(this.data.id);
  },
  getDoz: function () {
    return this.doz;
  },
  getClas: function () {
    return this.clas;
  },
  getRoom: function () {
    return this.room;
  },
  getWeekDay: function () {
    return numbertoday(parseInt(this.data.dow));
  },
  getBlock: function () {
    return this.data.block;
  },
  getCategory: function () {
	if(!Ext.isEmpty(this.data.category) && Ext.isString(this.data.category))
		return "-"+this.data.category;
	else
		return "";
  },
  setCellTemplate: function (t) {

    var time = "";
    var blocktimes = blocktotime(this.data.block);
    if (this.data.showtime == "full") {
      if (blocktimes[0] != this.data.stime || blocktimes[1] != this.data.etime) time = "(" + this.data.stime + "-" + this.data.etime + ")";
    }
    else if (this.data.showtime == "first") {
      if (blocktimes[0] != this.data.stime) time = "(ab " + this.data.stime + ")";
    }
    else if (this.data.showtime == "last") {
      if (blocktimes[1] != this.data.etime) time = "(bis " + this.data.etime + ")";
    }
    if (MySched.selectedSchedule) {
      var width = MySched.selectedSchedule.grid.getWidth();
    }
    if (t == "room") {
      var dozroomstring = stripHTML((this.getDozNames(this.getDoz()) + this.getClasShorter(this.getClas())));
      if (dozroomstring.length * 5.5 < width) {
        this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" class="{css} scheduleBox lectureBox">' + '<b class="lecturename">{desc}{category} {comment}</b><br/>{doz_name} / {clas_short} ' + time + ' {status_icons}</div>');
      }
      else {
        this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" class="{css} scheduleBox lectureBox">' + '<b class="lecturename">{desc}{category} {comment}</b><br/>{doz_name}<br/>{clas_short} ' + time + ' {status_icons}</div>');
      }
    }
    else if (t == "doz") {
      var dozroomstring = stripHTML((this.getClasShorter(this.getClas()) + this.getRoomShort(this.getRoom())));
      if (dozroomstring.length * 5.5 < width) {
        this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" class="{css} scheduleBox lectureBox">' + '<b class="lecturename">{desc}{category} {comment}</b><br/>{clas_short} / {room_shortname} ' + time + ' {status_icons}</div>');
      }
      else {
        this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" class="{css} scheduleBox lectureBox">' + '<b class="lecturename">{desc}{category} {comment}</b><br/>{clas_short}<br/>{room_shortname} ' + time + ' {status_icons}</div>');
      }
    }
    else {
      	var dozroomstring = stripHTML((this.getDozNames(this.getDoz()) + this.getRoomShort(this.getRoom())));
      	var classcss = "scheduleBox";
      	var lecturecss = "";

		if(isset(this.data.lessonChanges))
      	{
	     	if (this.data.lessonChanges.status == "removed") {
	        	classcss += " lectureBox_dis";
	        	lecturecss = "lecturename_dis";
	      	}
	    }

	    if(isset(this.data.periodChanges))
	    {
    		if (this.data.periodChanges.status == "removed") {
	    		classcss += " lectureBox_dis";
	    		lecturecss = "lecturename_dis";
    		}
     	}

	    if(lecturecss == "")
	    {
      		classcss += " lectureBox";
	        lecturecss = "lecturename";
	    }


      if (dozroomstring.length * 5.5 < width) {
        this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" class="{css} ' + classcss + '">' + '{top_icon}<b class="' + lecturecss + '">{desc}{category} {comment}</b><br/>{doz_name} / {room_shortname} ' + time + ' {status_icons}</div>');
      }
      else {
        this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" class="{css} ' + classcss + '">' + '{top_icon}<b class="' + lecturecss + '">{desc}{category} {comment}</b><br/>{doz_name}<br/>{room_shortname} ' + time + ' {status_icons}</div>');
      }
    }
  },
  setInfoTemplate: function (t) {
    this.infoTemplate.set(t, true);
  },
  getCellView: function (relObj) {
    var d = this.getDetailData({
      parentId: relObj.getId()
    });
    if (relObj.getId() != 'mySchedule' && MySched.Schedule.lectureExists(this)) d.css = ' lectureBox_cho';
    if (d.changes) {

    }
    return this.cellTemplate.apply(d);
  },
  getSporadicView: function (relObj) {
    var d = this.getDetailData({
      parentId: relObj.getId()
    });
    if (relObj.getId() != 'mySchedule' && MySched.Schedule.lectureExists(this)) d.css = ' lectureBox_cho';
    return this.sporadicTemplate.apply(d);
  },
  showInfoPanel: function () {
    return this.infoTemplate.apply(this.getDetailData(this));
  },
  has: function (type, val) {
    var o = {
      ret: false
    };
    Ext.each(this[type].getField('id'), function (o) {
      if (val.equal(o)) this.ret = true;
    }, o);
    return o.ret;
  },
  isSporadic: function () {
    return this.data.type == 'sporadic';
  }
});

Ext.define('mEventlist', {
  extend: 'MySched.Model',

  constructor: function () {
    var data;
    this.data = new MySched.Collection();
  },
  addEvent: function (e) {
    // Fuegt ein Event hinzu
    if(e.data.starttime == "00:00")
      e.data.starttime  = "08:00";
    if(e.data.endtime == "00:00")
      e.data.endtime  = "19:00";
    this.data.add(e.data.eid, e);
  },
  getEvent: function (id) {
    var idsplit = id.split("_");
    var datas = this.data.filterBy(function (o, k) {
      if (k == idsplit[1]) return true;
      return false;
    }, this);

    return datas.items[0];
  },
  checkRessource: function (res, dow, block, reserve) {
    var ret = "";
    var resarr = res.split(" ");
    var found = false;
    dow = dow - 1;
    if(!reserve)
      reserve = false;

    if (Ext.isNumber(this.data.items.length) == true)
      for (var y = 0; y < this.data.items.length; y++) {
      found = false;
      if (Ext.isEmpty(this.data.items[y].data.objects) == false)
        for (var item in this.data.items[y].data.objects) {
        if (Ext.isNumber(resarr.length) == true)
          for (var i = 0; i < resarr.length; i++) {
          if (resarr[i].toLowerCase() == item.toLowerCase()) {
            //sporadischer Termin
            var wd = null;
            var bl = null;
            var clickeddate = Ext.ComponentMgr.get('menuedatepicker');
            var weekpointer = null;

            weekpointer = Ext.Date.clone(clickeddate.value);

            if (weekpointer != "") {
              while (weekpointer.getDay() != 1) //Montag ermitteln
              {
                weekpointer.setDate(weekpointer.getDate() - 1);
              }
            }

            var lessondate = weekpointer;
            lessondate.setDate(weekpointer.getDate() + dow);

            var startdate = this.data.items[y].data.startdate.split(".");
            startdate = new Date(startdate[2], startdate[1]-1, startdate[0]);
            var enddate = this.data.items[y].data.enddate.split(".");
            enddate = new Date(enddate[2], enddate[1]-1, enddate[0]);

            if (startdate <= lessondate && enddate >= lessondate && this.data.items[y].data.reserve === reserve) {
              var blotimes = blocktotime(parseInt(block));

              var sbtime = blotimes[0];
              var ebtime = blotimes[1];

              if (this.data.items[y].data.starttime <= sbtime && this.data.items[y].data.endtime >= ebtime) {
                ret += this.data.items[y].getEventView();
                found = true;
                break;
              }
              else if ((this.data.items[y].data.starttime >= sbtime && this.data.items[y].data.starttime < ebtime) && (this.data.items[y].data.endtime >= sbtime && this.data.items[y].data.endtime <= ebtime)) {
                if(reserve != true)
                {
                  ret += this.data.items[y].getEventView();
                  found = true;
                  break;
                }
              }
              else if (this.data.items[y].data.starttime >= sbtime && this.data.items[y].data.starttime < ebtime) {
                ret += this.data.items[y].getEventView();
                found = true;
                break;
              }
              else if (this.data.items[y].data.endtime > sbtime && this.data.items[y].data.endtime <= ebtime) {
                if(reserve != true)
                {
                  ret += this.data.items[y].getEventView();
                  found = true;
                  break;
                }
              }
            }
          }
        }
        if (found == true) break;
      }
    }
    return ret;
  },
  getEvents: function (type, value) {
    if (Ext.isEmpty(type) && Ext.isEmpty(value)) return this.data.items;
    var datas = this.data.filterBy(function (o, k) {
      for (var item in o.data.objects) {
        if (item == value) return true;
      }
      return false;
    }, this);

    return datas.items;
  }
});

/**
 * EventModel
 * @param {Object} Event
 */
Ext.define('mEvent', {
  extend: 'MySched.Model',

  constructor: function (id, data) {
    var eventTemplate;
    this.id = id;
    this.data = data;

    if(this.data.enddate == "00.00.0000")
      this.data.enddate = this.data.startdate;

    this.data.starttime = this.data.starttime.substring(0, 5);
    this.data.endtime = this.data.endtime.substring(0, 5);

    var MySchedEventClass = 'MySchedEvent_' + this.data.source;

    this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + '{top_icon}<b id="MySchedEvent_{id}" class="MySchedEvent_name">{event_name}</b><br/>{doz} / {room}</div>');
  },
  getEventDetailData: function () {
    return Ext.apply(this.getData(this), {
      'event_name': this.getName(),
      'event_info': this.getEventInfoView(),
      'doz': this.getDozName(),
      'room': this.getRoomName()
    });
  },
  getName: function () {
    return this.data.title;
  },
  getDozName: function () {
    var dozS = "";

    for (var item in this.data.objects) {
      if (item.substring(0, 3) == "TR_") {
        if (dozS != "") {
          dozS += ", "
        }
        dozS += MySched.Mapping.getName("doz", item);
      }
    }
    return dozS;
  },
  getRoomName: function () {
    var roomS = "";

    for (var item in this.data.objects) {
      if (item.substring(0, 3) == "RM_") {
        if (roomS != "") {
          roomS += ", "
        }
        roomS += MySched.Mapping.getName("room", item);
      }
    }
    return roomS;
  },
  getData: function (addData) {
    return mEvent.superclass.getData.call(this, addData);
  },
  getEventView: function (type, bl, collision) {
    var d = this.getEventDetailData();
    var eventView = "";
    if (MySched.Authorize.user != null && MySched.Authorize.role != 'user' && MySched.Authorize.role != 'registered') {
      if (!this.eventTemplate.html.contains("MySchedEvent_joomla access"))
        this.eventTemplate.html = this.eventTemplate.html.replace("MySchedEvent_joomla", 'MySchedEvent_joomla access');
    }

    var MySchedEventClass = 'MySchedEvent_' + this.data.source;

    var collisionIcon = "";

    if(d.reserve === true && collision === true)
    {
      if(bl<4)
        bl++;
      var blocktimes = blocktotime(bl);
      if(blocktimes[0] < d.starttime && blocktimes[1] > d.starttime)
        collisionIcon = "<img class='MySched_EventCollision' width='24px' height='16px' data-qtip='Kollision mit einer Veranstaltung' src='" + MySched.mainPath + "images/warning.png'></img><br/>";
      if(blocktimes[0] < d.endtime && blocktimes[1] > d.endtime)
        collisionIcon = "<img class='MySched_EventCollision' width='24px' height='16px' data-qtip='Kollision mit einer Veranstaltung' src='" + MySched.mainPath + "images/warning.png'></img><br/>";
    }

    if(type === "doz")
    {
      this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + collisionIcon + '<b id="MySchedEvent_{id}" class="MySchedEvent_name">{event_name}</b><br/><small class="event_resource">{room}</small></div>');
    }
    else if(type === "room")
    {
      this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + collisionIcon + '<b id="MySchedEvent_{id}" class="MySchedEvent_name">{event_name}</b><br/><small class="event_resource">{doz}</small></div>');
    }
    else
    {
      this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + collisionIcon + '<b id="MySchedEvent_{id}" class="MySchedEvent_name">{event_name}</b><br/><small class="event_resource">{doz} / {room}</small></div>');
    }

    return this.eventTemplate.apply(d);
  },
  getEventInfoView: function () {
    var infoTemplateString = "<div id='MySchedEventInfo_" + this.id + "' class='MySchedEventInfo'>" + "<span class='MySchedEvent_desc'>Beschreibung: " + this.data.edescription + "</span><br/>" + "<span class='MySchedEvent_sdate'>Datum: " + this.data.startdate + " - " + this.data.enddate + "</span><br/>" + "<span class='MySchedEvent_stime'>Zeit: " + this.data.starttime + " - " + this.data.endtime + "</span><br/>";
    var resString = "";
    var dozS = "",
      roomS = "",
      clasS = "";

    for (var item in this.data.objects) {
      if (item.substring(0, 3) == "TR_") {
        if (dozS != "") {
          dozS += ", "
        }
        dozS += MySched.Mapping.getName("doz", item);
      }
      else if (item.substring(0, 3) == "RM_") {
        if (roomS != "") {
          roomS += ", "
        }
        roomS += MySched.Mapping.getName("room", item);
      }
      else if (item.substring(0, 3) == "CL_") {
        if (clasS != "") {
          clasS += ",<br/>"
        }
        var clasObj = MySched.Mapping.getObject("clas", item);
        if (typeof clasObj == "undefined") {
          clasS += item;
        }
        else clasS += clasObj.department + " - " + clasObj.name;
      }
    }

    if(dozS.length > 0)
    {
      if (dozS.contains(", ")) dozS = "Dozenten: " + dozS;
      else dozS = "Dozent: " + dozS;

      infoTemplateString += "<span class='MySchedEvent_doz'>" + dozS + "</span><br/>";
    }

    if(roomS.length > 0)
    {
      if (roomS.contains(", ")) roomS = "Räume: " + roomS;
      else roomS = "Raum: " + roomS;

      infoTemplateString += "<span class='MySchedEvent_room'>" + roomS + "</span><br/>";
    }

    if(clasS.length > 0)
    {
      clasS = "Semester:<br/>" + clasS;

      infoTemplateString += "<span class='MySchedEvent_clas'>" + clasS + "</span><br/></div>";
    }
    return infoTemplateString;
  }
});

/**
 * DozentModel
 * @param {Object} doz
 */
Ext.define('mDozent', {
  extend: 'MySched.Model',

  constructor: function (doz) {
    var bla = this;
    this.superclass.constructor.call(this, doz, doz);
  },
  getName: function () {
    return MySched.Mapping.getDozName(this.id);
  },
  getObjects: function () {
    return MySched.Mapping.getObjects("doz", this.id);
  }
});

/**
 * RoomModel
 * @param {Object} doz
 */
Ext.define('mRoom', {
  extend: 'MySched.Model',

  constructor: function (room) {
    mRoom.superclass.constructor.call(this, room, room);
  },
  getName: function () {
    return MySched.Mapping.getRoomName(this.id);
  },
  getObjects: function () {
    return MySched.Mapping.getObjects("room", this.id);
  }
});

/**
 * ClasModel
 * @param {Object} clas
 */
Ext.define('mClas', {
  extend: 'MySched.Model',

  constructor: function (clas) {
    mClas.superclass.constructor.call(this, clas, clas);
  },
  getName: function () {
    return MySched.Mapping.getClasName(this.id);
  },
  getFullName: function () {
    return MySched.Mapping.getObjectField("clas", this.id, "parentName") + " - " + MySched.Mapping.getObjectField("clas", this.id, "name");
  },
  getObjects: function () {
    return MySched.Mapping.getObjects("clas", this.id);
  }
});

/**
 * SubjectModel
 * @param {Object} clas
 */
Ext.define('mSubject', {
  extend: 'MySched.Model',

  constructor: function (subject) {
    mSubject.superclass.constructor.call(this, subject, subject);
  },
  getName: function () {
    return MySched.Mapping.getSubjectName(this.id);
  },
  getFullName: function () {
    return MySched.Mapping.getObjectField("subject", this.id, "parentName") + " - " + MySched.Mapping.getObjectField("subject", this.id, "name");
  },
  getObjects: function () {
    return MySched.Mapping.getObjects("subject", this.id);
  }
});

function getModuledesc(mninr) {

  if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip').destroy();
  var waitDesc = Ext.MessageBox.show({
    cls: 'mySched_noBackground',
    closable: false,
    msg: '<img  src="' + MySched.mainPath + 'images/ajax-loader.gif" />'
  });
  Ext.Ajax.request({
    url: _C('getModule'),
    method: 'POST',
    params: {
      nrmni: mninr
    },
    scope: waitDesc,
    failure: function (response, req) {
      waitDesc.hide();
      Ext.Msg.show({
        minWidth: 400,
        fn: function () {
          Ext.MessageBox.hide();
        },
        buttons: Ext.MessageBox.OK,
        title: "Error",
        msg: "Es ist ein Fehler beim Laden der Beschreibung aufgetreten."
      });
    },
    success: function (response, req) {
      var responseData = new Array();
      try {
        responseData = Ext.decode(response.responseText);
        waitDesc.hide();
        if (responseData.success == true) //Modulnummer wurde gefunden :)
        {
          Ext.Msg.show({
            minWidth: 600,
            fn: function () {
              Ext.MessageBox.hide();
            },
            buttons: Ext.MessageBox.OK,
            title: responseData['nrmni'] + " - " + responseData['title'],
            msg: responseData['html']
          });
        }
        else //Modulnummer wurde nicht gefunden :(
        {
          Ext.Msg.show({
            minWidth: 250,
            fn: function () {
              Ext.MessageBox.hide();
            },
            buttons: Ext.MessageBox.OK,
            title: responseData['nrmni'],
            msg: "Keine Daten gefunden!"
          });
        }
      }
      catch(e){
        waitDesc.hide();
        Ext.Msg.show({
          minWidth: 250,
          fn: function () {
            Ext.MessageBox.hide();
          },
          buttons: Ext.MessageBox.OK,
          title: responseData['nrmni'],
          msg: "Keine Daten gefunden!"
        });
      }

    }
  });
}

function zeigeTermine(rooms) {
  if (Ext.ComponentMgr.get('sporadicPanel').collapsed) Ext.ComponentMgr.get('sporadicPanel').expand();

  var counterall = 0;
  var allrooms = Ext.ComponentMgr.get('sporadicPanel').body.select("p[id]");
  for (var index in allrooms.elements)
  if (!Ext.isFunction(allrooms.elements[index])) {
    if (allrooms.elements[index].style != null) {
      allrooms.elements[index].style.display = "none";
      counterall++;
    }
  }

  rooms = rooms.replace(/<[^>]*>/g, "").replace(/[\n\r]/g, '').replace(/ +/g, ' ').replace(/^\s+/g, '').replace(/\s+$/g, '').split(",");
  var counter = 0;
  for (var i = 0; i < rooms.length; i++) {
    var room = rooms[i].replace(/[\n\r]/g, '').replace(/ +/g, ' ').replace(/^\s+/g, '').replace(/\s+$/g, '');
    var pos = room.search(/\s/);
    if (pos != -1) room = room.substring(0, pos);
    var selectedroomevents = Ext.ComponentMgr.get('sporadicPanel').body.select("p[id^=" + room + "_]");
    for (var index in selectedroomevents.elements) {
      if (!Ext.isFunction(selectedroomevents.elements[index])) {
        if (selectedroomevents.elements[index].style != null) {
          selectedroomevents.elements[index].style.display = "block";
          counter++;
        }
      }
    }
  }

  if (counter != 0) var tmp = Ext.ComponentMgr.get('sporadicPanel').setTitle('Einzel Termine - ' + room + ' (' + counter + ')');
}