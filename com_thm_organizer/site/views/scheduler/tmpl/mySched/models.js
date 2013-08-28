/*global Ext, MySched, MySchedLanguage, blocktotime, _C, getTeacherSurnameWithCutFirstName,
 getBlocksBetweenTimes, convertEnglishDateStringToDateObject, convertGermanDateStringToDateObject, getCurrentMoFrDate,
 displayDelta, isset, LectureModel, PoolModel, SubjectModel, TeacherModel,
 RoomModel, weekdayEtoD, getMonday, getSchedGrid, ScheduleModel, SchedJsonReader, exit */
/*jshint strict: false */
/**
 * Models von MySched
 * @author thorsten
 */
/**
 * Model als Grundform
 * @param {Object} id ID des Models
 * @param {Object} d DatenObjekt des Models
 */
Ext.define('MySched.Model',
{
    extend: 'Ext.util.Observable',

    constructor: function (id, d)
    {
        var data, responsible, object1, object2;

        this.id = id;
        this.data = {};
        this.eventList = new MySched.Collection();
        this.responsible = null;
        this.object1 = null;
        this.object2 = null;
        // WICHTIG!! Tiefe Kopie erzeugen, da sonst nur Referenzen kopiert werden.
        if (Ext.type(d) === 'object' || Ext.type(d) === 'array')
        {
            Ext.apply(this.data, d);
        }
        else
        {
            this.data = d;
        }
    },
    getId: function ()
    {
        return this.id;
    },
    getData: function (addData)
    {
        if (Ext.type(addData) !== 'object')
        {
            return this.data;
        }
        return Ext.applyIf(addData, this.data);
    },
    setParent: function (p)
    {
        this.parent = p;
    },
    getParent: function ()
    {
        return this.parent;
    },
    asArray: function ()
    {
        var ret = [];
        var d = this.data;
        if (d.asArray)
        {
            d = d.asArray();
        }
        Ext.each(d, function (e)
        {
            if (Ext.isEmpty(e))
            {
                return;
            }
            if (e.asArray)
            {
                e = e.asArray();
            }
            this[this.length] = e;
        }, ret);
        if (ret.length === 1)
        {
            return ret[0];
        }
        return ret;
    },
    exportData: function (type, pers)
    {
        var d = [];
        if (pers === "personal")
        {
            d = this.asPersArray();
        }
        else
        {
            if(type === "jsonpdf")
            {
                d = this.asArrayForPDF();
            }
            else
            {
                d = this.asArray();
            }
        }

        switch (type)
        {
        case 'arr':
        case 'array':
            return d;
        case 'xml':
            alert(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_XML_NOT_IMPLEMENTED);
            exit();
            break;
        default:
        case 'json':
            var returnValue = Ext.encode(d);
            return returnValue;
        }
    },
    exportAllData: function ()
    {
        var d = [];
        d[0] = {};
        d[0].htmlView = this.htmlView;
        d[0].lessons = this.asArray();
        d[0].visibleLessons = this.visibleLessons;
        d[0].events = this.visibleEvents;
        d[0].session = {};
        d[0].session.sdate = MySched.session.begin;
        d[0].session.edate = MySched.session.end;
        d[0].session.semesterID = MySched.class_semester_id;

        return Ext.encode(d);
    }
});

/**
 * Model zur Darstellung eines Stundenplans
 * @author thorsten
 */
Ext.define('ScheduleModel',
{
    extend: 'MySched.Model',

    constructor: function (id, title, config)
    {
        var blockCache, status;
        this.blockCache = null;
        this.title = title || id;
        this.status = "saved";
        this.id = id;
        this.title = title;
        this.visibleLessons = [];
        this.visibleEvents = [];
        this.superclass.constructor.call(this, id, new MySched.Collection());
        if (config && config.type && config.value)
        {
            this.init(config.type, config.value);
        }
        this.addEvents(
        {
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
    init: function (type, value, semesterID)
    {
        if (type === "delta")
        {
            this.data = MySched.delta.data;
        }
        else if (type === "respChanges")
        {
            this.data = MySched.responsibleChanges.data;
        }
        else
        {
            var valuearr = value.split(";");
            for (var i = 0; i < valuearr.length; i++)
            {
                var datatemp = MySched.Base.getLectures(type, valuearr[i]);
                if (datatemp.length > 0)
                {
                    this.data = datatemp;
                }

                this.eventList.addAll(MySched.eventlist.getEvents(type, value));
            }
        }

        this.semesterID = semesterID;
        this.changed = false;
        this.type = type;
        this.key = value;
        this.gpuntisID = MySched.Mapping[type].map[value].gpuntisID;

        return this;
    },
    addLecture: function (l)
    {
        if (this.fireEvent("beforeLectureAdd", l) === false)
        {
            return;
        }
        // Fuegt die lecture hinzu
        this.data.add(l.id, l);

        // blockCache wird dadurch unkonsistent
        this.blockCache = null;
        this.markChanged();

        this.fireEvent("lectureAdd", l);
    },
    clear: function ()
    {
        if (this.fireEvent("beforeClear", this) === false)
        {
            return this.data.clear();
        }
        this.blockCache = null;
        this.markChanged();
        this.fireEvent("clear", this);
    },
    removeLecture: function (l)
    {
        if (this.fireEvent("beforeLectureRemove", l) === false)
        {
            return;
        }
 
        if (this.blockCache && Ext.type(l) === 'object')
        {
            this.blockCache[l.getWeekDay()][l.getBlock() - 1]--;
        }
 
        if (Ext.type(l) === 'object')
        {
            this.data.removeAtKey(l.getId());
        }
        else
        {
            this.data.removeAtKey(l);
        }
 
        this.markChanged();
        this.fireEvent("lectureRemove", l);
    },
    /**
     * Gibt die lecture mit der id zurueck
     * @param {Object} id
     */
    getLecture: function (id)
    {
        if (id.match('##'))
        {
            id = id.split('##')[1];
        }
        if (MySched.selectedSchedule.type === "delta")
        {
            return MySched.delta.data.get(id);
        }
        var Plesson = this.data.get(id);
        if (Plesson !== null && Plesson.data !== null && Plesson.data.type === "personal")
        {
            return MySched.Schedule.data.get(id);
        }
        return this.data.get(id);
    },
    isEmpty: function ()
    {
        return this.data.isEmpty();
    },
    /**
     * Gibt nur bestimmte Lectures zurueck
     * @param {Object} type
     * @param {Object} value
     * @return {MySched.Collection}
     */
    getLectures: function (type, value)
    {
        if (Ext.isEmpty(type) && Ext.isEmpty(value))
        {
            return this.data.items;
        }
        return this.data.filterBy(function (o, k)
        {
            if (o.has(type, value))
            {
                return true;
            }
            return false;
        }, this);
    },
    getGridData: function ()
    {
        // 0-5 => Blocke am Tag
        var ret = [{},{},{},{},{},{}];

        // Muss fuer Grid festes Format haben
        // Sporatisch, nicht regelmaessige Veranstaltungen
        var sp = [];
        var wpMO = null;
        var cd = Ext.ComponentMgr.get('menuedatepicker');
        var wp = null;
        this.visibleLessons = [];
        this.visibleEvents = [];

        wp = Ext.Date.clone(cd.value);

        wpMO = getMonday(wp);

        var begin = MySched.session.begin.split("-");
        begin = new Date(begin[0], begin[1] - 1, begin[2]);

        if (wp < begin && cd.menu === null)
        {
            Ext.MessageBox.show(
            {
                title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER_NOT_STARTED,
                cls: "mysched_semesterbegin",
                buttons: Ext.MessageBox.YESNO,
                msg: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER_JUMP_TO_START,
                width: 400,
                modal: true,
                closable: false,
                fn: function (btn)
                {
                    if (btn === "yes")
                    {
                        var cd = Ext.ComponentMgr.get('menuedatepicker');
                        var begindate = MySched.session.begin.split("-");
                        var inidate = new Date(begindate[0], begindate[1] - 1, begindate[2]);

                        if (typeof cd.menu === "undefined")
                        {
                            cd.initialConfig.value = inidate;
                        }
                        else
                        {
                            cd.menu.picker.value = inidate;
                            cd.menu.picker.activeDate = inidate;
                        }

                        cd.setValue(MySched.session.begin);
                        if (typeof cd.menu !== "undefined")
                        {
                            cd.menu.picker.update();
                        }
                    }
                    Ext.MessageBox.hide();
                }
            });
        }
 
        var eventlist = MySched.eventlist;
        var events = eventlist;

        //sporadische Termine hinzufügen
        this.eventList.eachKey(function (k, v)
        {
            var eventData = v.data;
            var eventStartDate = eventData.startdate;
            var eventEndDate = eventData.enddate;
            var eventStartTime = eventData.starttime;
            var eventEndTime = eventData.endtime;
            var currMOFR = getCurrentMoFrDate();
            var eventStartDateInCurrentWeek = null;
            var eventEndDateInCurrentWeek = null;

            eventStartDate = convertGermanDateStringToDateObject(eventStartDate);
            eventEndDate = convertGermanDateStringToDateObject(eventEndDate);

            // Event war vor der aktuellen Woche
            if(eventStartDate < currMOFR.monday && eventEndDate < currMOFR.monday)
            {
                return true;
            }

            // Event ist nach der aktuellen Woche
            if(eventStartDate > currMOFR.friday && eventEndDate > currMOFR.friday)
            {
                return true;
            }

            // Event geht über die ganze Woche
            if(eventStartDate <= currMOFR.monday && eventEndDate >= currMOFR.friday)
            {
                eventStartDateInCurrentWeek = currMOFR.monday;
                eventEndDateInCurrentWeek = currMOFR.friday;
            } // Event ist nur genau in dieser Woche
            else if(currMOFR.monday <= eventStartDate && currMOFR.friday >= eventEndDate)
            {
                eventStartDateInCurrentWeek = eventStartDate;
                eventEndDateInCurrentWeek = eventEndDate;
            } // Event beginnt vor der Woche und endet aber in dieser Woche
            else if(eventStartDate <= currMOFR.monday && eventEndDate <= currMOFR.friday)
            {
                eventStartDateInCurrentWeek = currMOFR.monday;
                eventEndDateInCurrentWeek = eventEndDate;
            } // Event beginnt in dieser Woche und endet aber nicht in dieser Woche
            else if(eventStartDate >= currMOFR.monday && eventEndDate >= currMOFR.friday)
            {
                eventStartDateInCurrentWeek = eventStartDate;
                eventEndDateInCurrentWeek = currMOFR.friday;
            }

            // Beginn und/oder Ende Datum nicht gesetzt
            if(eventStartDateInCurrentWeek === null || eventEndDateInCurrentWeek === null)
            {
                return true;
            }

            // Beginn ist nach dem Ende Datum
            if(eventStartDateInCurrentWeek > eventEndDateInCurrentWeek)
            {
                return true;
            }

            while(eventStartDateInCurrentWeek <= eventEndDateInCurrentWeek)
            {
                // Wochentag holen
                var dow = Ext.Date.format(eventStartDateInCurrentWeek, "l");
                dow = dow.toLowerCase();

                // Block holen
                var eventBlocks = getBlocksBetweenTimes(eventStartTime, eventEndTime, eventStartDateInCurrentWeek, eventEndDateInCurrentWeek);

                for(var blockIndex = 0; blockIndex < eventBlocks.length; blockIndex++)
                {
                    var eventBlock = eventBlocks[blockIndex];
                    if (!ret[eventBlock][dow])
                    {
                        ret[eventBlock][dow] = [];
                    }

                    ret[eventBlock][dow].push(v.getEventView(this.type));
                }

                eventStartDateInCurrentWeek.setDate(eventStartDateInCurrentWeek.getDate() + 1);
            }
        }, this);

        //zyklische Termine hinzufügen
        this.data.eachKey(function (k, v)
        {
            var calendarDates = v.data.calendar;
            for (var dateIndex in calendarDates)
            {
                if (calendarDates.hasOwnProperty(dateIndex))
                {
                    var dateObject = convertEnglishDateStringToDateObject(dateIndex);
                    var currMOFR = getCurrentMoFrDate();
                    if (dateObject >= currMOFR.monday && dateObject <= currMOFR.friday)
                    {
                        var dow = Ext.Date.format(dateObject, "l");
                        dow = dow.toLowerCase();

                        var date = calendarDates[dateIndex], block;
                        for (var blockIndex in date)
                        {
                            if (date.hasOwnProperty(blockIndex))
                            {
                                block = date[blockIndex];
                                var displayLesson = false;
                                if (Ext.isObject(block.lessonData))
                                {
                                if(Ext.isString(block.lessonData.delta))
                                {
                                    if(block.lessonData.delta === "removed")
                                    {
                                        if(displayDelta() === false)
                                        {
                                            continue;
                                        }
                                    }
                                }
                                if(this.type === "room")
                                {
                                    for (var roomIndex in block.lessonData)
                                    {
                                        if (block.lessonData.hasOwnProperty(roomIndex) && roomIndex === this.key && block.lessonData[roomIndex] !== "removed")
                                        {
                                            displayLesson = true;
                                        }
                                    }
                                }
                                else
                                {
                                    displayLesson = true;
                                }

                                if(displayLesson === true)
                                {
                                    block = blockIndex - 1;

                                    if (!ret[block][dow])
                                    {
                                        ret[block][dow] = [];
                                    }

                                    ret[block][dow].push(v.getCellView(this, block, dow));
                                    this.visibleLessons.push(v.data);
                                }
                            }
                            }
                        }
                    }
                }
            }
        }, this);

        this.htmlView = Ext.clone(ret);

        return ret;
    },
    load: function (url, type, cb, scope, username, tmi)
    {
        var scheduleTask = 'UserSchedule.load';

        var defaultParams = {
            username: username,
            jsid: MySched.SessionId,
            semesterID: MySched.modules_semester_id,
            scheduletask: scheduleTask
        };

        this.reader = new SchedJsonReader();

        this.proxy = Ext.create('Ext.data.proxy.Rest',
        {
            url: url,
            extraParams: defaultParams,
            reader: this.reader
        });

        this.proxy.read(new Ext.data.Operation(
        {
            action: 'read'
        }), cb, scope);
    },
    /**
     * Ueberprueft ob existierende Veranstaltungen noch existieren
     * @param {Object} against Summe aller existierender Veranstaltungen
     */
    checkLectureVersion: function (against)
    {
        var ret = {};
        this.data.each(function (v)
        {
            v.data.css = "";
        });
        var newdatas = this.data.clone();
        var funcsort = function numsort(a, b)
        {
            if (a.data.subject.toString() > b.data.subject.toString())
            {
                return 1; // a steht vor b
            }
            else if (a.data.subject.toString() < b.data.subject.toString())
            {
                return -1; // b steht vor a
            }
            return 0; // nix passiert
        };

        newdatas.sort("ASC", funcsort);
        var keystoremove = [];
        ret.data = against.data;
        against.data.sort("ASC", funcsort);
        ret.showMsg = false;
        ret.ret = "";
        for (var i = 0; i < this.data.length; i++)
        {
            if (against.data.containsKey(this.data.items[i].id) &&
                (against.data.get(this.data.items[i].id).module.keys.toString() !== this.data.items[i].module.keys.toString() ||
                against.data.get(this.data.items[i].id).teacher.keys.toString() !== this.data.items[i].teacher.keys.toString() ||
                against.data.get(this.data.items[i].id).room.keys.toString() !== this.data.items[i].room.keys.toString()))
            {
                // Es hat sich etwas geändert
                newdatas.removeAtKey(this.data.items[i].id);
                newdatas.add(this.data.items[i].id, against.data.get(this.data.items[i].id));
                newdatas.get(this.data.items[i].id).data.css = "movedto";
            }
            else
            {
                // Veranstaltung existiert nicht mehr :(
                if (this.data.items[i].data.type !== "personal" || (this.data.items[i].data.responsible !== "mySchedule" && this.data.items[i].data.type === "personal"))
                {
                    keystoremove.push(this.data.items[i].id);
                    for (var n = 0; n < against.data.length; n++)
                    {
                        if (against.data.items[n].data.subject.toString() > this.data.items[i].data.subject.toString())
                        {
                            break;
                        }
                        if (against.data.items[n].data.subject.toString() === this.data.items[i].data.subject.toString() && !newdatas.containsKey(against.data.items[n].data.key.toLowerCase()))
                        {
                            newdatas.add(against.data.items[n].data.key, against.data.items[n]);
                            newdatas.get(against.data.items[n].data.key).data.css = "movedto";
                        }
                    }
                }
            }
        }

        for (i = 0; i < keystoremove.length; i++)
        {
            newdatas.removeAtKey(keystoremove[i]);
        }

        this.data.clear();
        this.data.addAll(newdatas.items);

        MySched.Authorize.saveIfAuth(false);

        MySched.selectedSchedule.eventsloaded = null;
        MySched.selectedSchedule.refreshView();

        var func = function ()
        {
            MySched.SelectionManager.stopSelection();
            MySched.SelectionManager.startSelection();
        };
        Ext.defer(func, 50);
    },
    /**
     * Prueft verschiedene Vorbedinungen
     * @param {Object} o
     * @param {Object} arg
     */
    preParseLectures: function (o, arg)
    {
        // Funktion nach dem Auth ausfuehren und loeschen -> SPeichern geklickt
        if (MySched.Authorize.afterAuthCallback)
        {
            MySched.Authorize.afterAuthCallback();
            MySched.Authorize.afterAuthCallback = null;
        }
        return this.parseLectures(o);
    },
    loadsavedLectures: function (o, arg)
    {
        if (o.resultSet !== null)
        {
            var r = o.resultSet.records, e;

            //this.data.clear();
            for (var i = 0, len = r.length; i < len; i++)
            {
                e = r[i];
                // Filtert Veranstaltungen ohne Datum aus
                if (Ext.isEmpty(e.data.dow))
                {
                    continue;
                }
                this.data.add(e.data.key, e);
            }
        }

        if (!MySched.SessionId)
        {
            var tree = MySched.Tree.tree;

            var treeRoot = tree.getRootNode();

            var semid = treeRoot.firstChild.data.id;

            semid = semid.split(".");

            semid = semid[0];

            var deltaid = semid + ".1.delta";

            var deltaSched = new ScheduleModel(deltaid, MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_CENTRAL).init("delta", deltaid);
            deltaSched.show();
            MySched.layout.viewport.doLayout();
            MySched.selectedSchedule.responsible = "delta";
 
            MySched.Schedule.status = "saved";
        }

        return;
    },
    /**
     * Callback zum parsen der XML Datei in Lecture
     * @param {Object} o
     */
    parseLectures: function (o)
    {
        this.fireEvent('load', this);
        var r = o.resultSet.records;
        var l, key;
        for (var i = 0, len = r.length; i < len; i++)
        {
            var e = r[i];
            this.data.add(e.id, e);
        }

        if (MySched.layout.tabpanel.getComponent('mySchedule'))
        {
            var func = function ()
            {
                MySched.SelectionManager.stopSelection();
                MySched.SelectionManager.startSelection();
            };
            Ext.defer(func, 50);
            this.eventsloaded = null;
            this.refreshView();
        }
        else
        {
            var grid = MySched.Schedule.show(true);
            Ext.apply(grid,
            {
                closable: false,
                tabTip: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE,
                iconCls: 'myScheduleIcon'
            });
            MySched.layout.createTab('mySchedule', MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE, grid, "mySchedule");
        }
        // Buttons aktivieren wenn nicht leer
        if (!MySched.Schedule.isEmpty() && MySched.libraryFPDFIsInstalled)
        {
            Ext.ComponentMgr.get('btnEmpty').enable();
            Ext.ComponentMgr.get('btnPdf').enable();
            if (_C('enableSubscribing'))
            {
                Ext.ComponentMgr.get('btnSub').enable();
            }
        }

        // tab 'Mein Stundenplan' wird DropArea
        var tabID = MySched.layout.tabpanel.getComponent('mySchedule')
            .tab.el.dom;
        var dropTarget = new Ext.dd.DropTarget(tabID, MySched.Base.getDropConfig());
        this.blockCache = null;
        this.markUnchanged();
    },
    /**
     * Callback zum parsen der XML Datei in Lecture
     * @param {Object} o
     * @param {Object} arg
     */
    parseLecturesdiff: function (o, arg)
    {
        // Fuegt dem Uebergabeparameter das Result hinzu
        Ext.applyIf(arg.params,
        {
            result: o
        });
        var r = o.records;

        for (var i = 0, len = r.length; i < len; i++)
        {
            var e = r[i];
            // Filtert Veranstaltungen ohne Datum aus
            if (Ext.isEmpty(e.data.subject) || Ext.isEmpty(e.data.dow))
            {
                continue;
            }
            MySched.selectedSchedule.data.add(e.data.key, e);
        }
        if (arg.callback)
        {
            arg.callback.createDelegate(arg.scope)(arg.params);
        }
    },
    show: function (ret, closeable)
    {
        if (closeable !== false)
        {
            closeable = true;
        }
        this.grid = getSchedGrid(this.getGridData());
        this.grid.ScheduleModel = this;
        if (ret)
        {
            return this.grid;
        }
        var name = this.title.replace(/\s*\/\s*/g, ' ');
        MySched.layout.createTab(this.getId(), name, this.grid, this.type, closeable);

        if (this.type === "delta")
        {
            MySched.selectedSchedule.data = MySched.delta.data;
        }
        else if (MySched.Authorize.role !== "user" || this.getId() !== "mySchedule")
        {
            this.dragzone = new Ext.dd.DragZone(this.getId(),
            {
                containerScroll: true,
                ddGroup: 'lecture'
            });
        }
    },
    refreshView: function ()
    {
        if (!this.grid)
        {
            return this.show();
        }
        if (this.type !== "delta")
        {
            this.eventList.addAll(MySched.eventlist.getEvents(this.type, this.key));
        }
        this.grid.loadData(this.getGridData());
        var func = function ()
        {
            MySched.SelectionManager.stopSelection();
            MySched.SelectionManager.startSelection();
        };
        Ext.defer(func, 50);
    },
    getBlockStatus: function (wd, block)
    {
        var weekdays = {
            1: "monday",
            2: "tuesday",
            3: "wednesday",
            4: "thursday",
            5: "friday",
            6: "saturday",
            7: "sunday"
        };

        // Numerischer Index erlaubt
        if (weekdays[wd])
        {
            wd = weekdays[wd];
        }
        if (this.getBlockCache()[wd] && this.blockCache[wd][block])
        {
            return this.blockCache[wd][block];
        }
        return 0;
    },
    getBlockCache: function (forceGenNew)
    {
        // Generiere den BlockCache neu falls notwendig
        if (forceGenNew || Ext.isEmpty(this.blockCache))
        {
            this.blockCache = {
                monday: [],
                tuesday: [],
                wednesday: [],
                thursday: [],
                friday: [],
                saturday: [],
                sunday: []
            };
            this.data.each(function (l)
            {
                var wd = l.getWeekDay();
                var b = l.getBlock();
                b = b - 1;
 
                var calendarDates = l.data.calendar;
                for (var dateIndex in calendarDates)
                {
                    if (calendarDates.hasOwnProperty(dateIndex))
                    {
                        var dateObject = convertEnglishDateStringToDateObject(dateIndex);
                        var currMOFR = getCurrentMoFrDate();
                        if (dateObject >= currMOFR.monday && dateObject <= currMOFR.friday)
                        {
                            if(calendarDates[dateIndex][l.getBlock()].lessonData.delta !== "removed")
                            {
                                if (!this.blockCache[wd][b])
                                {
                                    this.blockCache[wd][b] = 1;
                                }
                                else
                                {
                                    this.blockCache[wd][b]++;
                                }
                                break;
                            }
                        }
                    }
                }
            }, this);
        }

        return this.blockCache;
    },
    lectureExists: function (l)
    {
        if (l.getId)
        {
            l = l.getId();
        }
        if (l.match('##'))
        {
            l = l.split('##')[1];
        }
        return this.data.containsKey(l);
    },
    markChanged: function ()
    {
        if (this.changed)
        {
            return;
        }
        this.fireEvent("changed", this);
        this.changed = true;
    },
    markUnchanged: function ()
    {
        if (!this.changed)
        {
            return;
        }
        this.changed = false;
    },
    save: function (url, success, scheduletask)
    {
        if (MySched.Authorize.user !== null)
        {
            if (this.fireEvent("beforeSave", this, url) === false)
            {
                return;
            }

            var defaultParams, data;
            if (scheduletask === "UserSchedule.save")
            {
                defaultParams = {
                    jsid: MySched.SessionId,
                    sid: MySched.Base.sid,
                    scheduletask: scheduletask
                };
                data = MySched.Schedule.exportData();
            }
            else
            {
                defaultParams = {
                    jsid: MySched.SessionId,
                    sid: MySched.Base.sid,
                    semesterID: MySched.modules_semester_id,
                    id: this.id,
                    scheduletask: scheduletask
                };
                data = this.exportData("json", "personal");
            }
            var savewait;
            if (success !== false)
            {
                savewait = Ext.MessageBox.wait(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_SAVING, MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_PLEASE_WAIT);
            }
            else
            {
                savewait = null;
            }
 
            Ext.Ajax.request(
            {
                url: url,
                jsonData: data,
                scope: savewait,
                method: 'POST',
                params: defaultParams,
                success: function (resp)
                {
                    if (savewait !== null)
                    {
                        Ext.MessageBox.hide();
                    }
 
                    try
                    {
                        var json = Ext.decode(resp.responseText);
                        if (json.code)
                        {
                            if (json.code !== 1)
                            {
                                Ext.Msg.show(
                                {
                                    title: 'Error',
                                    msg: json.reason,
                                    buttons: Ext.Msg.OK,
                                    minWidth: 400
                                });
                                MySched.Schedule.status = "unsaved";
                                Ext.ComponentMgr.get('btnSave').enable();
                                var tab = MySched.layout.tabpanel.getComponent(MySched.selectedSchedule.id);
                                tab.ScheduleModel.status = "unsaved";
                                tab = Ext.get(MySched.layout.tabpanel.getTabEl(tab)).child('.' + MySched.selectedSchedule.type + 'Icon');
                                if (tab)
                                {
                                    tab.replaceClass('' + MySched.selectedSchedule.type + 'Icon', '' + MySched.selectedSchedule.type + 'IconSave');
                                }
                            }
                            else
                            {
                                MySched.Schedule.status = "saved";
                                Ext.ComponentMgr.get('btnSave').disable();
                            }
                        }
                    }
                    catch (e)
                    {

                    }
                }
            });
            this.fireEvent("save", this, url);
            this.markUnchanged();
        }
        else
        {
            Ext.Msg.show(
            {
                title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR,
                msg: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LOGIN_PLEASE,
                buttons: Ext.Msg.OK,
                minWidth: 400
            });
        }
    },
    asArray: function ()
    {
        var asArrRet = {};
        var d = this.data;
 
        for(var index = 0; index < d.length; index++)
        {
            var lesson = d.items[index];
            asArrRet[lesson.id] = Ext.clone(lesson.data);
            asArrRet[lesson.id].modules = asArrRet[lesson.id].modules.map;
            asArrRet[lesson.id].teachers = asArrRet[lesson.id].teachers.map;
            asArrRet[lesson.id].subjects = asArrRet[lesson.id].subjects.map;
        }
 
        return asArrRet;
    },
    asArrayForPDF: function ()
    {
        var asArrRet = [];
        var d = this.data;
        if (d.asArray)
        {
            d = d.asArray();
        }
 
        var wpMO = null;
        var cd = Ext.ComponentMgr.get('menuedatepicker');
        var wp = null;
 
        wp = Ext.Date.clone(cd.value);

        wpMO = getMonday(wp);
 
        Ext.each(d, function (v)
        {
            var calendarDates = v.data.calendar;
            for (var dateIndex in calendarDates)
            {
                if (calendarDates.hasOwnProperty(dateIndex))
                {
                    var dateObject = convertEnglishDateStringToDateObject(dateIndex);
                    var wpFR = Ext.Date.clone(wpMO);
                    wpFR.setDate(wpFR.getDate() + 6);
                    if (dateObject >= wpMO && dateObject <= wpFR)
                    {
                        var dow = Ext.Date.format(dateObject, "l");
                        var dowNR = Ext.Date.format(dateObject, "N");
                        dow = dow.toLowerCase();

                        var date = calendarDates[dateIndex];
                        for (var blockIndex in date)
                        {
                            if (date.hasOwnProperty(blockIndex))
                            {
                                var block = date[blockIndex];
                                if (Ext.isObject(block.lessonData))
                                {
                                    if(date[blockIndex].lessonData.delta && date[blockIndex].lessonData.delta === "removed")
                                    {
                                        continue;
                                    }

                                    block = blockIndex - 1;

                                    asArrRet[asArrRet.length] = {};
                                    asArrRet[asArrRet.length - 1].cell = v.getCellView(this, blockIndex, dowNR);
                                    asArrRet[asArrRet.length - 1].block = Ext.clone(blockIndex);
                                    asArrRet[asArrRet.length - 1].dow = Ext.clone(dowNR);
                                }
                            }
                        }
                    }
                }
            }
        });
 
        //if (this.asArrRet.length === 1) return this.asArrRet[0];
        return asArrRet;
    },
    getLessonKeys: function ()
    {
        return this.data.keys;
    },
    asPersArray: function ()
    {
        this.asArrRet = [];
        var d = this.data;
        if (d.asArray)
        {
            d = d.asArray();
        }
        Ext.each(d, function (e)
        {
            if (Ext.isEmpty(e))
            {
                return;
            }
            if (e.data.type === "personal")
            {
                var cell = e.getCellView(this);
                if (e.asArray)
                {
                    e = e.asArray();
                }
                e.cell = cell;
                this.asArrRet[this.asArrRet.length] = e;
            }
        }, this);
        //if (this.asArrRet.length === 1) return this.asArrRet[0];
        return this.asArrRet;
    }
});

/**
 * LectureModel
 * @param {Object} lecture
 */
Ext.define('LectureModel',
{
    extend: 'MySched.Model',

    constructor: function (id, data, semesterID, plantypeID)
    {
        var teacher, module, room, cellTemplate, infoTemplate;
        var owner = data.owner;
        var stime = data.stime;
        var etime = data.etime;
        var showtime = data.showtime;

        this.superclass.constructor.call(this, id, Ext.clone(data));

        this.data.teachers = new MySched.Collection();
        this.data.teachers.addAll(data.teachers);
        this.data.rooms = new MySched.Collection();
        this.data.rooms.addAll(data.rooms);
        this.data.subjects = new MySched.Collection();
        this.data.subjects.addAll(data.subjects);
        this.data.modules = new MySched.Collection();
        this.data.modules.addAll(data.modules);
        this.data.rooms = new MySched.Collection();

        this.semesterID = semesterID;
        this.plantypeID = plantypeID;

        if (this.data.moduleID === MySched.searchModuleID && !Ext.isEmpty(MySched.searchModuleID))
        {
            this.data.css = this.data.css + " searchSubject";
        }

        //New CellStyle
        this.setCellTemplate();

        var infoTemplateString = '<div>' + '<small><span class="def">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOM + ':</span> {roomName}<br/>' + '<span class="def">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER + ':</span><big> {teacherName}</big><br/>' + '<span class="def">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER + ':</span> <br/>{moduleName}<br/>';
        infoTemplateString += '</small></div>';

        this.infoTemplate = new Ext.Template(infoTemplateString);

        this.sporadicTemplate = new Ext.Template('<div id="{parentId}##{id}" block="{lessonBlock}" dow="{lessonDow}" class="{css} sporadicBox lectureBox">' + '<b>{desc}</b> <small><i>({desc:defaultValue("' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NO_DESCRIPTION + '")})</i> ' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOM + ': {room_short} - ' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER + ': {teacher_name} - {module_short}</small>' + '</div>');
    },
    getDetailData: function (d)
    {
        return Ext.apply(this.getData(d),
        {
            'lessonTitle': this.getLessonTitle(d),
            'teacherName': this.getTeacherNames(d),
            'moduleName': this.getModuleName(d),
            'roomName': this.getRoomName(d),
            'weekday': weekdayEtoD(this.getWeekDay()),
            'block': this.getBlock(),
            'description': this.getDescription(),
            'changesAll': this.getChanges(d),
            'statusIcons': this.getStatus(d),
            'topIcon': this.getTopIcon(d),
            'comment': this.getComment(d),
            'deltaStatus': this.getDeltaStatus(d),
            'curriculumColor': this.getCurriculumColor(d),
            'lessonEvents': this.getEvents(d)
        });
    },
    getCurriculumColor: function (d)
    {
        if(MySched.selectedSchedule === null)
        {
            return "";
        }
        var curriculumColors = MySched.startup.CurriculumColors;

        if(curriculumColors.length < 1)
        {
            return "";
        }

        var key = MySched.selectedSchedule.key;
        var moduleName = MySched.Mapping.getModuleFullName(key);
        var moduleDegree = MySched.Mapping.getModuleParent(key);
        var degreeName = MySched.Mapping.getDegreeName(moduleDegree);

        for(var index = 0; index < curriculumColors.length; index++)
        {
            var curriculumColor = curriculumColors[index];
            if(curriculumColor.semesterName === moduleName && curriculumColor.organizerMajorName === degreeName)
            {
                return "background-color: #" + curriculumColor.hexColorCode;
            }
        }
        return "";
    },
    getDeltaStatus: function (d)
    {
        var currentMoFrDate = getCurrentMoFrDate();
        var returnValue = "";
        if(d.showDelta === true)
        {
            for(var dateIndex in this.data.calendar)
            {
                if (this.data.calendar.hasOwnProperty(dateIndex))
                {
                    var dateObject = convertEnglishDateStringToDateObject(dateIndex);
                    if(dateObject >= currentMoFrDate.monday && dateObject <= currentMoFrDate.friday && this.data.calendar[dateIndex][this.data.block].lessonData.delta)
                    {
                        returnValue = "delta" + this.data.calendar[dateIndex][this.data.block].lessonData.delta;
                        return returnValue;
                    }
                }
            }
        }
        return returnValue;
    },
    getLessonTitle: function (d)
    {
        var firstSubject = this.data.subjects.keys[0];
        var lessonTitle = MySched.Mapping.getSubjectName(firstSubject);
        return lessonTitle;
    },
    getComment: function (d)
    {
        if (!Ext.isEmpty(d.comment) && Ext.isString(d.comment))
        {
            return "<br/>(" + d.comment + ")";
        }
        else
        {
            return "";
        }
    },
    getEvents: function (d)
    {
        var ret = "";
        ret = MySched.eventlist.getEventsForLecture(this, d.block, d.dow);
        return ret;
    },
    getTopIcon: function (d)
    {
        if (isset(this.data.lessonChanges) && this.data.lessonChanges.status === "new")
        {
            return '<div data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_IS_NEW + '" class="top_icon_image">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NEW + '</div>';
        }

        if (isset(this.data.periodChanges))
        {
            if (this.data.periodChanges.status === "new")
            {
                return '<div data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_IS_NEW + '" class="top_icon_image">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NEW + '</div>';
            }
            else if (this.data.periodChanges.status === "moved")
            {
                return '<div data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_MOVED_DESC + '" class="top_icon_image">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_MOVED + '</div>';
            }
        }

        if (this.data.css === "mysched_proposal")
        {
            return '<div data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_PROPOSAL_DESC + '" class="top_icon_image">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_PROPOSAL + '</div><br/>';
        }

        return "";
    },
    getStatus: function (d)
    {
        var ret = '<div class="status_icons"> ';

        if (this.data.ecollaborationLink != null)
        {
            ret += '<a target="_blank" href="' + this.data.ecollaborationLink + '"><img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MOODLE_CALL + '" class="status_icons_ecollabLink" src="' + MySched.mainPath + 'images/collab.png" width="12" heigth="12"/></a>';
        }

        if (MySched.Authorize.user != null && MySched.Authorize.user != "" && typeof d.parentId != "undefined")
        {
            var parentIDArr = d.parentId.split(".");
            parentIDArr = parentIDArr[(parentIDArr.length - 1)];
            if (parentIDArr != 'delta')
            {
                if (d.parentId == 'mySchedule')
                {
                    ret += '<img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE_LESSON_REMOVE + '" class="status_icons_add" src="' + MySched.mainPath + '/images/delete.png" width="12" heigth="12"/>';
                }
                else if (d.parentId != 'mySchedule' && MySched.Schedule.lectureExists(this))
                {
                    ret += '<img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE_LESSON_REMOVE + '" class="status_icons_add" src="' + MySched.mainPath + '/images/delete.png" width="12" heigth="12"/>';
                }
                else
                {
                    ret += '<img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE_LESSON_ADD + '" class="status_icons_add" src="' + MySched.mainPath + '/images/add.png" width="12" heigth="12"/>';
                }
            }
        }

        if ((d.owner === MySched.Authorize.user || (MySched.Authorize.user === MySched.modules_semester_author && d.type === "personal")) && MySched.Authorize.user != null && MySched.Authorize.user != "")
        {
            ret += '<img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_CHANGE + '" class="status_icons_edit" src="' + MySched.mainPath + 'images/icon-edit.png" width="12" heigth="12"/>';
            ret += '<img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_DELETE + '" class="status_icons_delete" src="' + MySched.mainPath + 'images/icon-delete.png" width="12" heigth="12"/>';
        }

        return ret + ' </div>';
    },
    getChanges: function (lec)
    {
        var r = "", t = "", c = "", l, temp;

        if (lec && lec.changes)
        {
            if (lec.changes.rooms)
            {
                var rooms = lec.changes.rooms;
                r += "<span>" + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOMS + ":<br/>";
                for (var room in rooms)
                {
                    if (rooms.hasOwnProperty(room) && room !== "")
                    {
                        temp = MySched.Mapping.getObject("room", room);
                        if (!temp)
                        {
                            r += '<small class="' + rooms[room] + '"> ' + room + ' </small>, ';
                        }
                        else
                        {
                            r += '<small class="' + rooms[room] + '"> ' + temp.name + ' </small>, ';
                        }
                        r += "<br/>";
                    }
                }
                if (r !== "")
                {
                    l = r.length - 2;
                    r = r.substr(0, l);
                }
                r += "</span><br/>";
            }
            if (lec.changes.teachers)
            {
                var teachers = lec.changes.teachers;
                t += "<span>" + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHERS + ":<br/>";
                for (var teacher in teachers)
                {
                    if (teachers.hasOwnProperty(teacher) && teacher !== "")
                    {
                        temp = MySched.Mapping.getObject("teacher", teacher);
                        if (!temp)
                        {
                            t += '<small class="' + teachers[teacher] + '"> ' + teacher + ' </small>, ';
                        }
                        else
                        {
                            t += '<small class="' + teachers[teacher] + '"> ' + temp.name + ' </small>, ';
                        }
                        t += "<br/>";
                    }
                }
                if (t !== "")
                {
                    l = t.length - 2;
                    t = t.substr(0, l);
                }
                t += "</span><br/>";
            }
            if (lec.changes.moduleses)
            {
                var moduleses = lec.changes.moduleses;
                c += "<span>" + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER + ":<br/>";
                for (var module in moduleses)
                {
                    if (moduleses.hasOwnProperty(module)  && module !== "")
                    {
                        temp = MySched.Mapping.getObject("module", module);
                        if (!temp)
                        {
                            c += '<small class="' + moduleses[module] + '"> ' + module + ' </small>, ';
                        }
                        else
                        {
                            c += '<small class="' + moduleses[module] + '"> ' + temp.department + " - " + temp.name + ' </small>, ';
                        }
                        c += "<br/>";
                    }
                }
                if (c !== "")
                {
                    l = c.length - 2;
                    c = c.substr(0, l);
                }
                c += "</span><br/>";
            }
        }
        return r + t + c;
    },
    loadTeacher: function (arr)
    {
        if (arr)
        {
            var myteacher = arr.split(" ");
            Ext.each(myteacher, function (e)
            {
                var nteacher = new TeacherModel(e);
                this.teacher.add(nteacher);
            }, this);
        }
    },
    loadRoom: function (arr)
    {
        if (arr)
        {
            var myroom = arr.split(" ");
            Ext.each(myroom, function (e) { this.room.add(new RoomModel(e)); }, this);
        }
    },
    loadSubject: function (arr)
    {
        if (arr)
        {
            var mySubject = arr.split(" ");
            Ext.each(mySubject, function (e) { this.subject.add(new SubjectModel(e)); }, this);
        }
    },
    loadClas: function (arr)
    {
        if (arr)
        {
            var mymodule = arr.split(" ");
            Ext.each(mymodule, function (e) { this.module.add(new PoolModel(e)); }, this);
        }
    },
    getData: function (addData)
    {
        if (!this.data.name)
        {
            this.data.name = this.getName();
        }
        if (!this.data.desc)
        {
            this.data.desc = this.getDesc();
        }
        return LectureModel.superclass.getData.call(this, addData);
    },
    getRoomName: function (d)
    {
        var rooms = this.getRooms(this);
        var ret = [];
        var removed = [];
        var changedStatus = "";

        for (var roomIndex in rooms.map)
        {
            if (rooms.map.hasOwnProperty(roomIndex))
            {
                var roomName = MySched.Mapping.getRoomName(roomIndex);

                if(d.showDelta === true && rooms.map[roomIndex] !== "")
                {
                    changedStatus = "room"+rooms.map[roomIndex];
                }
                else if(rooms.map[roomIndex] !== "" && rooms.map[roomIndex] !== "new")
                {
                    continue;
                }

                var roomNameHTML = '<small roomID="' + roomIndex +  '" class="roomname ' + changedStatus + '">' + roomName + '</small>';
                ret.push(roomNameHTML);
            }
        }

        return ret.join(', ') + " " + removed.join(', ');
    },
    getRooms: function(lesson)
    {
        var roomCollection = new MySched.Collection();
        var currentMoFrDate = getCurrentMoFrDate();
        for(var dateIndex in lesson.data.calendar)
        {
            if (lesson.data.calendar.hasOwnProperty(dateIndex))
            {
                var dateObject = convertEnglishDateStringToDateObject(dateIndex);
                if(dateObject >= currentMoFrDate.monday && dateObject <= currentMoFrDate.friday)
                {
                    roomCollection.addAll(lesson.data.calendar[dateIndex][lesson.data.block].lessonData);
                }
            }
        }

        roomCollection.removeAtKey("delta");
        return roomCollection;
    },
    getTeacherNames: function (d)
    {
        var teachers = this.data.teachers.map;
        var ret = [];
        var removed = [];
        var changedStatus = "";

        for (var teacherIndex in teachers)
        {
            if (teachers.hasOwnProperty(teacherIndex))
            {
                var teacherName = getTeacherSurnameWithCutFirstName(teacherIndex);

                if(d.showDelta === true && teachers[teacherIndex] !== "")
                {
                    changedStatus = "teacher" + teachers[teacherIndex];
                }
                else if(teachers[teacherIndex] !== "" && teachers[teacherIndex] !== "new")
                {
                    continue;
                }

                var teacherNameHTML = '<small teacherID="' + teacherIndex +  '" class="teachername ' +  changedStatus + '">' + teacherName + '</small>';
                ret.push(teacherNameHTML);
            }
        }

        return ret.join(', ') + " " + removed.join(', ');
    },
    getNames: function (col, shortVersion)
    {
        var ret = [];
        col.each(function (e)
        {
            // Abkuerzung anstatt Ausgeschrieben
            var temproom = "";
            if (shortVersion)
            {
                temproom = e.getId();
            }
            else
            {
                temproom = e.getName();
            }
            this.push(temproom);
        }, ret);
        // Bei der kurzen Varianten ohne BLANK
        if (shortVersion)
        {
            return ret.join(',');
        }
        return ret.join(', ');
    },
    getClassFull: function (col)
    {
        var ret = [];
        col.each(function (e)
        {
            // Abkuerzung anstatt Ausgeschrieben
            var temproom = e.getFullName();
            this.push(temproom);
        }, ret);
        // Bei der kurzen Varianten ohne BLANK
        return ret.join(',<br/>');
    },
    getModuleName: function (d)
    {
        var modules = this.data.modules.map;
        var ret = [];
        var removed = [];
        var changedStatus = "";

        for (var moduleIndex in modules)
        {
            if (modules.hasOwnProperty(moduleIndex))
            {
                var moduleName = MySched.Mapping.getModuleName(moduleIndex);

                if(d.showDelta === true)
                {
                    if(modules[moduleIndex] !== "")
                    {
                        changedStatus = "module" + modules[moduleIndex];
                    }
                }
                else
                {
                    if(modules[moduleIndex] !== "" && modules[moduleIndex] !== "new")
                    {
                        continue;
                    }
                }

                var moduleNameHTML = '<small moduleID="' + moduleIndex +  '" class="modulename ' + changedStatus + '">' + moduleName + '</small>';
                ret.push(moduleNameHTML);
            }
        }

        return ret.join(', ') + " " + removed.join(', ');
    },
    getName: function ()
    {
        return MySched.Mapping.getLectureName(this.data.id);
    },
    getDesc: function ()
    {
        return MySched.Mapping.getLectureDescription(this.data.id);
    },
    getTeacher: function ()
    {
        return this.teacher;
    },
    getClas: function ()
    {
        return this.module;
    },
    getRoom: function ()
    {
        return this.room;
    },
    getWeekDay: function ()
    {
        return this.data.dow;
    },
    getBlock: function ()
    {
        return this.data.block;
    },
    getDescription: function ()
    {
        if (!Ext.isEmpty(this.data.description) && Ext.isString(this.data.description))
        {
            return "-" + this.data.description;
        }
        else
        {
            return "";
        }
    },
    setCellTemplate: function (t)
    {
        var time = "";
        var blocktimes = blocktotime(this.data.block);
        if (this.data.showtime === "full")
        {
            if (blocktimes[0] !== this.data.stime || blocktimes[1] !== this.data.etime)
            {
                time = "(" + this.data.stime + "-" + this.data.etime + ")";
            }
        }
        else if (this.data.showtime === "first")
        {
            if (blocktimes[0] !== this.data.stime)
            {
                time = "(ab " + this.data.stime + ")";
            }
        }
        else if (this.data.showtime === "last")
        {
            if (blocktimes[1] !== this.data.etime)
            {
                time = "(bis " + this.data.etime + ")";
            }
        }

        if (Ext.isObject(MySched.selectedSchedule))
        {
            if (!Ext.isString(t))
            {
                t = MySched.selectedSchedule.type;
            }
        }
 
        if (t === "room")
        {
            this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" block="{lessonBlock}" dow="{lessonDow}" class="{css} {deltaStatus} scheduleBox lectureBox">' + '<b class="lecturename">{lessonTitle}{description} {comment}</b><br/>{teacherName} / {moduleName} {lessonEvents}' + time + ' {statusIcons}</div>');
        }
        else if (t === "teacher")
        {
            this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" block="{lessonBlock}" dow="{lessonDow}" class="{css} {deltaStatus} scheduleBox lectureBox">' + '<b class="lecturename">{lessonTitle}{description} {comment}</b><br/>{moduleName} / {roomName} {lessonEvents}' + time + ' {statusIcons}</div>');
        }
        else
        {
            var modulescss = "scheduleBox";
            var lecturecss = "";

            if (isset(this.data.lessonChanges) && this.data.lessonChanges.status === "removed")
            {
                modulescss += " lectureBox_dis";
                lecturecss = "lecturename_dis";
            }

            if (isset(this.data.periodChanges) && this.data.periodChanges.status === "removed")
            {
                modulescss += " lectureBox_dis";
                lecturecss = "lecturename_dis";
            }

            if (lecturecss === "")
            {
                modulescss += " lectureBox";
                lecturecss = "lecturename";
            }

            this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" style="{curriculumColor}" block="{lessonBlock}" dow="{lessonDow}" class="{css} {deltaStatus} ' + modulescss + '">' + '{topIcon}<b class="' + lecturecss + '">{lessonTitle}{description} {comment}</b><br/>{teacherName} / {roomName} {lessonEvents}' + time + ' {statusIcons}</div>');
        }
    },
    setInfoTemplate: function (t)
    {
        this.infoTemplate.set(t, true);
    },
    getCellView: function (relObj, block, dow)
    {
        var showDelta = displayDelta();

        var d = this.getDetailData(
        {
            parentId: relObj.getId(),
            key: this.id,
            showDelta: showDelta,
            block: block,
            dow: dow
        });
        if (relObj.getId() !== 'mySchedule' && MySched.Schedule.lectureExists(this))
        {
            d.css = ' lectureBox_cho';
        }
        var cellView =  this.cellTemplate.apply(d);
 
        if(cellView.contains("MySchedEvent_reserve"))
        {
            cellView = cellView.replace("lectureBox", "lectureBox lectureBox_reserve");
        }
 
        return cellView;
    },
    getSporadicView: function (relObj)
    {
        var d = this.getDetailData({ parentId: relObj.getId() });
        if (relObj.getId() !== 'mySchedule' && MySched.Schedule.lectureExists(this))
        {
            d.css = ' lectureBox_cho';
        }
        return this.sporadicTemplate.apply(d);
    },
    showInfoPanel: function ()
    {
        return this.infoTemplate.apply(this.getDetailData(this));
    },
    has: function (type, val)
    {
        var o = { ret: false };

        if (type === "teacher")
        {
            type = "teachers";
        }
        else if (type === "room")
        {
            type = "rooms";
        }
        else if (type === "module")
        {
            type = "modules";
        }
        else if (type === "subject")
        {
            type = "subjects";
        }

        if (type === "rooms")
        {
            for (var calendarIndex in this.data.calendar)
            {
                if (this.data.calendar.hasOwnProperty(calendarIndex))
                {
                    var blocks = this.data.calendar[calendarIndex];
                    for (var blockIndex in blocks)
                    {
                        if (blocks.hasOwnProperty(blockIndex))
                        {
                            for (var roomIndex in blocks[blockIndex].lessonData)
                            {
                                if (blocks[blockIndex].lessonData.hasOwnProperty(roomIndex) && roomIndex === val)
                                {
                                        o.ret = true;
                                        return o.ret;
                                }
                            }
                        }
                    }
                }
            }
        }
        else if (this.data[type].containsKey(val))
        {
            o.ret = true;
            return o.ret;
        }

        return o.ret;
    },
    isSporadic: function ()
    {
        return this.data.type === 'sporadic';
    }
});

Ext.define('EventListModel',
{
    extend: 'MySched.Model',

    constructor: function ()
    {
        var data;
        this.data = new MySched.Collection();
    },
    addEvent: function (e)
    {
        // Fuegt ein Event hinzu
        if (e.data.starttime === "00:00")
        {
            e.data.starttime = "08:00";
        }
        if (e.data.endtime === "00:00")
        {
            e.data.endtime = "19:00";
        }
        this.data.add(e.data.id, e);
    },
    getEvent: function (id)
    {
        var idsplit = id.split("_");
        var datas = this.data.filterBy(function (o, k)
        {
            if (k === idsplit[1])
            {
                return true;
            }
            return false;
        }, this);

        return datas.items[0];
    },
    getEvents: function (type, value)
    {
        if (Ext.isEmpty(type) && Ext.isEmpty(value))
        {
            return this.data.items;
        }
 
        var data = this.data.filterBy(function (o, k)
        {
            var eventObjects = o.data.objects;

            // Events mit 0 Objekten können keinem Plan zugeordnet werden und werden erstmal nicht beachtet.
            if(Ext.isArray(eventObjects))
            {
                if(eventObjects.length > 0)
                {
                    var dbID;
                    if(type === "teacher")
                    {
                        dbID = MySched.Mapping.getTeacherDbID(value);
                    }
                    else if(type === "room")
                    {
                        dbID = MySched.Mapping.getRoomDbID(value);
                    }
                    else
                    {
                        return false;
                    }

                    for (var eventIndex = 0; eventIndex < eventObjects.length; eventIndex++)
                    {
                        if (Ext.isObject(eventObjects[eventIndex]) && eventObjects[eventIndex].id === dbID && eventObjects[eventIndex].type === type)
                        {
                            return true;
                        }
                    }
                }
            }
            return false;
        }, this);

        return data.map;
    },
    getEventsForLecture: function(lecture, block, dow)
    {
        var ret = "";

        var data = this.data.filterBy(function (o, k)
        {
            var eventData = o.data;
            var eventStartDate = eventData.startdate;
            var eventEndDate = eventData.enddate;
            var eventStartTime = eventData.starttime;
            var eventEndTime = eventData.endtime;
            var currMOFR = getCurrentMoFrDate();

            eventStartDate = convertGermanDateStringToDateObject(eventStartDate);
            eventEndDate = convertGermanDateStringToDateObject(eventEndDate);

            var lectureData = lecture.data;
            var lectureCalendar = lectureData.calendar;

            for(var lectureCalendarIndex in lectureCalendar)
            {
                if(Ext.isObject(lectureCalendar[lectureCalendarIndex]))
                {
                    var lectureDate = convertEnglishDateStringToDateObject(lectureCalendarIndex);
                    if (eventStartDate <= lectureDate && eventEndDate >= lectureDate && lectureDate >= currMOFR.monday && lectureDate <= currMOFR.friday && Ext.Date.format(lectureDate, "l").toLowerCase() === dow)
                    {
                        var eventBlocks = getBlocksBetweenTimes(eventStartTime, eventEndTime, eventStartDate, eventEndDate);
                        for(var eventBlocksIndex = 0; eventBlocksIndex < eventBlocks.length; eventBlocksIndex++)
                        {
                            var eventBlock = eventBlocks[eventBlocksIndex];
                            if(eventBlock === block)
                            {
                                var eventObjects = eventData.objects;
                                for(var eventObjectsIndex = 0; eventObjectsIndex < eventObjects.length; eventObjectsIndex++)
                                {
                                    var eventObject = eventObjects[eventObjectsIndex];
                                    if(eventObject.type === "teacher")
                                    {
                                        var teacherName = MySched.Mapping.getTeacherKeyByID(eventObject.id);
                                        if(Ext.isString(lectureData.teachers.map[teacherName]) && lectureData.teachers.map[teacherName] !== "removed")
                                        {
                                            return true;
                                        }
                                    }
                                    else if(eventObject.type === "room")
                                    {
                                        var roomData = lectureCalendar[lectureCalendarIndex][block+1].lessonData;
                                        var roomName = MySched.Mapping.getRoomKeyByID(eventObject.id);
                                        for(var roomDataIndex in roomData)
                                        {
                                            if(roomData.hasOwnProperty(roomDataIndex) && Ext.isString(roomData[roomDataIndex]) && roomData[roomDataIndex] !== "removed" && roomName === roomDataIndex)
                                            {
                                                return true;
                                            }
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
            }
            return false;
        });

        for(var dataIndex = 0; dataIndex < data.items.length; dataIndex++)
        {
            ret += data.items[dataIndex].getEventView();
        }
        return ret;
    }
});

/**
 * EventModel
 * @param {Object} Event
 */
Ext.define('EventModel',
{
    extend: 'MySched.Model',

    constructor: function (id, data)
    {
        var eventTemplate;
        this.id = id;
        this.data = data;

        if (this.data.enddate === "00.00.0000")
        {
            this.data.enddate = this.data.startdate;
        }

        this.data.starttime = this.data.starttime.substring(0, 5);
        this.data.endtime = this.data.endtime.substring(0, 5);

        var MySchedEventClass = 'MySchedEvent_' + this.data.source;
        if(this.data.reserve === true)
        {
            MySchedEventClass += " MySchedEvent_reserve";
        }
        this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + '{top_icon}<b id="MySchedEvent_{id}" class="MySchedEvent_name">{event_name}</b><br/>{teacher} / {room}</div>');
    },
    getEventDetailData: function ()
    {
        return Ext.apply(this.getData(this),
        {
            'event_name': this.getName(),
            'event_info': this.getEventInfoView(),
            'teacher': this.getTeacherName(),
            'room': this.getRoomName()
        });
    },
    getName: function ()
    {
        return this.data.title;
    },
    getTeacherName: function ()
    {
        var teacherNames = "";

        this.data.objects.each(function (o, k)
        {
            if (o.type === "teacher")
            {
                var teacherName = getTeacherSurnameWithCutFirstName(MySched.Mapping.getTeacherKeyByID(o.id));
                if (teacherNames !== "")
                {
                    teacherNames += ", ";
                }
                teacherNames += teacherName;
            }
        });

        return teacherNames;
    },
    getRoomName: function ()
    {
        var roomNames = "";

        this.data.objects.each(function (o, k)
        {
            if (o.type === "room")
            {
                var roomName = MySched.Mapping.getRoomKeyByID(o.id);
                if (roomNames !== "")
                {
                    roomNames += ", ";
                }
                roomNames += roomName;
            }
        });

        return roomNames;
    },
    getData: function (addData)
    {
        return this.superclass.getData.call(this, addData);
    },
    getEventView: function (type, bl, collision)
    {
        var d = this.getEventDetailData();
        if (MySched.Authorize.user !== null && MySched.Authorize.role !== 'user' && MySched.Authorize.role !== 'registered' && !this.eventTemplate.html.contains("MySchedEvent_joomla access"))
        {
            this.eventTemplate.html = this.eventTemplate.html.replace("MySchedEvent_joomla", 'MySchedEvent_joomla access');
        }

        var MySchedEventClass = 'MySchedEvent_' + this.data.source;
        if(this.data.reserve === true)
        {
            MySchedEventClass += " MySchedEvent_reserve";
        }
        var collisionIcon = "";

        if (d.reserve === true && collision === true)
        {
            if (bl < 4)
            {
                bl++;
            }
            var blocktimes = blocktotime(bl);
            if (blocktimes[0] < d.starttime && blocktimes[1] > d.starttime)
            {
                collisionIcon = "<img class='MySched_EventCollision' width='24px' height='16px' data-qtip='" + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_COLLISION + "' src='" + MySched.mainPath + "images/warning.png'></img><br/>";
            }
            if (blocktimes[0] < d.endtime && blocktimes[1] > d.endtime)
            {
                collisionIcon = "<img class='MySched_EventCollision' width='24px' height='16px' data-qtip='" + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_COLLISION + "' src='" + MySched.mainPath + "images/warning.png'></img><br/>";
            }
        }

        if (type === "teacher")
        {
            this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + collisionIcon + '<b id="MySchedEvent_{id}" class="MySchedEvent_name">{event_name}</b><br/><small class="event_resource">{room}</small></div>');
        }
        else if (type === "room")
        {
            this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + collisionIcon + '<b id="MySchedEvent_{id}" class="MySchedEvent_name">{event_name}</b><br/><small class="event_resource">{teacher}</small></div>');
        }
        else
        {
            this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + collisionIcon + '<b id="MySchedEvent_{id}" class="MySchedEvent_name">{event_name}</b><br/><small class="event_resource">{teacher} / {room}</small></div>');
        }

        return this.eventTemplate.apply(d);
    },
    getEventInfoView: function ()
    {
        var infoTemplateString = "<div id='MySchedEventInfo_" + this.id + "' class='MySchedEventInfo'>" + "<span class='MySchedEvent_desc'>" + this.data.description + "</span><br/>";
        var teacherS = "";
        var roomS = "";

        teacherS = this.getTeacherName();
        roomS = this.getRoomName();

        if (teacherS.length > 0)
        {
            if (teacherS.contains(", "))
            {
                teacherS = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHERS + ": " + teacherS;
            }
            else
            {
                teacherS = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER + ": " + teacherS;
            }

            infoTemplateString += "<span class='MySchedEvent_teacher'>" + teacherS + "</span><br/>";
        }

        if (roomS.length > 0)
        {
            if (roomS.contains(", "))
            {
                roomS = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOMS + ": " + roomS;
            }
            else
            {
                roomS = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOM + ": " + roomS;
            }

            infoTemplateString += "<span class='MySchedEvent_room'>" + roomS + "</span><br/>";
        }

        return infoTemplateString;
    }
});

/**
 * DoentModel
 * @param {Object} teacher
 */
Ext.define('TeacherModel',
{
    extend: 'MySched.Model',

    constructor: function (teacher)
    {
        this.superclass.constructor.call(this, teacher, teacher);
    },
    getName: function ()
    {
        return MySched.Mapping.getTeacherName(this.id);
    },
    getObjects: function ()
    {
        return MySched.Mapping.getObjects("teacher", this.id);
    }
});

/**
 * RoomModel
 * @param {Object} room
 */
Ext.define('RoomModel',
{
    extend: 'MySched.Model',

    constructor: function (room)
    {
        this.superclass.constructor.call(this, room, room);
    },
    getName: function ()
    {
        return MySched.Mapping.getRoomName(this.id);
    },
    getObjects: function ()
    {
        return MySched.Mapping.getObjects("room", this.id);
    }
});

/**
 * ClasModel
 * @param {Object} module
 */
Ext.define('PoolModel',
{
    extend: 'MySched.Model',

    constructor: function (module)
    {
        this.superclass.constructor.call(this, module, module);
    },
    getName: function ()
    {
        return MySched.Mapping.getClasName(this.id);
    },
    getFullName: function ()
    {
        return MySched.Mapping.getObjectField("module", this.id, "parentName") + " - " + MySched.Mapping.getObjectField("module", this.id, "name");
    },
    getObjects: function ()
    {
        return MySched.Mapping.getObjects("module", this.id);
    }
});

/**
 * SubjectModel
 * @param {Object} module
 */
Ext.define('SubjectModel',
{
    extend: 'MySched.Model',

    constructor: function (subject)
    {
        this.superclass.constructor.call(this, subject, subject);
    },
    getName: function ()
    {
        return MySched.Mapping.getSubjectName(this.id);
    },
    getFullName: function ()
    {
        return MySched.Mapping.getObjectField("subject", this.id, "parentName") + " - " + MySched.Mapping.getObjectField("subject", this.id, "name");
    },
    getObjects: function ()
    {
        return MySched.Mapping.getObjects("subject", this.id);
    }
});

function getModuledesc(mninr)
{
    if (Ext.getCmp('content-anchor-tip'))
    {
        Ext.getCmp('content-anchor-tip').destroy();
    }
    var waitDesc = Ext.MessageBox.show(
    {
        cls: 'mySched_noBackground',
        closable: false,
        msg: '<img  src="' + MySched.mainPath + 'images/ajax-loader.gif" />'
    });
    Ext.Ajax.request(
    {
        url: _C('getModule'),
        method: 'POST',
        params: { nrmni: mninr },
        scope: waitDesc,
        failure: function ()
        {
            waitDesc.hide();
            Ext.Msg.show(
            {
                minWidth: 400,
                fn: function ()
                {
                    Ext.MessageBox.hide();
                },
                buttons: Ext.MessageBox.OK,
                title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR,
                msg: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DESCRIPTION_ERROR
            });
        },
        success: function (response)
        {
            var responseData = [];
            try
            {
                responseData = Ext.decode(response.responseText);
                waitDesc.hide();

                //Modulnummer wurde gefunden :)
                if (responseData.success === true)
                {
                    Ext.Msg.show(
                    {
                        minWidth: 600,
                        fn: function ()
                        {
                            Ext.MessageBox.hide();
                        },
                        buttons: Ext.MessageBox.OK,
                        title: responseData.nrmni + " - " + responseData.title,
                        msg: responseData.html
                    });
                }

                //Modulnummer wurde nicht gefunden :(
                else
                {
                    Ext.Msg.show(
                    {
                        minWidth: 250,
                        fn: function ()
                        {
                            Ext.MessageBox.hide();
                        },
                        buttons: Ext.MessageBox.OK,
                        title: responseData.nrmni,
                        msg: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NO_DATA_FOUND + "!"
                    });
                }
            }
            catch (e)
            {
                waitDesc.hide();
                Ext.Msg.show(
                {
                    minWidth: 250,
                    fn: function ()
                    {
                        Ext.MessageBox.hide();
                    },
                    buttons: Ext.MessageBox.OK,
                    title: responseData.nrmni,
                    msg: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NO_DATA_FOUND + "!"
                });
            }

        }
    });
}

function zeigeTermine(rooms)
{
    if (Ext.ComponentMgr.get('sporadicPanel').collapsed)
    {
        Ext.ComponentMgr.get('sporadicPanel').expand();
    }

    var counterall = 0;
    var allrooms = Ext.ComponentMgr.get('sporadicPanel').body.select("p[id]");
    var index;
    for (index in allrooms.elements)
    {
        if (!Ext.isFunction(allrooms.elements[index]) && allrooms.elements[index].style !== null)
        {
            allrooms.elements[index].style.display = "none";
            counterall++;
        }
    }

    rooms = rooms.replace(/<[^>]*>/g, "").replace(/[\n\r]/g, '').replace(/ +/g, ' ').replace(/^\s+/g, '').replace(/\s+$/g, '').split(",");
    var counter = 0, room;
    for (var i = 0; i < rooms.length; i++)
    {
        room = rooms[i].replace(/[\n\r]/g, '').replace(/ +/g, ' ').replace(/^\s+/g, '').replace(/\s+$/g, '');
        var pos = room.search(/\s/);
        if (pos !== -1)
        {
            room = room.substring(0, pos);
        }
        var selectedroomevents = Ext.ComponentMgr.get('sporadicPanel').body.select("p[id^=" + room + "_]");
        for (index in selectedroomevents.elements)
        {
            if (selectedroomevents.elements.hasOwnProperty(index) &&
                !Ext.isFunction(selectedroomevents.elements[index]) &&
                selectedroomevents.elements[index].style !== null)
            {
                selectedroomevents.elements[index].style.display = "block";
                counter++;
            }
        }
    }

    if (counter !== 0)
    {
        var tmp = Ext.ComponentMgr.get('sporadicPanel').setTitle(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SINGLE_EVENT + ' - ' + room + ' (' + counter + ')');
    }
}