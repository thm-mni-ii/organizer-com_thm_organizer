/**
 * mySched - Mainclass by Thorsten Buss and Wolf Rost
 */
// Zeigt farbige Frei/Belegtzeiten an
MySched.freeBusyState = true;
// verweiss auf aktuell ausgewaehlten Stundenplan
MySched.selectedSchedule = null;
// Versionsnummer
MySched.version = '3.1.0';
// verweiss auf den Plan mit den Aenderungen
MySched.delta = null;
// verweiss auf den Plan mit dem ChangeLog
MySched.responsibleChanges = null;
MySched.session = new Array();
MySched.daytime = new Array();
MySched.loadedLessons = new Array();
MySched.mainPath = externLinks.mainPath;
// set ajax timeout to 10 seconds
Ext.Ajax.timeout = 60000;
// Setzte die initalwerte fuer das Konfigurationsobjekt
MySched.Config.addAll(
{
    // Bestimt die art und weise der Anzeige von Zusatzinfos
    infoMode: 'popup',
    // layout | popup
    ajaxHandler: externLinks.ajaxHandler,
    estudycourse: MySched.mainPath + 'php/estudy_course.php',
    infoUrl: MySched.mainPath + 'php/info.php',
    showHeader: false,
    // soll der Headerbereich angezeigt werden?
    headerHTML: '<img src="http://www.mni.thm.de/templates/fh/Bilder/Header.png" title="fh-header" alt="fh-header"/>',
    enableSubscribing: false,
    // Aktiviert den Button und die Funktion 'Einschreiben'
    logoutTarget: 'http://www.mni.thm.de'
});

// Authorize wir initalisiert
// Mit dem Uebergebenen Array wird die Rolle auf rechte in MySched gemappt
// ACHTUNG!! Keys werden komplett lowercase gehalten
MySched.Authorize.init(
{
    // Welche Rolle hat ein nicht angemeldeter User?
    defaultRole: 'user',
    // ALL definiert den gemeinsammen Nenner aller Rollen
    ALL: {
        module: '*',
        diff: '*',
        curtea: '*'
    },
    // jede Rolle kann ein Array mit Keys oder ein string mit '*' zugewiesen
    // bekommen
    user: {
        room: '*'
    },
    registered: {
        room: '*'
    },
    author: {
        teacher: '*',
        room: '*'
    },
    editor: {
        teacher: '*',
        room: '*'
    },
    publisher: {
        teacher: '*',
        room: '*'
    },
    administrator: {
        teacher: '*',
        room: '*',
        respChanges: '*'
    },
    manager: {
        teacher: '*',
        room: '*'
    },
    'super users': {
        teacher: '*',
        room: '*',
        respChanges: '*'
    }
});

MySched.BlockMenu = [];
MySched.BlockMenu.Menu = [];

/*
 * var addLesson = { text: "Veranstaltung hinzuf&uuml;gen", icon:
 * MySched.mainPath + "images/icon-publish.png", handler: function () {
 * newPEvent(MySched.BlockMenu.day, MySched.BlockMenu.stime,
 * MySched.BlockMenu.etime); } };
 * 
 * MySched.BlockMenu.Menu[MySched.BlockMenu.Menu.length] = addLesson;
 */

/**
 * MainObjekt
 */
MySched.Base = function ()
{
    var schedule, sid, fertig = false;

    return {
        init: function ()
        {
            if (Ext.isString(MySched.startup) === true)
            {
                try
                {
                    MySched.startup = Ext.decode(decodeURIComponent(MySched.startup));
                }
                catch (e)
                {

                    }
                MySched.Base.startMySched(MySched.startup["Grid.load"]);
            }
        },
        startMySched: function (json)
        {
            var length = 0;
            if (Ext.isNumber(json.length)) length = json.length;
            else length = json.size;
            for (var i = 1; i <= length; i++)
            {
                if (!MySched.daytime[json[i].day])
                {
                    MySched.daytime[json[i].day] = new Array();
                    MySched.daytime[json[i].day]["engName"] = numbertoday(json[i].day);
                    MySched.daytime[json[i].day]["gerName"] = weekdayEtoD(numbertoday(json[i].day));
                    MySched.daytime[json[i].day]["localName"] = "day"
                }
                if (!MySched.daytime[json[i].day][json[i].period]) MySched.daytime[json[i].day][json[i].period] = new Array();
                MySched.daytime[json[i].day][json[i].period]["etime"] = json[i].endtime.substr(0, 5);
                MySched.daytime[json[i].day][json[i].period]["stime"] = json[i].starttime.substr(0, 5);
                MySched.daytime[json[i].day][json[i].period]["tpid"] = json[i].gpuntisID;
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
         */
        registerScheduleEvents: function ()
        {
            // Steuerun der Anzeige des "leeren" Buttons - nur wenn Stundenplan
            // nicht schon leer
            MySched.Schedule.on(
            {
                'lectureAdd': function ()
                {
                    Ext.ComponentMgr.get('btnEmpty').enable();
                },
                'lectureDel': function ()
                {
                    if (MySched.Schedule.isEmpty())
                    {
                        Ext.ComponentMgr.get('btnPdf')
                            .disable();
                        if (_C('enableSubscribing')) Ext.ComponentMgr.get('btnSub')
                            .disable();
                    }
                },
                'changed': function ()
                {
                    var contentAnchorTip = Ext.getCmp('content-anchor-tip');
                    if (contentAnchorTip) contentAnchorTip.destroy();
                    if (!MySched.Schedule.isEmpty() && MySched.libraryFPDFIsInstalled)
                    {
                        Ext.ComponentMgr.get('btnPdf').enable();
                        if (_C('enableSubscribing'))
                        {
                        	Ext.ComponentMgr.get('btnSub').enable();
                        }
                            
                    }
                    
                    if(MySched.libraryFPDFIsInstalled)
                    {
                    	Ext.ComponentMgr.get('btnSave').enable();
                	}
                        
                    var tab = MySched.layout.tabpanel.getComponent('mySchedule');
                    tab.mSchedule.status = "unsaved";
                },
                'save': function (s)
                {
                    var tab = MySched.layout.tabpanel.getComponent('mySchedule');
                    tab.mSchedule.status = "saved";
                    Ext.ComponentMgr.get('btnSave').disable();
                },
                'load': function (s)
                {
                    MySched.Base.createUserSchedule();
                    Ext.ComponentMgr.get('btnSave').disable();
                    var tab = MySched.layout.tabpanel.getComponent('mySchedule');
                },
                'clear': function (s)
                {}
            });
        },
        /**
         * Stundenplan Events registrieren.
         */
        regScheduleEvents: function (id)
        {
            MySched.selectedSchedule.on(
            {
                'changed': function ()
                {
                    var contentAnchorTip = Ext.getCmp('content-anchor-tip');
                    if (contentAnchorTip) contentAnchorTip.destroy();
                    var tab = MySched.layout.tabpanel.getComponent(id);

                   	Ext.ComponentMgr.get('btnSave').enable();
                        
                    tab.mSchedule.status = "unsaved";
                },
                'save': function (s)
                {
                    var tab = MySched.layout.tabpanel.getComponent(id);
                    tab.mSchedule.status = "saved";
                    Ext.ComponentMgr.get('btnSave').disable();
                },
                'clear': function (s)
                {
                    Ext.ComponentMgr.get('btnEmpty').disable();
                }
            });
        },
        /**
         * Laed die XML Datei und startet den Parsevorgang
         * 
         * @param {String}
         *            url XML-Datei
         */
        loadLectures: function (url)
        {
            // Stundenplandaten werden in einem 'gesamten' Stundenplan
            // gespeichert
            this.schedule = new mSchedule();
            this.afterLoad();
        },
        /**
         * Aufgaben nachdem die XMLDaten erfolgreich geladen wurden
         * 
         * @param {Object}
         *            ret ReturnObjekt vom Ladevorgang (Im Endeffet this)
         */
        afterLoad: function (ret)
        {
            MySched.eventlist = new mEventlist();
            if (checkStartup("Events.load") === true)
            {
                MySched.TreeManager.afterloadEvents(MySched.startup["Events.load"].data);
                MySched.Base.myschedInit(ret);
            }
            else Ext.Ajax.request(
            {
                url: _C('ajaxHandler'),
                method: 'POST',
                params: {
                    jsid: MySched.SessionId,
                    scheduletask: "Events.load"
                },
                failure: function (response, request)
                {
                    MySched.Base.myschedInit(ret);
                },
                success: function (response, request)
                {
                    try
                    {
                        var jsonData = new Array();

                        if (response.responseText.length > 0)
                        {
                            jsonData = Ext.decode(response.responseText);
                        }

                        MySched.TreeManager.afterloadEvents(jsonData);

                        MySched.Base.myschedInit(ret);
                    }
                    catch (e)
                    {

                        }
                }
            });
        },
        myschedInit: function (ret)
        {
        	var lessonData = MySched.startup["Lessons"];
            plantypeID = "";

            for (var item in lessonData)
            {
                if (Ext.isObject(lessonData[item]))
                {
                    var record = new mLecture(
                    item,
                    Ext.clone(lessonData[item]),
                    MySched.class_semester_id,
                    plantypeID);
                    MySched.Base.schedule.addLecture(record);
                }
            }
        	
            // Initialisiert "Mein Stundenplan"
            MySched.Schedule = new mSchedule('mySchedule',
            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE);

            // Initialisiert "Änderungen der Verantwortlichen"
            MySched.responsibleChanges = new mSchedule("respChanges",
            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_OWN);

            // Registriert Events bei Aenderung des eigenen Stundenplans
            MySched.Base.registerScheduleEvents();

            // Initalisiert Infoanzeige
            MySched.InfoPanel.init();
            MySched.SelectionManager.init();

            // Erstellt das Layout
            MySched.layout.buildLayout();

            MySched.Base.setScheduleDescription(MySched.startup["ScheduleDescription.load"].data);
        },
        setScheduleDescription: function (jsonData)
        {
            if (Ext.isObject(jsonData))
            {
                MySched.Tree.setTitle(jsonData.description, true);

                MySched.session["begin"] = jsonData.startdate;
                MySched.session["end"] = jsonData.enddate;
                MySched.session["creationdate"] = jsonData.creationdate;
                Ext.ComponentMgr.get('leftMenu')
                    .setTitle(
                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_AS_OF + " " + MySched.session["creationdate"]);
                // Managed die Sichtbarkeit der Add/Del Buttons in der Toolbar
                MySched.SelectionManager.on('select', function (el)
                {
                    if (MySched.Schedule.lectureExists(el.id))
                    {
                        Ext.ComponentMgr.get('btnDel')
                            .enable();
                    }
                    else
                    {
                        Ext.ComponentMgr.get('btnAdd')
                            .enable();
                    }
                });
                MySched.SelectionManager.on('unselect', function ()
                {
                    Ext.ComponentMgr.get('btnDel')
                        .disable();
                    Ext.ComponentMgr.get('btnAdd')
                        .disable();
                });
                MySched.SelectionManager.on('lectureAdd', function ()
                {
                    Ext.ComponentMgr.get('btnAdd').disable();
                });
                MySched.SelectionManager.on('lectureDel', function ()
                {
                    Ext.ComponentMgr.get('btnDel').disable();
                });

                /*MySched.Tree.refreshTreeData();

                var tree = MySched.Tree.tree;

                var treeRoot = tree.getRootNode();

                var semid = treeRoot.firstChild.data.id;

                semid = semid.split(".")

                semid = semid[0];

                var deltaid = semid + ".1.delta";

                // Initialisiert "Änderungen"
                MySched.delta = new mSchedule(
                deltaid,
                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_CENTRAL);
                MySched.delta.responsible = "delta";*/

                if (MySched.SessionId)
                {
                    MySched.Authorize.verifyToken(MySched.SessionId,
                    MySched.Authorize.verifySuccess, MySched.Authorize);
                    // Lädt Delta Daten
//                    MySched.delta.load(_C('ajaxHandler'), 'json',
//                    MySched.delta.loadsavedLectures, MySched.delta, "delta");
                }
                else
                {
                    
                }
            }
            else
            {
                Ext.ComponentMgr.get('leftMenu')
                    .setTitle(
                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_INVALID);
                Ext.ComponentMgr.get('topMenu')
                    .disable();
            }
        },
        /**
         * Erstellt den Tab "Mein Stundenplan"
         */
        createUserSchedule: function ()
        {
            if (!MySched.layout.tabpanel.getComponent('mySchedule'))
            {
                var grid = MySched.Schedule.show(true);
                Ext.apply(
                grid,
                {
                    closable: false,
                    tabTip: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE,
                    iconCls: 'myScheduleIcon'
                });

                MySched.layout.createTab('mySchedule',
                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE,
                grid, "mySchedule");
                // tab 'Mein Stundenplan' wird DropArea
                var tabID = MySched.layout.tabpanel.getComponent('mySchedule')
                    .tab.el.dom;
                var dropTarget = new Ext.dd.DropTarget(tabID, this.getDropConfig());
            }
        },
        /**
         * Laed den von dem User definierten Stundenplan
         */
        loadUserSchedule: function ()
        {
            MySched.Schedule.load(_C('ajaxHandler'), 'json',
            MySched.Schedule.preParseLectures, MySched.Schedule,
            MySched.Authorize.user);
            MySched.layout.viewport.doLayout();
        },
        /**
         * Gibt die Drop-Konfiguration fuer Drag'n'Drop zurueck
         */
        getDropConfig: function ()
        {
            // Definiert Konfiguration fuer Drag'n'Drop
            return {
                ddGroup: 'lecture',
                // Akzeptiert lectures
                notifyDrop: function (dd, e, data)
                {
                    if (!Ext.isEmpty(data.records))
                    {
                        if (data.records[0].isLeaf())
                        {
                            // Fuegt gesammten SemesterPlan dem eigenen
                            // Stundenplan hinzu
                            var n = data.records[0].raw;

                            var nodeID = n.id;
                            var nodeKey = n.nodeKey;
                            var gpuntisID = n.gpuntisID;
                            var semesterID = n.semesterID;
                            var plantypeID = n.plantype;
                            var type = n.type;

                            if (MySched.loadLessonsOnStartUp == false)
                            {
                                Ext.Ajax.request(
                                {
                                    url: _C('ajaxHandler'),
                                    method: 'POST',
                                    params: {
                                        nodeID: nodeID,
                                        nodeKey: nodeKey,
                                        gpuntisID: gpuntisID,
                                        semesterID: semesterID,
                                        scheduletask: "Ressource.load",
                                        plantypeID: plantypeID,
                                        type: type
                                    },
                                    failure: function (response)
                                    {
                                        Ext.Msg.alert(
                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR,
                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_ERROR);
                                    },
                                    success: function (response)
                                    {
                                        var json = Ext.decode(response.responseText);
                                        var lessonData = json["lessonData"];
                                        var lessonDate = json["lessonDate"];
                                        for (var item in lessonData)
                                        {
                                            if (Ext.isObject(lessonData[item]))
                                            {
                                                var record = new mLecture(
                                                item,
                                                lessonData[item], semesterID,
                                                plantypeID);
                                                MySched.Base.schedule.addLecture(record);
                                                //															MySched.TreeManager.add(record);
                                            }
                                        }
                                        if (Ext.isObject(lessonDate))
                                        {
                                            MySched.Calendar.addAll(lessonDate);
                                        }

                                        var s = new mSchedule(
                                        nodeID, '_tmpSchedule')
                                            .init(type, nodeKey);

                                        var lectures = s.getLectures();

                                        for (var lectureIndex = 0; lectureIndex < lectures.length; lectureIndex++)
                                        {
                                            MySched.Schedule.addLecture(lectures[lectureIndex]);
                                        }

                                        MySched.selectedSchedule.eventsloaded = null;
                                        MySched.selectedSchedule.refreshView();
                                    }
                                });

                            }
                            else
                            {
                                var s = new mSchedule(nodeID, '_tmpSchedule')
                                    .init(type, nodeKey);

                                var lectures = s.getLectures();

                                for (var lectureIndex = 0; lectureIndex < lectures.length; lectureIndex++)
                                {
                                    MySched.Schedule.addLecture(lectures[lectureIndex]);
                                }

                                MySched.selectedSchedule.eventsloaded = null;
                                MySched.selectedSchedule.refreshView();
                            }
                        }
                    }
                    else
                    {
                        // Fuegt Veranstaltung zu eigenem Stundenplan hinzu
                        MySched.Schedule.addLecture(MySched.Base.schedule.getLecture(data.id));
                        MySched.selectedSchedule.eventsloaded = null;
                        MySched.selectedSchedule.refreshView();
                    }
                    return true;
                }
            };
        },
        /**
         * Gibt die Veranstaltung mit der id zurueck
         * 
         * @param {Object}
         *            id VeranstlatungsID
         */
        getLecture: function (id)
        {
            return this.schedule.getLecture(id);
        },
        /**
         * Gibt nur bestimmte Lectures zurueck
         * 
         * @param {Object}
         *            type Ueber welches Feld soll Selektiert werden
         * @param {Object}
         *            value Welchen Wert muss dieses Feld haben
         * @return {MySched.Collection}
         */
        getLectures: function (type, value)
        {
            return this.schedule.getLectures(type, value);
        },
        /**
         * Handelt den FreeBusy Zustand - wird beim schalten des Buttons
         * aufgerufen
         * 
         * @param {Object}
         *            e Event welches ausgeloest wurde
         * @param {Object}
         *            state Zustand des Buttons
         */
        freeBusyHandler: function (e, state)
        {
            if (!state)
            {
                Ext.select('.blockFree')
                    .replaceClass('blockFree', 'blockFree_DIS');
                Ext.select('.blockBusy')
                    .replaceClass('blockBusy', 'blockBusy_DIS');
                Ext.select('.blockOccupied')
                    .replaceClass('blockOccupied', 'blockOccupied_DIS');
            }
            else
            {
                Ext.select('.blockFree_DIS')
                    .replaceClass('blockFree_DIS', 'blockFree');
                Ext.select('.blockBusy_DIS')
                    .replaceClass('blockBusy_DIS', 'blockBusy');
                Ext.select('.blockOccupied_DIS')
                    .replaceClass('blockOccupied_DIS', 'blockOccupied');
            }
            // Legt neuen State fest
            MySched.freeBusyState = state;
        }
    }
}();

/**
 * Zeigt die Informationen zur augewaehlten Lecture an
 */
MySched.InfoPanel = function ()
{
    var el = null;
    return {
        init: function ()
        {
            this.el = Ext.get('infoPanel');
        },
        /**
         * Zeigt eine Info in dem Info Panel unterhalb des Baumes an
         * 
         * @param {Object}
         *            el HTML Element welches selektiert wurde
         */
        showInfo: function (el)
        {
            var text = false;
            if (Ext.type(el) == 'object')
            {

                var l = MySched.Base.getLecture(el.id);
                if (l)
                {
                    text = l.showInfoPanel();
                }
            }
            else
            {
                text = el;
            }
            if (!text) this.el.update(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_ERROR);
            else
            {
                this.el.update(text);
            }
            // Updated Handler fuer Detailinfos
            this.updateDetailInfoClickHandler();
        },
        /**
         * Erneuert die onClick events der InfoIcons innerhalb des InfoPanels
         */
        updateDetailInfoClickHandler: function ()
        {
            this.el.select('.detailInfoBtn')
                .on('click', this.detailInfoClick,
            this);
        },
        /**
         * Wird aufgerufen wenn ein blaues Informationsicon fuer Detailinfos
         * geklickt wird
         * 
         * @param {Object}
         *            e Event welches ausgeloest wurde
         */
        detailInfoClick: function (e)
        {
            // Splitte Id - zb. info_room_i136
            var tmp = e.target.id.split('_');
            // Holt die geforderte Info vom Server ab.
            Ext.Ajax.request(
            {
                url: _C('infoUrl'),
                params: {
                    type: tmp[1],
                    key: tmp[2],
                    viewMode: _C('infoMode')
                },
                method: 'POST',
                failure: function ()
                {
                    Ext.Msg.alert(
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NOTICE,
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NOTICE_ERROR);
                },
                scope: this,
                success: function (resp)
                {
                    try
                    {
                        var json = Ext.decode(resp.responseText);
                        if (!json.success)
                        {
                            if (!json.error) json.error = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_UNKNOWN_ERROR;
                            this.showDetailInfo(
                            json.error,
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR);
                            return;
                        }
                        // Zeigt ermittelte Info an
                        this.showDetailInfo(
                        Ext.Template(json.template)
                            .apply(json.data),
                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_INFO);
                    }
                    catch (e)
                    {}
                }
            });
        },
        /**
         * Zeigt Detailierte Info an
         * 
         * @param {Object}
         *            text
         * @param {Object}
         *            title
         */
        showDetailInfo: function (text, title)
        {
            var mode = _C('infoMode');
            // Je nach Mode wird es im normalen InfoFenster oder als Popup
            // angezeigt.
            if (mode == 'layout')
            {
                this.showInfo(text);
            }
            else if (mode == 'popup')
            {
                Ext.Msg.show(
                {
                    title: title,
                    buttons: {
                        cancel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CLOSE
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
        clearInfo: function ()
        {
            Ext.get('infoPanel')
                .update('');
        }
    }
}();

/**
 * Steuert die Auswahl der Veranstaltungen und die Hoverbuttons
 */
MySched.SelectionManager = Ext.apply(
new Ext.util.Observable(),
{
    selectEl: null,
    hoverEl: new MySched.Collection(),
    selectButton: null,
    selectLectureId: null,
    lectureAddButton: externLinks.lectureAddButton,
    lectureRemoveButton: externLinks.lectureRemoveButton,
    /**
     * Initalisierung
     */
    init: function ()
    {
        // Definierten welche Events geworfen werden
        this.addEvents(
        {
            beforeSelect: true,
            select: true,
            beforeUnselect: true,
            unselect: true,
            lectureAdd: true,
            lectureDel: true
        });
    },
    /**
     * Stoppt die Selektierung
     * 
     * @param {Object}
     *            o o == leer => fuer den aktiven Tab o==true =>
     *            dann fuer document, und wenn o ==
     *            Ext.Element|Node => nur fuer dieses
     */
    stopSelection: function (o)
    {
        if (Ext.type(o) == 'object')
        { // Nur unterhalb
            // uebergebenen Objekt
            var dom = o.dom || Ext.get(o)
                .dom;
            Ext.select('.status_icons_delete', false, dom)
                .removeAllListeners();
            Ext.select('.status_icons_info', false, dom)
                .removeAllListeners();
            Ext.select('.status_icons_edit', false, dom)
                .removeAllListeners();
            Ext.select('.teachername', false, dom)
                .removeAllListeners();
            Ext.select('.lectureBox', false, dom)
                .removeAllListeners();
            Ext.select('.conMenu', false, dom)
                .removeAllListeners();
            Ext.select('.MySchedEvent_joomla', false, dom)
                .removeAllListeners();
            Ext.select('.lecturename', false, dom)
                .removeAllListeners();
            Ext.select('.roomname', false, dom)
                .removeAllListeners();
            Ext.select('.modulename', false, dom)
                .removeAllListeners();
            Ext.select('.status_icons_add', false, dom)
                .removeAllListeners();
            Ext.select('.status_icons_info', false, dom)
                .removeAllListeners();
            Ext.select('.status_icons_estudy', false, dom)
                .removeAllListeners();
        }
        else if (o === true)
        { // Alle
            Ext.select('.status_icons_delete')
                .removeAllListeners();
            Ext.select('.status_icons_info')
                .removeAllListeners();
            Ext.select('.status_icons_edit')
                .removeAllListeners();
            Ext.select('.teachername')
                .removeAllListeners();
            Ext.select('.lectureBox')
                .removeAllListeners();
            Ext.select('.conMenu')
                .removeAllListeners();
            Ext.select('.MySched_event_joomla')
                .removeAllListeners();
            Ext.select('.lecturename')
                .removeAllListeners();
            Ext.select('.roomname')
                .removeAllListeners();
            Ext.select('.modulename')
                .removeAllListeners();
            Ext.select('.status_icons_add')
                .removeAllListeners();
            Ext.select('.status_icons_info')
                .removeAllListeners();
            Ext.select('.status_icons_estudy')
                .removeAllListeners();
        }
        else if (MySched.layout.tabpanel.items.getCount() > 0)
        { // Nur
            // Aktiven
            // Tab
            var activeTabDom = MySched.layout.tabpanel.getActiveTab()
                .getEl()
                .dom;
            Ext.select('.status_icons_delete', false,
            activeTabDom)
                .removeAllListeners();
            Ext.select('.status_icons_info', false,
            activeTabDom)
                .removeAllListeners();
            Ext.select('.status_icons_edit', false,
            activeTabDom)
                .removeAllListeners();
            Ext.select('.teachername', false, activeTabDom)
                .removeAllListeners();
            Ext.select('.lectureBox', false, activeTabDom)
                .removeAllListeners();
            Ext.select('.conMenu', false, activeTabDom)
                .removeAllListeners();
            Ext.select('.MySchedEvent_joomla', false,
            activeTabDom)
                .removeAllListeners();
            Ext.select('.lecturename', false, activeTabDom)
                .removeAllListeners();
            Ext.select('.roomname', false, activeTabDom)
                .removeAllListeners();
            Ext.select('.modulename', false, activeTabDom)
                .removeAllListeners();
            Ext.select('.status_icons_add', false,
            activeTabDom)
                .removeAllListeners();
            Ext.select('.status_icons_info', false,
            activeTabDom)
                .removeAllListeners();
            Ext.select('.status_icons_estudy', false,
            activeTabDom)
                .removeAllListeners();
        }
        // Ext.select('.teachername', false,
        // document).removeAllListeners();
        // Ext.select('.roomshortname', false,
        // document).removeAllListeners();
    },
    /**
     * Startet die Selektierung
     * 
     * @param {Object}
     *            el Tab fuer den die Selektierung gestartet
     *            werden soll wenn leer, dann fuer den aktiven
     *            Tab
     */
    startSelection: function (el)
    {
        var tab = el || MySched.layout.tabpanel.getActiveTab()
            .getEl();
        if (!tab) return;
        // this.stopSelection(tab);
        
//        Ext.select('.status_icons_delete', false, tab.dom)
//            .on(
//        {
//            'click': function (e)
//            {
//                if (e.button == 0) // links Klick
//                {
//                    e.stopEvent();
//                    MySched.SelectionManager.deleteLesson();
//                }
//            },
//            scope: this
//        });

        Ext.select('.status_icons_info', false, tab.dom)
            .on(
        {
            'click': function (e)
            {
                if (e.button == 0) // links Klick
                {
                    e.stopEvent();
                    this.showModuleInformation(e);
                }
            },
            scope: this
        });

//        Ext.select('.status_icons_edit', false, tab.dom)
//            .on(
//        {
//            'click': function (e)
//            {
//                if (e.button == 0) // links Klick
//                {
//                    e.stopEvent();
//                    MySched.SelectionManager.editLesson();
//                }
//            },
//            scope: this
//        });

        // Aboniert Events für Teacherentennamen
        Ext.select('.teachername', false, tab.dom)
        .on(
        {
            'click': function (e)
            {
                if (e.button == 0) // links Klick
                this.showSchedule(e, 'teacher');
            },
            scope: this
        });

        // Aboniert Events für Lecturenamen
        Ext.select('.lecturename', false, tab.dom)
        .on(
        {
            'mouseover': function (e)
            {
                e.stopEvent();
                this.showInformation(e);
            },
            'mouseout': function ()
            {
                var contentAnchorTip = Ext.getCmp('content-anchor-tip');
                if (contentAnchorTip) contentAnchorTip.destroy();
            },
            'click': function (e)
            {
                e.stopEvent();
                this.showModuleInformation(e);
            },
            scope: this
        });

        // Aboniert Events für Teacherentennamen
        Ext.select('.MySchedEvent_name', false, tab.dom).on(
        {
            'mouseover': function (e)
            {
                e.stopEvent();
                this.showEventInformation(e);    
            },
            'mouseout': function ()
            {
                var contentAnchorTip = Ext.getCmp('content-anchor-tip');
                if (contentAnchorTip) contentAnchorTip.destroy();
            },
            'click': function (e)
            {
                if (e.button == 0) // links
                // Klick
                e.stopEvent();
                if (MySched.Authorize.user != null && MySched.Authorize.role != 'user' && MySched.Authorize.role != 'registered') addNewEvent(e.target.id);
            },
            scope: this
        });

        // Aboniert Events für Teacherentennamen
        Ext.select('.roomname', false, tab.dom)
            .on(
        {
            'click': function (e)
            {
                if (e.button == 0) // links Klick
                this.showSchedule(e, 'room');
            },
            scope: this
        });

        // Aboniert Events f�r Teacherentennamen
        Ext.select('.modulename', false, tab.dom)
            .on(
        {
            'click': function (e)
            {
                this.showSchedule(e, 'module');
            },
            scope: this
        });

        // Aboniert Events der Veranstaltungsboxen
        Ext.select('.lectureBox', false, tab.dom)
            .on(
        {
            'mousedown': this.onMouseDown,
            'dblclick': this.ondblclick,
            'contextmenu': function (e)
            {
                showLessonMenu(e);
            },
            scope: this
        });

        // Aboniert Events der Veranstaltungsboxen
        Ext.select('.status_icons_add', false, tab.dom)
            .on(
        {
            'click': function (e)
            {
                e.stopEvent();
                this.lecture2ScheduleHandler()
            },
            scope: this
        });

        Ext.select('.conMenu', false, tab.dom)
            .on(
        {
            'contextmenu': function (e)
            {
                showBlockMenu(e);
            },
            scope: this
        });
    },
    showSchedule: function (e, type)
    {
    	var target = e.getTarget();
    	var id = target.getAttribute(type+"ID");

        var nodeID = null;
        var nodeKey = null;
        var gpuntisID = null;
        var plantypeID = null;
        var semesterID = null;
        var parent = null;
        
        nodeKey = id;
        semesterID = MySched.class_semester_name;

        if(type == "teacher")
        {
        	parent = MySched.Mapping.getTeacherParent(id);
        }
        else if(type == "module")
        {
        	parent = MySched.Mapping.getModuleParent(id);
        }
        else if(type == "room")
        {
        	parent = MySched.Mapping.getRoomParent(id);
        }
        else
        {
        	return;
        }

        nodeID = semesterID + ";" + type + ";" + parent + ";" + nodeKey;

        MySched.Tree.showScheduleTab(nodeID, nodeKey, gpuntisID, semesterID, plantypeID, type);
        
        var record = MySched.Tree.tree.getRootNode().findChild("id",nodeID, true);
        var path = record.getPath("id", "#");
        MySched.Tree.tree.expandPath(path, "id", "#");
        MySched.Tree.tree.getSelectionModel().select(record);

    },
    showEventInformation: function (e)
    {
        var el = e.getTarget('.MySchedEvent_joomla', 5, true);
        if (!el) el = e.getTarget('.MySchedEvent_name', 5, true);
        if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip')
            .destroy();
        var xy = el.getXY();
        xy[0] = xy[0] + el.getWidth() + 10;
        var l = MySched.eventlist.getEvent(el.id);
        var ttInfo = Ext.create('Ext.tip.ToolTip',
        {
            title: '<div class="MySchedEvent_tooltip"> ' + l.data.title + " " + '</div>',
            id: 'content-anchor-tip',
            target: el.id,
            anchorToTarget: true,
            autoHide: false,
            html: l.getEventInfoView(),
            cls: "mySched_tooltip_index"
        });

        ttInfo.showAt(xy);
    },
    showModuleInformation: function (e)
    {
        var bla = typeof e;
        
        if (typeof e == "undefined")
        {
            var id = "";
            if (this.selectLectureId)
            {
                id = this.selectLectureId;
                var el = Ext.get(id);
            }
            else
            {
                var el = this.selectEl;
                id = el.id;
            }
        }
        else
        {
        	if(e.getTarget)
        	{
        		var el = e.getTarget('.lectureBox', 5, true);
        	}
        	else
        	{
        		var el = e;
        	}
        }

        var l = MySched.selectedSchedule.getLecture(el.id);
        var subjects = l.data.subjects;
        var subjectNo = null;
        
        this.showSubjectNoMenu(subjects, e);
    },
    showSubjectWindow: function(subjectNo)
    {
    	var modulewin = Ext.create('Ext.Window',
        {
            id: 'moduleWin',
            width: 600,
            height: 450,
            modal: true,
            frame: false,
            hideLabel: true,
            closeable: true,
            html: '<iframe id="iframeModule" class="mysched_iframeModule" src="' + externLinks.curriculumLink + '&nrmni=' + subjectNo.toUpperCase() + '"></iframe>'
        });

        modulewin.show();
    },
    showSubjectNoMenu: function(subjects, e)
    {
    	subjectNo = MySched.Mapping.getSubjectNo(subjects.keys[0]);
    	
	    destroyMenu();

	    var menuItems = [];
	    
	    for (var subject in subjects.map)
	    {
	    	if(Ext.isString(subject))
	    	{
		    	if(subjects.map[subject] != "removed")
		    	{
		    		menuItems[menuItems.length] = {
		    			id: MySched.Mapping.getSubjectNo(subject),
				        text: MySched.Mapping.getSubjectName(subject),
				        icon: MySched.mainPath + "images/clasIcon.png",
				        handler: function (element, event)
				        {
				        	MySched.SelectionManager.showSubjectWindow(element.id);
				        },
				        xtype: "button"
				    }
		    	}
	    	}
	    }	
	   
	    var menu = Ext.create('Ext.menu.Menu',
	    {
	        id: 'subjectNoMenu',
	        items: menuItems
	    });

	    if (menuItems.length > 0)
	    {
	    	if(menuItems.length == 1)
	    	{
	            var subjectNo = MySched.Mapping.getSubjectNo(subjects.keys[0]);
	            
	            if (subjectNo == subjects.keys[0])
	            {
	                Ext.Msg.alert(
	                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NOTICE,
	                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_MODULENR_UNKNOWN);
	                return;
	            }

	            this.showSubjectWindow(subjectNo);
	    	}
	    	else
	    	{
	    		menu.showAt(e.getXY());	
	    	}
	    }
	    else
	    {
	    	if (!Ext.isString(subjects.keys[0]))
            {
                Ext.Msg.alert(
                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NOTICE,
                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_MODULENR_UNKNOWN);
                return;
            }
	    }
    },
    showInformation: function (e)
    {
        if (typeof e == "undefined")
        {
            var id = "";
            if (this.selectLectureId)
            {
                id = this.selectLectureId;
                var el = Ext.get(id);
            }
            else
            {
                var el = this.selectEl;
                id = el.id;
            }
        }
        else
        {
            var el = e.getTarget('.lectureBox', 5, true);
        }

        if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip')
            .destroy();

        var xy = el.getXY();
        xy[0] = xy[0] + el.getWidth() + 10;

        var l = MySched.selectedSchedule.getLecture(el.id);
        var title = l.data.desc;
        if (l.longname != "") title = l.longname

        var ttInfo = Ext.create('Ext.tip.ToolTip',
        {
            title: '<div class="mySched_lesson_tooltip"> ' + l.data.desc + " " + '</div>',
            id: 'content-anchor-tip',
            target: el.id,
            anchorToTarget: true,
            html: l.showInfoPanel(),
            autoHide: false,
            cls: "mySched_tooltip_index"
        });

        ttInfo.showAt(xy);
    },
    /**
     * Wenn das MouseOver Event ausgeloest wurde
     * 
     * @param {Object}
     *            e Event
     */
    onMouseOver: function (e)
    {
        // Ermittelt Aktive Veranstaltung
        var el = e.getTarget('.lectureBox', 5, true);
        if (el.id.substr(0, 4) != "delta" && MySched.Authorize.user != null && MySched.Authorize.role != "user")
        {
            this.selectLectureId = el.id;
            // Wenn Veranstaltung vorhanden, setze HoverButton
            // auf Entfernen
            if (MySched.Schedule.lectureExists(el.id))
            {
                this.selectButton.dom.src = this.lectureRemoveButton;
                this.selectButton.dom.qtip = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE_LESSON_REMOVE;
            }
            // Zeige HoverButton an
            this.selectButton.show()
                .alignTo(el, 'tr-tr', [-4, 5]);
        }
    },
    /**
     * Wenn das MouseOut Event ausgeloest wurde
     * 
     * @param {Object}
     *            e Event
     */
    onMouseOut: function (e)
    {
        var el = Ext.get(e.getRelatedTarget());
        // Blendet HoverButton aus, und resetet ihn auf
        // hinzufuegen
        if (!el || el.id != 'lectureSelectButton')
        {
            this.selectButton.hide();
            this.selectButton.dom.src = this.lectureAddButton;
            this.selectLectureId = null;
            this.selectButton.dom.qtip = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE_LESSON_ADD;
        }
    },
    /**
     * Beim klick auf dem HoverButton ausgeloest, be DD oder
     * Klick auf einen Button Entfernt oder Fuegt Veranstaltung
     * hinzu
     */
    lecture2ScheduleHandler: function ()
    {
        // Aktion ueber HoverButton ausgeloest
        if (this.selectLectureId)
        {
            var id = this.selectLectureId;
            var el = Ext.get(id);
            // oder ueber DD oder ButtonLeiste
        }
        else
        {
            var el = this.selectEl;
            var id = el.id;
        }
        
        var tabID = id.split('##')[0];
        var lessonID = id.split('##')[1];
        
        if (el.id.substr(0, 4) != "delta" && MySched.Authorize.user != null && MySched.Authorize.role != "user")
        {
            // Entfernt Veranstaltung
            if (el.hasCls('lectureBox_cho') || tabID == 'mySchedule')
            {
                if (typeof MySched.Base.getLecture(lessonID) != "undefined")
                {
                	MySched.Schedule.removeLecture(MySched.Base.getLecture(lessonID));
                }
                else
                {
                	MySched.Schedule.removeLecture(MySched.Schedule.getLecture(lessonID));
                }
                // Minus Icon kann ueber mouseout nicht mehr
                // ausgeblendet werden -> Also Manuell
                /*
                 * this.selectButton.hide();
                 * this.selectButton.dom.src =
                 * this.lectureAddButton;
                 */
                this.selectLectureId = null;
                // this.selectButton.dom.qtip = 'Veranstaltung
                // Ihrem Stundenplan hinzuf&uuml;gen';
                this.fireEvent("lectureDel", el);
                // Fuegt Veranstaltung hinzu
            }
            else
            {
            	var lectureToAdd = MySched.Base.getLecture(lessonID);
                MySched.Schedule.addLecture(lectureToAdd);
                this.fireEvent("lectureAdd", el);
            }

            el.toggleCls('lectureBox_cho');

            // Refresh
            MySched.selectedSchedule.refreshView();
            MySched.Schedule.refreshView();
        }
    },
    /**
     * Edit a Lesson
     */
    editLesson: function ()
    {
        if (this.selectLectureId)
        {
            var id = this.selectLectureId;
            var el = Ext.get(id);
            // oder ueber DD oder ButtonLeiste
        }
        else
        {
            var el = this.selectEl;
            var id = el.id;
        }
        var lesson = MySched.Base.getLecture(id);
        newPEvent(numbertoday(lesson.data.dow),
        lesson.data.stime, lesson.data.etime,
        lesson.data.subject, lesson.data.teacher.replace(/\s+/g, ','), lesson.data.module.replace(/\s+/g, ','), lesson.data.room.replace(/\s+/g, ','), lesson.data.lock,
        lesson.data.key);
    },
    /**
     * Delete a Lesson
     */
    deleteLesson: function (id)
    {
        if (!id)
        {
            if (this.selectLectureId)
            {
                id = this.selectLectureId;
                var el = Ext.get(id);
                // oder ueber DD oder ButtonLeiste
            }
            else
            {
                var el = this.selectEl;
                id = el.id;
            }
        }

        var tab = MySched.layout.tabpanel.getComponent(MySched.Base.schedule.getLecture(id)
            .data.responsible);
        if (tab) tab.mSchedule.removeLecture(tab.mSchedule.getLecture(id));
        MySched.selectedSchedule.removeLecture(MySched.selectedSchedule.getLecture(id));
        MySched.Schedule.removeLecture(MySched.Schedule.getLecture(id));
        MySched.responsibleChanges.removeLecture(MySched.responsibleChanges.getLecture(id));
        MySched.Base.schedule.removeLecture(MySched.Base.schedule.getLecture(id));

        // Minus Icon kann ueber mouseout nicht mehr
        // ausgeblendet werden -> Also Manuell
        this.selectButton.hide();
        this.selectButton.dom.src = this.lectureAddButton;
        this.selectLectureId = null;
        this.selectButton.dom.qtip = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE_LESSON_ADD;
        this.fireEvent("lectureDel", el);

        // Refresh
        MySched.selectedSchedule.refreshView();
        MySched.Schedule.refreshView();
    },
    /**
     * MouseDown Event ausgeloest
     * 
     * @param {Object}
     *            e Event
     */
    onMouseDown: function (e)
    {
        var el = e.getTarget('.lectureBox', 5, true)
        if (el == null) return; // Element ist schon selektiert
        // Selektiere Element
        if (!Ext.isEmpty(this.selectEl)) this.unselect(this.selectEl);
        this.select(el)
        this.selectEl = el;

    },
    ondblclick: function (e)
    {
        this.lecture2ScheduleHandler();
    },
    /**
     * Waehlt Veranstaltung aus
     * 
     * @param {Object}
     *            el Veranstaltungselement
     */
    select: function (el)
    {
        if (this.fireEvent("beforeSelect", el) === false) return el.addClass('lectureBox_sel');

        this.fireEvent("select", el); // Aboniert Events f�r
        // Teacherentennamen
    },
    /**
     * Waehlt Veranstaltung ab
     * 
     * @param {Object}
     *            el Veranstaltungselement
     */
    unselect: function (el)
    {
        if (el == null) if (this.selectEl) el = this.selectEl;
        else return false;
        if (this.fireEvent("beforeUnselect", el) === false) return el.removeClass('lectureBox_sel');
        this.fireEvent("unselect", el);
    }
});

function stripHTML(oldString)
{

    var newString = "";
    var inTag = false;
    for (var i = 0; i < oldString.length; i++)
    {

        if (oldString.charAt(i) == '<') inTag = true;
        if (oldString.charAt(i) == '>')
        {
            if (oldString.charAt(i + 1) == "<")
            {
                // dont do anything
            }
            else
            {
                inTag = false;
                i++;
            }
        }

        if (!inTag) newString += oldString.charAt(i);

    }

    return newString;
}

function gotoExtURL(url, text)
{
    if (Ext.getCmp('content-anchor-tip')) Ext.getCmp('content-anchor-tip')
        .destroy();
    var tabs = MySched.layout.tabpanel.items.items;
    var tosave = false;
    var bla = document;

    var myschedextwin = Ext.DomQuery.select('iframe[id=MySchedexternURL]',
    document);
    var myschedmainwin = Ext.DomQuery.select('div[id=MySchedMainW]', document);

    for (var i = 0; i < tabs.length; i++)
    {
        if (tabs[i].mSchedule.status == "unsaved")
        {
            tosave = true;
            Ext.Msg.show(
            {
                title: "",
                buttons: Ext.Msg.YESNOCANCEL,
                buttonText: {
                    cancel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CANCEL
                },
                msg: text + "<br/>" + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CHANGES_SAVE,
                width: 400,
                modal: true,
                cls: "mySched_gotoMessage_index",
                fn: function (btn)
                {
                    if (btn == "cancel")
                    {
                        Ext.MessageBox.hide();
                        return;
                    }
                    if (btn == "yes")
                    {
                        var temptabs = MySched.layout.tabpanel.items.items;
                        for (var ti = 0; ti < temptabs.length; ti++)
                        {
                            if (temptabs[ti].mSchedule.status == "unsaved")
                            {
                                if (temptabs[ti].mSchedule.id == "mySchedule") temptabs[ti].mSchedule.save(
                                _C('ajaxHandler'), false, "UserSchedule.save");
                                else temptabs[ti].mSchedule.save(
                                _C('ajaxHandler'), false, "ScheduleChanges.save");
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
    if (!tosave)
    {
        Ext.Msg.show(
        {
            title: "",
            buttons: Ext.Msg.OKCANCEL,
            buttonText: {
                cancel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CANCEL
            },
            msg: text,
            width: 400,
            modal: true,
            cls: "mySched_gotoMessage_index",
            fn: function (btn)
            {
                if (btn == "cancel")
                {
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

function showLessonMenu(e)
{
    e.stopEvent();
    var el = e.getTarget('.lectureBox', 5, true);
    var lesson = MySched.Base.getLecture(el.id);
    if (typeof lesson == "undefined") lesson = MySched.Schedule.getLecture(el.id);

    destroyMenu();

    var editLesson = {
        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CHANGE,
        icon: MySched.mainPath + "images/icon-edit.png",
        handler: function ()
        {
        	destroyMenu();
            MySched.SelectionManager.editLesson();
        },
        xtype: "button"
    }

    var deleteLesson = {
        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELETE,
        icon: MySched.mainPath + "images/icon-delete.png",
        handler: function ()
        {
        	destroyMenu();
            MySched.SelectionManager.deleteLesson();
        },
        xtype: "button"
    }

    var addLesson = {
        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ADD,
        icon: MySched.mainPath + "images/add.png",
        handler: function ()
        {
        	destroyMenu();
            MySched.SelectionManager.selectEl = el;
            MySched.SelectionManager.lecture2ScheduleHandler();
        },
        xtype: "button"
    }

    var delLesson = {
        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_REMOVE,
        icon: MySched.mainPath + "images/delete.png",
        handler: function ()
        {
        	destroyMenu();
            MySched.SelectionManager.selectEl = el;
            MySched.SelectionManager.lecture2ScheduleHandler();
        },
        xtype: "button"
    }

    var estudyLesson = {
        text: "eStudy",
        icon: MySched.mainPath + "images/estudy_logo.jpg",
        handler: function (element, event)
        {
            MySched.SelectionManager.showModuleInformation(this);
        },
        scope: el,
        xtype: "button"
    }

    var infoLesson = {
        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_INFORMATION,
        icon: MySched.mainPath + "images/information.png",
        handler: function (element, event)
        {
            MySched.SelectionManager.showModuleInformation(this);
        },
        scope: el,
        xtype: "button"
    }

    var menuItems = [];

    if (MySched.Authorize.role != "user")
    {
        // menuItems[menuItems.length] = estudyLesson;
        if (MySched.selectedSchedule.id == "mySchedule" || el.hasCls('lectureBox_cho'))
        {
            menuItems[menuItems.length] = delLesson;
        }
        else
        {
            if (MySched.selectedSchedule.type != 'delta')
            {
                menuItems[menuItems.length] = addLesson;
            }
        }

    }
    if (((lesson.data.owner == MySched.Authorize.user || MySched.Authorize.isClassSemesterAuthor()) && lesson.data.owner != null) && lesson.data.owner != "gpuntis")
    {
        menuItems[menuItems.length] = editLesson;
        menuItems[menuItems.length] = deleteLesson;
    }

    menuItems[menuItems.length] = infoLesson;

    var menu = Ext.create('Ext.menu.Menu',
    {
        id: 'ownerMenu',
        items: menuItems
    });

    if (menuItems.length > 0)
    {
    	menu.showAt(e.getXY());
    }
}

function showBlockMenu(e)
{
    e.stopEvent();

    destroyMenu();

    var el = e.getTarget('.conMenu', 5, true);
    MySched.BlockMenu.stime = el.getAttribute("stime");
    MySched.BlockMenu.etime = el.getAttribute("etime");
    MySched.BlockMenu.day = numbertoday(el.dom.cellIndex);

    var menu = Ext.create('Ext.menu.Menu',
    {
        id: 'responsibleMenu',
        items: MySched.BlockMenu.Menu
    });

    if (MySched.BlockMenu.Menu.length > 0) menu.showAt(e.getXY());
}

function destroyMenu()
{
	var rMenu = Ext.getCmp('responsibleMenu');
    var oMenu = Ext.getCmp('ownerMenu');
    var sMenu = Ext.getCmp('subjectNoMenu');
    if (Ext.isDefined(rMenu))
    {
    	rMenu.destroy();
    }
    
    if (Ext.isDefined(oMenu))
    {
    	oMenu.destroy();
    }
    
    if (Ext.isDefined(sMenu))
    {
    	sMenu.destroy();
    }
}

/**
 * Ist fuer die Verwaltung und Erstellung der Uebersichtslisten zustaendig
 */
MySched.TreeManager = function ()
{
    var teacherTree, roomTree, clasTree, curteaTree; // neu

    return {
        /**
         * Initialisierung
         */
        init: function ()
        {
            this.teacherTree = new MySched.Collection();
            this.roomTree = new MySched.Collection();
            this.clasTree = new MySched.Collection();
            this.curteaTree = new MySched.Collection(); // neu
        },
        afterloadEvents: function (arr, refresh)
        {
            for (var e in arr)
            {
                if (Ext.isObject(arr[e]))
                {
                    var event = new mEvent(arr[e].eid, arr[e]);
                    MySched.eventlist.addEvent(event);
                }
            }
        },
        /**
         * Fuegt den Listen eine Veranstaltung hinzu
         * 
         * @param {Object}
         *            lecture Veranstaltung
         */
        add: function (lecture)
        {
            if (Ext.isObject(lecture))
            {
                this.teacherTree.addAll(lecture.getTeacher()
                    .asArray());
                this.roomTree.addAll(lecture.getRoom()
                    .asArray());
                this.clasTree.addAll(lecture.getClas()
                    .asArray());
            }
        },
        /**
         * Erstellt die Teacherenten Uebersichtsliste
         * 
         * @param {Object}
         *            tree Basis Tree dem die Liste hinzugefuegt wird
         */
        createTeacherTree: function (tree)
        {
            return this.createTree(
            tree, 'teacher',
            this.teacherTree,
            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_TEACHER);
        },
        /**
         * Erstellt die Raum Uebersichtsliste
         * 
         * @param {Object}
         *            tree Basis Tree dem die Liste hinzugefuegt wird
         */
        createRoomTree: function (tree)
        {
            return this.createTree(tree, 'room', this.roomTree,
            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_ROOM);
        },
        /**
         * Erstellt die Studiengang Uebersichtsliste
         * 
         * @param {Object}
         *            tree Basis Tree dem die Liste hinzugefuegt wird
         */
        createClasTree: function (tree)
        {
            return this.createTree(
            tree, 'module',
            this.clasTree,
            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_SEMESTER);
        },
        /**
         * Erstellt die �nderungen
         * 
         * @param {Object}
         *            tree Basis Tree dem die Liste hinzugefuegt wird
         */
        createDiffTree: function (tree)
        {
            return this.createTree(tree, 'diff');
        },
        /**
         * Erstellt die �nderungen von Verantwortlichen
         * 
         * @param {Object}
         *            tree Basis Tree dem die Liste hinzugefuegt wird
         */
        createrespChangesTree: function (tree)
        {
            return this.createTree(tree, 'respChanges');
        },
        /**
         * Sucht alle Lessons
         * 
         * @param {Object}
         *            tree Basis Tree dem die Liste hinzugefuegt wird
         */
        createCurteaTree: function (tree)
        { // neu->
            return this.createTree(
            tree, 'curtea',
            this.curteaTree,
            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_CURRICULUM); // UnsetTimes
        },

        processTreeData: function (json, type, accMode, name, baseTree)
        {
            var treeData = json["treeData"];
            /*
             * if (accMode != 'none') { treeRoot.appendChild(children); }
             */

            for (var item in treeData)
            {
                if (Ext.isObject(treeData[item]))
                {
                    for (var childitem in treeData[item])
                    {
                        if (Ext.isObject(treeData[item][childitem]))
                        {
                            MySched.Mapping[item].add(
                            childitem,
                            treeData[item][childitem]);
                        }
                    }
                }
            }
        },
        /**
         * Erstellt eine Uebersichtsliste
         * 
         * @param {Object}
         *            baseTree Baum dem die Liste hinzugefuegt wird
         * @param {Object}
         *            type Typ der Liste (teacher|module|room)
         * @param {Object}
         *            data Daten Baum mit Elementen zum Hinzufuegen
         * @param {Object}
         *            name Name der Listengruppe
         */
        createTree: function (baseTree, type, data, name)
        {

            // Generelle Rechteuberpruefung auf diese Uebersichtsliste
            var accMode = MySched.Authorize.checkAccessMode(type);

            if (type != "diff" && type != "respChanges" && type != "curtea")
            {
                if (checkStartup("TreeView.load") === true)
                {
                    MySched.TreeManager.processTreeData(
                    MySched.startup["TreeView.load"].data, type,
                    accMode, name, baseTree);
                }
                else Ext.Ajax.request(
                {
                    url: _C('ajaxHandler'),
                    method: 'POST',
                    params: {
                        type: type,
                        semesterID: MySched.class_semester_id,
                        scheduletask: "TreeView.load"
                    },
                    failure: function (response)
                    {
                        var bla = response;
                    },
                    success: function (response)
                    {
                        try
                        {
                            var json = Ext.decode(response.responseText);
                            MySched.TreeManager.processTreeData(json, type,
                            accMode, name, baseTree);
                        }
                        catch (e)
                        {}
                    }
                });
            }

            if (type == "curtea")
            { // neu->
                MySched.TreeManager.processTreeData(
                MySched.startup["TreeView.curiculumTeachers"].data,
                type, accMode, name, baseTree);
                return ret;
            }

            // Keine Rechte, also nicht anzeigen
            if (accMode == 'none') return null;

            if (type == "diff")
            {
                // Fuegt die Liste der Uebersicht an
                var ret = baseTree.root.appendChild(
                {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_CENTRAL,
                    id: 'delta',
                    cls: type + '-root',
                    draggable: false,
                    leaf: true
                });
                return ret;
            }

            if (type == "respChanges")
            {
                // Fuegt die Liste der Uebersicht an
                var ret = baseTree.root.appendChild(
                {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_OWN,
                    id: 'respChanges',
                    cls: type + '-root',
                    draggable: false,
                    leaf: true
                });
                return ret;
            }
        }
    }
}();

/**
 * Layouterstellung und Verwaltung
 */
MySched.layout = function ()
{
    var tabpanel, selectedTab, w_leftMenu, w_topMenu, w_infoPanel, infoWindow;

    return {
        /**
         * Gibt den Ausgewaehlten Tab zurueck
         */
        getSelectedTab: function ()
        {
            return this.selectedTab;
        },
        /**
         * Erstellt das Grundlayout
         */
        buildLayout: function ()
        {
            // Erstellt TabPanel
            this.tabpanel = Ext.create('Ext.tab.Panel',
            {
                resizeTabs: false,
                // turn on tab resizing
                // minTabWidth: 155,
                // tabWidth: 155,
                // heigth: 500,
                enableTabScroll: true,
                id: 'tabpanel',
                region: 'center'
            });

            this.tabpanel.on('tabchange',
	            function (panel, o)
	            {
	                showLoadMask();
	                var contentAnchorTip = Ext.getCmp('content-anchor-tip');
	                if (contentAnchorTip) contentAnchorTip.destroy();
	                MySched.selectedSchedule = o.mSchedule;
	
	                var weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker').value);
	
	                var currentMoFrDate = getCurrentMoFrDate();
	                var selectedSchedule = MySched.selectedSchedule;
	                var nodeKey = selectedSchedule.key;
	                var nodeID = selectedSchedule.id;
	                var gpuntisID = selectedSchedule.gpuntisID;
	                var semesterID = selectedSchedule.semesterID;
	                var plantypeID = "";
	                var type = selectedSchedule.type;
	
	                if (MySched.Schedule.status == "unsaved")
	                {
	                	Ext.ComponentMgr.get('btnSave').enable();
	                }
	                else
	                {
	                	Ext.ComponentMgr.get('btnSave').disable();
	                }
	                
	                if(MySched.selectedSchedule.id != "mySchedule")
	                {
		                if (MySched.loadLessonsOnStartUp == false)
		                {
		                    Ext.Ajax.request(
		                    {
		                        url: _C('ajaxHandler'),
		                        method: 'POST',
		                        params: {
		                            nodeID: nodeID,
		                            nodeKey: nodeKey,
		                            gpuntisID: gpuntisID,
		                            semesterID: semesterID,
		                            scheduletask: "Ressource.load",
		                            plantypeID: plantypeID,
		                            type: type,
		                            startdate: Ext.Date.format(currentMoFrDate.monday, "Y-m-d"),
		                            enddate: Ext.Date.format(currentMoFrDate.friday, "Y-m-d")
		                        },
		                        failure: function (response)
		                        {
		                            Ext.Msg.alert(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR,
		                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_ERROR);
		                        },
		                        success: function (response)
		                        {
		                            var json = Ext.decode(response.responseText);
		                            var lessonData = json["lessonData"];
		                            var lessonDate = json["lessonDate"];
		                            for (var item in lessonData)
		                            {
		                                if (Ext.isObject(lessonData[item]))
		                                {
		                                    var record = new mLecture(
		                                    item,
		                                    lessonData[item], semesterID,
		                                    plantypeID);
		                                    MySched.Base.schedule.addLecture(record);
		                                    //															MySched.TreeManager.add(record);
		                                }
		                            }
		                            if (Ext.isObject(lessonDate))
		                            {
		                                MySched.Calendar.addAll(lessonDate);
		                            }
		
		                            MySched.selectedSchedule.eventsloaded = null;
		                            MySched.selectedSchedule.init(type, nodeKey, semesterID);
		                            // Aufgerufener Tab wird neu geladen
		                            if (MySched.Schedule.status == "unsaved")
		                            {
		                                Ext.ComponentMgr.get('btnSave').enable();
		                            }
		                            else
		                            {
		                                Ext.ComponentMgr.get('btnSave').disable();
		                            }
		
		                            var lectureData = MySched.selectedSchedule.data.items;
		
		                            for (var lectureIndex = 0; lectureIndex < lectureData.length; lectureIndex++)
		                            {
		                                if (Ext.isDefined(lectureData[lectureIndex]))
		                                {
		                                    if (Ext.isDefined(lectureData[lectureIndex].setCellTemplate) == true)
		                                    {
		                                        lectureData[lectureIndex].setCellTemplate(MySched.selectedSchedule.type);
		                                    }
		                                }
		                            }
		
		                            MySched.selectedSchedule.eventsloaded = null;
		                            o.mSchedule.refreshView();
		
		                            // Evtl. irgendwo haengender AddLectureButton
		                            // wird ausgeblendet
		                            /* MySched.SelectionManager.selectButton.hide(); */
		                            MySched.SelectionManager.unselect();
		                            this.selectedTab = o;
		                        }
		                    });
		                }
		                else
		                {
		                    MySched.selectedSchedule.eventsloaded = null;
		                    MySched.selectedSchedule.init(type, nodeKey, semesterID);
		                    // Aufgerufener Tab wird neu geladen
		                    if (MySched.Schedule.status == "unsaved")
		                    {
		                        Ext.ComponentMgr.get('btnSave').enable();
		                    }
		                    else
		                    {
		                        Ext.ComponentMgr.get('btnSave').disable();
		                    }
		
		                    var lectureData = MySched.selectedSchedule.data.items;
		
		                    for (var lectureIndex = 0; lectureIndex < lectureData.length; lectureIndex++)
		                    {
		                        if (Ext.isDefined(lectureData[lectureIndex]))
		                        {
		                            if (Ext.isDefined(lectureData[lectureIndex].setCellTemplate) == true)
		                            {
		                                lectureData[lectureIndex].setCellTemplate(MySched.selectedSchedule.type);
		                            }
		                        }
		                    }
		
		                    MySched.selectedSchedule.eventsloaded = null;
		                    o.mSchedule.refreshView();
		
		                    // Evtl. irgendwo haengender AddLectureButton
		                    // wird ausgeblendet
		                    /* MySched.SelectionManager.selectButton.hide(); */
		                    MySched.SelectionManager.unselect();
		                    this.selectedTab = o;
		                }
	                }
	                else
	                {
	                	MySched.Schedule.refreshView();
	                }
	            }, this
	        );

            // Wenn der Header der FH angezeigt werden soll
            if (_C('showHeader'))
            {
                this.w_topMenu = Ext.create('Ext.Panel',
                {
                    id: 'topMenu',
                    region: 'north',
                    bodyStyle: 'text-align:center;',
                    html: _C('headerHTML'),
                    bbar: this.getMainToolbar()
                });
                // ..und wenn nicht
            }
            else
            {
                this.w_topMenu = Ext.create('Ext.Panel',
                {
                    id: 'topMenu',
                    region: 'north',
                    bbar: this.getMainToolbar()
                });
            }

            var treeData = MySched.Tree.init();
    		            
            // Linker Bereich der Info und Ubersichtsliste enthaelt
            this.w_leftMenu = Ext.create('Ext.panel.Panel',
            {
                id: 'leftMenu',
                title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_LOADING,
                region: 'center',
                split: false,
                width: 242,
                minSize: 242,
                maxSize: 242,
                collapsible: false,
                collapsed: false,
                autoScroll: true,
                headerCfg: {
                    tag: '',
                    cls: 'x-panel-header mySched_techheader',
                    // Default class not applied if Custom
                    // element specified
                    html: ''
                },
                items: [treeData]
            });

            this.w_leftMenu.on("expand", function ()
            {
                if (MySched.selectedSchedule)
                {
                    MySched.selectedSchedule.eventsloaded = null;
                    MySched.selectedSchedule.refreshView();
                }
            });
            this.w_leftMenu.on("collapse", function ()
            {
                if (MySched.selectedSchedule)
                {
                    MySched.selectedSchedule.eventsloaded = null;
                    MySched.selectedSchedule.refreshView();
                }
            });

            this.rightviewport = Ext.create('Ext.Panel',
            {
                id: "rightviewport",
                region: 'center',
                items: [this.w_topMenu, this.tabpanel]
            });

            this.leftviewport = Ext.create('Ext.Panel',
            {
                id: "leftviewport",
                region: 'west',
                items: [this.w_leftMenu]
            });

            // und schliesslich erstellung des gesamten Layouts
            this.viewport = Ext.create('Ext.panel.Panel',
            {
                id: "viewport",
                layout: "border",
                renderTo: "MySchedMainW",
                width: 968,
                height: 500,
                minSize: 968,
                maxSize: 968,
                items: [this.leftviewport, this.rightviewport]
            });
            
    		loadMask = new Ext.LoadMask(
    	    "selectTree",
    	    {
    	        msg: "Loading..."
    	    });
    		loadMask.show();

            var calendar = Ext.ComponentMgr.get('menuedatepicker');
            if (calendar) var imgs = Ext.DomQuery.select('img[class=x-form-trigger x-form-date-trigger]',
            calendar.container.dom);
            for (var i = 0; i < imgs.length; i++)
            {
                imgs[i].alt = "calendar";
            }
        },
        /**
         * Zeigt das Infofenster von MySched an
         */
        showInfoWindow: function ()
        {
            if (Ext.ComponentMgr.get("infoWindow") == null || typeof Ext.ComponentMgr.get("infoWindow") == "undefined")
            {

                this.infoWindow = Ext.create('Ext.Window',
                {
                    id: 'infoWindow',
                    title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_INFO_WINDOW_TITLE,
                    width: 675,
                    height: 380,
                    frame: false,
                    bodyStyle: 'background-color: #FFF; padding: 7px;',
                    buttons: [
                    {
                        text: 'Schlie&szlig;en',
                        handler: function ()
                        {
                            this.infoWindow.close();
                        },
                        scope: this
                    }],
                    html: "<small style='float:right; font-style:italic;'>Version " + MySched.version + "</small>" + "<p style='font-weight:bold;'>&Auml;nderungen sind farblich markiert: <br /> <p style='padding-left:10px;'> <span style='background-color: #00ff00;' >Neue Veranstaltung</span></p> <p style='padding-left:10px;'><span style='background-color: #ff4444;' >Gel&ouml;schte Veranstaltung</span></p> <p style='padding-left:10px;'><span style='background-color: #ffff00;' >Ge&auml;nderte Veranstatung (neuer Raum, neuer Teacherent)</span> </p><p style='padding-left:10px;'> <span style='background-color: #ffaa00;' >Ge&auml;nderte Veranstaltung (neue Zeit:von)</span>, <span style='background-color: #ffff00;' >Ge&auml;nderte Veranstaltung (neue Zeit:zu)</span></p></p>" + "<b>Version: 2.1.6:</b>" + "<ul>" + "<li style='padding-left:10px;'>NEU: Hinzuf&uuml;gen der Veranstaltungen &uuml;ber Kontextmenu (Rechtsklick auf Veranstaltung).</li>" + "<li style='padding-left:10px;'>NEU: Hinzuf&uuml;gen von eigenen Veranstaltungen &uuml;ber Kontextmenu (Rechtsklick in einen Block).</li>" + "<li style='padding-left:10px;'>NEU: Navigation &uuml;ber den Teacherent, Raum, Fachbereich einer Veranstaltung.</li>" + "<li style='padding-left:10px;'>NEU: Navigation durch einzelne Wochen &uuml;ber einen Kalender (Men&uuml;leiste).</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Anzeige von Terminen.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Informationen zu Terminen &uuml;ber Termintitel (Mauszeiger &uuml;ber Titel).</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Informationen zu Veranstaltungen &uuml;ber Veranstaltungstitel (Klick auf den Titel).</li>" + "</ul>" + "<br/>" + "<b>Version: 2.1.5:</b>" + "<ul>" + "<li style='padding-left:10px;'>NEU: Pers&ouml;nliche Termine k&ouml;nnen &uuml;ber den Men&uuml;punkt 'Neuer Termin' oder per Klick in einen Block angelegt werden.</li>" + "<li style='padding-left:10px;'>NEU: Berechtigte Benutzer d&uuml;rfen im Panel 'Einzel Termine' neue Termine anlegen oder alte editieren.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: &Auml;nderungen werden wie ein Stundenplan aufgerufen.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Nur registrierte Benutzer haben Zugriff auf alle Funktionen.</li>" + "</ul>" + "<br/>" + "<b>Version: 2.1.4:</b>" + "<li style='padding-left:10px;'>NEU: In der Infoanzeige von Veranstaltungen kann die Modulbeschreibung abgerufen werden.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Termine werden nur noch an betroffenen Tagen angezeigt.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Termine werden bei Klick auf das orangene Ausrufezeichen angezeigt.</li>" + "<br/>" + "<b>Version: 2.1.3:</b>" + "" + "<li style='padding-left:10px;'>NEU: Stundenpl&auml;ne k&ouml;nnen als Terminkalendar heruntergeladen werden. (Men&uuml;punkt ICal Download)</li>" + "<li style='padding-left:10px;'>NEU: Navigationsleiste kann eingeklappt werden.</li>" + "<li style='padding-left:10px;'>NEU: Veranstaltungen k&ouml;nnen per Doppelklick hinzugef&uuml;gt / entfernt werden.</li>" + "<li style='padding-left:10px;'>NEU: Bei &Auml;nderungen zu Ihrem abgespeicherten Plan werden jetzt sinnvolle Vorschl&auml;ge gemacht.</li>" + "<li style='padding-left:10px;'>NEU: Kontrastreiche Men&uuml;s, sinnvollere Neuanordnung des Men&uuml;s.</li>" + "<li style='padding-left:10px;'>NEU: Seitentitel auch als Titel des pdf-download.</li>" + "<li style='padding-left:10px;'>NEU: Kleinere Texte bei den Einzelterminen.</li>" + "<br/>" + "<b>Version: 2.1.2:</b>" + "" + "<li style='padding-left:10px;'>NEU: MNI Style</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: PDF Download und PDF Dateiname bezieht sich auf den aktiven Tab</li>"
                });
                this.infoWindow.show();
            }
        },
        /**
         * Erstellt einen neuen Stundenplan Tab
         * 
         * @param {Object}
         *            id ID des Tabs
         * @param {Object}
         *            title Title des Tabs
         * @param {Object}
         *            grid Grid das angezeigt werden soll
         */
        createTab: function (id, title, grid, type, closeable)
        {
            if (closeable != false) closeable = true;
            var tab = null;
            if (MySched.Authorize.role == "user" && id == "mySchedule")
            {
                // DO NOTHING
            }
            else
            {
                if (!(tab = this.tabpanel.getComponent(id)))
                {
                    if (type)
                    {

                        var lectureData = grid.mSchedule.data.items;

                        for (var lectureIndex = 0; lectureIndex < lectureData.length; lectureIndex++)
                        {
                            if (Ext.isDefined(lectureData[lectureIndex]))
                            {
                                if (Ext.isDefined(lectureData[lectureIndex].setCellTemplate) == true)
                                {
                                    lectureData[lectureIndex].setCellTemplate(type);
                                }
                            }
                        }

                        /*
                         * grid.mSchedule.data.eachKey(function (k, v) { if
                         * (typeof v.setCellTemplate != "undefined")
                         * v.setCellTemplate(type); });
                         */
                    }
                    if ((MySched.Authorize.role == "user" && type == "delta") || type == "mySchedule")
                    {
                        tab = Ext.apply(
                        // Defaultwerte - wenn schon gesetzt bleiben sie
                        Ext.apply(grid,
                        {
                            cls: 'MySched_ScheduleTab',
                            tabTip: title,
                            closable: false
                        }),
                        {
                            // Diese werden Ueberschrieben, falls sie Existieren
                            id: id,
                            title: title
                        });
                    }
                    else
                    {
                        tab = Ext.apply(
                        // Defaultwerte - wenn schon gesetzt bleiben sie
                        Ext.apply(grid,
                        {
                            cls: 'MySched_ScheduleTab',
                            tabTip: title,
                            closable: closeable
                            // iconCls: type + 'Icon',
                        }),
                        {
                            // Diese werden Ueberschrieben, falls sie Existieren
                            id: id,
                            title: title
                        });
                    }
                    this.tabpanel.add(tab);
                }
                if (Ext.getCmp('content-anchor-tip'))
                {
                	Ext.getCmp('content-anchor-tip').destroy();                	
                }
                	
                MySched.selectedSchedule = tab.mSchedule;
                // Aufgerufener Tab wird neu geladen
                if (MySched.Schedule.status == "unsaved")
                {
                    Ext.ComponentMgr.get('btnSave').enable();
                }
                else
                {
                    Ext.ComponentMgr.get('btnSave').disable();
                }

                MySched.selectedSchedule.eventsloaded = null;
                // tab.mSchedule.refreshView();

                this.selectedTab = tab;

                // Wechselt zum neu erstellten Tab
                this.tabpanel.setActiveTab(tab);
                MySched.Base.regScheduleEvents(id);

                if(this.tabpanel.items.length == 1)
            	{
                   	MySched.selectedSchedule.refreshView();
            	}
            }
        },
        /**
         * Gibt die Toolbar zurueck
         */
        getMainToolbar: function ()
        {
            var btnSave = Ext.create('Ext.Button',
            {
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SAVE,
                id: 'btnSave',
                iconCls: 'tbSave',
                disabled: false,
                hidden: true,
                handler: MySched.Authorize.saveIfAuth,
                scope: MySched.Authorize,
                tooltip: {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_SAVE
                }
            });
            var btnEmpty = Ext.create('Ext.Button',
            {
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EMPTY,
                id: 'btnEmpty',
                iconCls: 'tbEmpty',
                hidden: true,
                disabled: false,
                tooltip: {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_EMPTY
                },
                scope: MySched.selectedSchedule,
                handler: function ()
                {
                    Ext.Msg.confirm(
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_DELETE,
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_DELETE_QUESTION1 + MySched.selectedSchedule.title + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_DELETE_QUESTION2,

                    function (r)
                    {
                        if (r == 'yes')
                        {
                            var lessons = MySched.selectedSchedule.getLectures();
                            var toremove = [];
                            for (var i = 0; i < lessons.length; i++)
                            {
                                if ((lessons[i].data.type == "personal" && ((lessons[i].data.owner == MySched.Authorize.user && lessons[i].data.responsible == MySched.selectedSchedule.id) || MySched.Authorize.isClassSemesterAuthor())) || MySched.selectedSchedule.id == "mySchedule")
                                {
                                    toremove[toremove.length] = lessons[i].data.key;
                                }
                            }
                            for (var i = 0; i < toremove.length; i++)
                            {
                                if (MySched.selectedSchedule.id == "mySchedule")
                                {
                                    MySched.selectedSchedule.removeLecture(MySched.selectedSchedule.getLecture(toremove[i]));
                                }
                                else
                                {
                                    var tab = MySched.layout.tabpanel.getComponent(MySched.Base.schedule.getLecture(toremove[i])
                                        .data.responsible);
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
            });
            
            var disablePDF = true;
            if(MySched.libraryFPDFIsInstalled == true)
            {
            	disablePDF = false
            }
            
            var btnSavePdf = Ext.create('Ext.Button',
            {
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE,
                id: 'btnPdf',
                iconCls: 'tbSavePdf',
                disabled: disablePDF,
                tooltip: {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_PDF_DESC
                },
                handler: function ()
                {
                    clickMenuHandler();
                    var pdfwait = Ext.MessageBox.wait(
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_GENERATED,
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_PDF_CREATE,
                    {
                        interval: 100,
                        duration: 2000
                    });

                    var currentMoFrDate = getCurrentMoFrDate();

                    Ext.Ajax.request(
                    {
                        url: _C('ajaxHandler'),
                        jsonData: MySched.selectedSchedule.exportData("jsonpdf"),
                        method: 'POST',
                        params: {
                            username: MySched.Authorize.user,
                            title: MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' '),
                            what: "pdf",
                            startdate: Ext.Date.format(currentMoFrDate.monday, "Y-m-d"),
                            enddate: Ext.Date.format(currentMoFrDate.friday, "Y-m-d"),
                            scheduletask: "Schedule.export",
                            semesterID: MySched.selectedSchedule.semesterID
                        },
                        scope: pdfwait,
                        failure: function ()
                        {
                            Ext.MessageBox.hide();
                            Ext.Msg.alert(
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_PDF_DOWNLOAD,
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_PDF_ERROR);
                        },
                        success: function (response)
                        {
                            Ext.MessageBox.hide();
                            if (response.responseText != "Permission Denied!")
                            {
                                // IFrame zum downloaden
                                // wird erstellt
                                Ext.core.DomHelper.append(
                                Ext.getBody(),
                                {
                                    tag: 'iframe',
                                    id: 'downloadIframe',
                                    src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=pdf&save=false&scheduletask=Download.schedule",
                                    style: 'display:none;z-index:10000;'
                                });
                                // Iframe wird nach 2
                                // Sec geloescht.
                                var func = function ()
                                {
                                    Ext.get('downloadIframe')
                                        .remove();
                                }
                                Ext.defer(func, 2000);
                            }
                            else
                            {
                                Ext.Msg.alert(
                                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_PDF_DOWNLOAD,
                                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_PDF_ERROR);
                            }
                        }
                    })
                }
            });

            var btnSaveWeekPdf = Ext.create('Ext.Button',
            {
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_WEEK_SCHEDULE,
                id: 'btnWeekPdf',
                iconCls: 'tbSavePdf',
                disabled: true,
                tooltip: {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_WEEK_SCHEDULE_PDF_DESC
                },
                handler: function ()
                {
                    clickMenuHandler();
                    var pdfwait = Ext.MessageBox.wait(
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_WEEK_SCHEDULE_GENERATE,
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_PDF_CREATE,
                    {
                        interval: 100,
                        duration: 2000
                    });

                    Ext.Ajax.request(
                    {
                        url: _C('ajaxHandler'),
                        jsonData: MySched.selectedSchedule.exportAllData(true),
                        method: 'POST',
                        params: {
                            username: MySched.Authorize.user,
                            title: MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' '),
                            what: "pdf",
                            scheduletask: "Schedule.export"
                        },
                        scope: pdfwait,
                        failure: function ()
                        {
                            Ext.MessageBox.hide();
                            Ext.Msg.alert(
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_PDF_DOWNLOAD,
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_PDF_ERROR);
                        },
                        success: function (response)
                        {
                            Ext.MessageBox.hide();
                            if (response.responseText != "Permission Denied!")
                            {
                                // IFrame zum downloaden
                                // wird erstellt
                                Ext.core.DomHelper.append(
                                Ext.getBody(),
                                {
                                    tag: 'iframe',
                                    id: 'downloadIframe',
                                    src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=pdf&save=false&scheduletask=Download.schedule",
                                    style: 'display:none;z-index:10000;'
                                });
                                // Iframe wird nach 2
                                // Sec geloescht.
                                var func = function ()
                                {
                                    Ext.get('downloadIframe')
                                        .remove();
                                }
                                Ext.defer(func, 2000);
                            }
                            else
                            {
                                Ext.Msg.alert(
                                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_PDF_DOWNLOAD,
                                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_PDF_ERROR);
                            }
                        }
                    })
                }
            });

            var disableICal = true;
            if(MySched.libraryiCalcreatorIsInstalled == true)
            {
            	disableICal = false
            }
            
            var btnICal = Ext.create('Ext.Button',
            {
                // ICal DownloadButton
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ICAL,
                id: 'btnICal',
                iconCls: 'tbSaveICal',
                disabled: disableICal,
                tooltip: {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ICAL_DESC
                },
                handler: function ()
                {
                    clickMenuHandler();
                    var icalwait = Ext.MessageBox.wait(
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ICAL_GENERATE,
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ICAL_CREATE,
                    {
                        interval: 100,
                        duration: 2000
                    });
                    Ext.Ajax.request(
                    {
                        url: _C('ajaxHandler'),
                        jsonData: MySched.selectedSchedule.exportData(),
                        method: 'POST',
                        params: {
                            username: MySched.Authorize.user,
                            title: MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' '),
                            what: "ical",
                            scheduletask: "Schedule.export",
                            departmentAndSemester: MySched.departmentAndSemester
                        },
                        scope: icalwait,
                        failure: function (response,
                        ret)
                        {
                            Ext.MessageBox.hide();
                            Ext.Msg.alert(
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ICAL_DOWNLOAD,
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ICAL_DOWNLOAD_ERROR);
                        },
                        success: function (response,
                        ret)
                        {
                            Ext.MessageBox.hide();
                            try
                            {
                                var responseData = new Array();
                                responseData = Ext.decode(response.responseText);
                                if (responseData['url'] != "false")
                                {
                                    Ext.MessageBox.show(
                                    {
                                        minWidth: 500,
                                        title: "Synchronisieren",
                                        msg: '<strong style="font-weight:bold">Link</strong>:<br/>' + responseData['url'] + '<br/>Wollen Sie den Terminkalendar ersetzen?',
                                        buttons: Ext.Msg.YESNO,
                                        fn: function (
                                        btn,
                                        text)
                                        {
                                            if (btn == "yes")
                                            {
                                                // IFrame
                                                // zum
                                                // downloaden
                                                // wird
                                                // erstellt
                                                Ext.core.DomHelper.append(
                                                Ext.getBody(),
                                                {
                                                    tag: 'iframe',
                                                    id: 'downloadIframe',
                                                    src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=ics&save=true&scheduletask=Download.schedule",
                                                    style: 'display:none;z-index:10000;'
                                                });
                                                // Iframe
                                                // wird
                                                // nach
                                                // 2
                                                // Sec
                                                // geloescht.
                                                var func = function ()
                                                {
                                                    Ext.get('downloadIframe')
                                                        .remove();
                                                }
                                                Ext.defer(
                                                func,
                                                2000);
                                            }
                                            else
                                            {
                                                // IFrame
                                                // zum
                                                // downloaden
                                                // wird
                                                // erstellt
                                                Ext.core.DomHelper.append(
                                                Ext.getBody(),
                                                {
                                                    tag: 'iframe',
                                                    id: 'downloadIframe',
                                                    src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=ics&save=false&scheduletask=Download.schedule",
                                                    style: 'display:none;z-index:10000;'
                                                });
                                                // Iframe
                                                // wird
                                                // nach
                                                // 2
                                                // Sec
                                                // geloescht.
                                                var func = function ()
                                                {
                                                    Ext.get('downloadIframe')
                                                        .remove();
                                                }
                                                Ext.defer(
                                                func,
                                                2000);
                                            }
                                        }
                                    });
                                }
                                else
                                {
                                    // IFrame zum
                                    // downloaden wird
                                    // erstellt
                                    Ext.core.DomHelper.append(
                                    Ext.getBody(),
                                    {
                                        tag: 'iframe',
                                        id: 'downloadIframe',
                                        src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=ics&save=false&scheduletask=Download.schedule",
                                        style: 'display:none;z-index:10000;'
                                    });
                                    // Iframe wird nach
                                    // 2 Sec geloescht.
                                    var func = function ()
                                    {
                                        Ext.get('downloadIframe')
                                            .remove();
                                    }
                                    Ext.defer(func,
                                    2000);
                                }
                            }
                            catch (e)
                            {
                                Ext.Msg.alert(
                                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ICAL_DOWNLOAD,
                                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ICAL_DOWNLOAD_ERROR);
                            }
                        }
                    })
                }
            });

            var btnSaveTxt = Ext.create('Ext.Button',
            {
                // TxT DownloadButton
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EXCEL,
                id: 'btnTxt',
                iconCls: 'tbSaveTxt',
                disabled: true,
                tooltip: {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_EXCEL_DESC
                },
                handler: function ()
                {
                    clickMenuHandler();
                    var txtwait = Ext.MessageBox.wait(
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_GENERATED,
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EXCEL_CREATE,
                    {
                        interval: 100,
                        duration: 2000
                    });

                    Ext.Ajax.request(
                    {
                        url: _C('ajaxHandler'),
                        jsonData: MySched.selectedSchedule.exportAllData(),
                        method: 'POST',
                        params: {
                            username: MySched.Authorize.user,
                            title: MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' '),
                            what: "ics",
                            scheduletask: "Schedule.export"
                        },
                        scope: txtwait,
                        failure: function ()
                        {
                            Ext.MessageBox.hide();
                            Ext.Msg.alert(
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NOTICE,
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CREATE_ERROR);
                        },
                        success: function (response)
                        {
                            Ext.MessageBox.hide();
                            if (response.responseText != "Permission Denied!")
                            {
                                // IFrame zum downloaden
                                // wird erstellt
                                Ext.core.DomHelper.append(
                                Ext.getBody(),
                                {
                                    tag: 'iframe',
                                    id: 'downloadIframe',
                                    src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=xls&save=false&scheduletask=Download.schedule",
                                    style: 'display:none;z-index:10000;'
                                });
                                // Iframe wird nach 2
                                // Sec geloescht.
                                var func = function ()
                                {
                                    Ext.get('downloadIframe')
                                        .remove();
                                }
                                Ext.defer(func, 2000);
                            }
                            else
                            {
                                Ext.Msg.alert(
                                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NOTICE,
                                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CREATE_ERROR);
                            }
                        }
                    })
                }
            });

            var btnAdd = Ext.create('Ext.Button',
            {
                // HinzufuegenButton
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ADD,
                id: 'btnAdd',
                iconCls: 'tbAdd',
                disabled: true,
                hidden: true,
                handler: MySched.SelectionManager.lecture2ScheduleHandler,
                scope: MySched.SelectionManager,
                tooltip: {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ADD_LESSON
                }
            });

            var btnMenu = Ext.create('Ext.Button',
            {
                // MenuButton
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD,
                id: 'btnMenu',
                iconCls: 'tbDownload',
                disabled: false,
                menu: [btnSavePdf, btnSaveWeekPdf, btnICal, btnSaveTxt]
            });

            function clickMenuHandler()
            {
                btnMenu.hideMenu();
            }

            var btnDel = Ext.create('Ext.Button',
            {
                // EntfernenButton
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_REMOVE,
                id: 'btnDel',
                iconCls: 'tbTrash',
                hidden: true,
                disabled: true,
                handler: MySched.SelectionManager.lecture2ScheduleHandler,
                scope: MySched.SelectionManager,
                tooltip: {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_REMOVE_LESSON
                }
            });

            var btnInfo = Ext.create('Ext.Button',
            {
                // InfoButton
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_INFO,
                id: 'btnInfo',
                iconCls: 'tbInfo',
                hidden: true,
                handler: MySched.layout.showInfoWindow,
                scope: MySched.layout
            });

            var tbFreeBusy = Ext.create('Ext.Button',
            {
                // Frei/Belegt Button
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_FREE_BUSY,
                id: 'btnFreeBusy',
                iconCls: 'tbFreeBusy',
                hidden: true,
                enableToggle: true,
                pressed: MySched.freeBusyState,
                toggleHandler: MySched.Base.freeBusyHandler,
                tooltip: {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_FREE_BUSY_DESC
                }
            });

            Ext.DatePicker.prototype.startDay = 1;

            var inidate = new Date();

            var menuedatepicker = Ext.create('Ext.form.field.Date',
            {
                id: 'menuedatepicker',
                showWeekNumber: true,
                format: 'd.m.Y',
                useQuickTips: false,
                editable: false,
                value: inidate,
                startDay: 1,
                // disabledDays: [0, 6],
                listeners: {
                    'change': function ()
                    {
                        if (MySched.selectedSchedule != null)
                        {
                            showLoadMask();
                        	if(MySched.selectedSchedule.id != "mySchedule")
        	                {
	                            var weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker').value);
	
	                            var currentMoFrDate = getCurrentMoFrDate();
	                            var selectedSchedule = MySched.selectedSchedule;
	                            var nodeKey = selectedSchedule.key;
	                            var nodeID = selectedSchedule.id;
	                            var gpuntisID = selectedSchedule.gpuntisID;
	                            var semesterID = selectedSchedule.semesterID;
	                            var plantypeID = "";
	                            var type = selectedSchedule.type;
	
	                            if (MySched.loadLessonsOnStartUp == false)
	                            {
	                                Ext.Ajax.request(
	                                {
	                                    url: _C('ajaxHandler'),
	                                    method: 'POST',
	                                    params: {
	                                        nodeID: nodeID,
	                                        nodeKey: nodeKey,
	                                        gpuntisID: gpuntisID,
	                                        semesterID: semesterID,
	                                        scheduletask: "Ressource.load",
	                                        plantypeID: plantypeID,
	                                        type: type,
	                                        startdate: Ext.Date.format(currentMoFrDate.monday, "Y-m-d"),
	                                        enddate: Ext.Date.format(currentMoFrDate.friday, "Y-m-d")
	                                    },
	                                    failure: function (response)
	                                    {
	                                        Ext.Msg.alert(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR,
	                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_ERROR);
	                                    },
	                                    success: function (response)
	                                    {
	                                        var json = Ext.decode(response.responseText);
	                                        var lessonData = json["lessonData"];
	                                        var lessonDate = json["lessonDate"];
	                                        for (var item in lessonData)
	                                        {
	                                            if (Ext.isObject(lessonData[item]))
	                                            {
	                                                var record = new mLecture(
	                                                item,
	                                                lessonData[item], semesterID,
	                                                plantypeID);
	                                                MySched.Base.schedule.addLecture(record);
	                                                //														MySched.TreeManager.add(record);
	                                            }
	                                        }
	                                        if (Ext.isObject(lessonDate))
	                                        {
	                                            MySched.Calendar.addAll(lessonDate);
	                                        }
	
	                                        MySched.selectedSchedule.eventsloaded = null;
	                                        MySched.selectedSchedule.init(type, nodeKey, semesterID);
	                                        MySched.selectedSchedule.refreshView();
	                                    }
	                                });
	                            }
	                            else
	                            {
	                                MySched.selectedSchedule.eventsloaded = null;
	                                MySched.selectedSchedule.init(type, nodeKey, semesterID);
	                                MySched.selectedSchedule.refreshView();
	                            }
        	                }
                        	else
                        	{
                        		MySched.selectedSchedule.eventsloaded = null;
                                MySched.selectedSchedule.refreshView();
                        	}
                        }
                    }
                }
            });

            return [menuedatepicker, btnSave, btnMenu, '->', btnInfo,
            btnEmpty, btnAdd, btnDel];
        }
    };
}();

Ext.form.VTypes['ValidTimeText'] = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_VALID_STARTTIME_LESSER;
Ext.form.VTypes['ValidTime'] = function (arg, field)
{
    if (field.id == "starttiid")
    {
        if (!Ext.getCmp('endtiid')
            .getValue()) return true;
        if (Ext.getCmp('starttiid')
            .getValue() < Ext.getCmp('endtiid')
            .getValue())
        {
            return true;
        }
        return false;
    }
    else
    {
        if (!Ext.getCmp('starttiid')
            .getValue()) Ext.getCmp('starttiid')
            .validate();
        if (Ext.getCmp('starttiid')
            .getValue() < Ext.getCmp('endtiid')
            .getValue())
        {
            Ext.getCmp('starttiid')
                .validate();
            return true;
        }
        return false;
    }

}

function newPEvent(pday, pstime, petime, title, teacher_name, clas_name, room_name,
l, key)
{
    if (l) var lock = l;
    else var lock = MySched.selectedSchedule.type;
    var titel = {
        layout: 'form',
        width: 550,
        labelAlign: 'top',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TITLE,
            width: 525,
            name: 'titel',
            id: 'titelid',
            value: title,
            emptyText: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EMPTY_LESSON_TITLE,
            blankText: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EMPTY_LESSON_TITLE,
            allowBlank: false
        }]
    };

    // Wird erstmal nicht mehr verwendet
    var notice = {
        layout: 'form',
        defaultType: 'htmleditor',
        width: 550,
        height: 160,
        hidden: true,
        // Verstecken
        items: [
        {
            fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DESCRIPTION,
            labelSeparator: '',
            width: 420,
            height: 170,
            name: 'notice',
            id: 'noticeid'
        }]
    };

    var datedata = new Array();
    for (var ddi = 1; ddi < MySched.daytime.length; ddi++)
    {
        datedata[datedata.length] = [ddi, MySched.daytime[ddi].gerName];
    }

    var date = {
        columnWidth: .33,
        layout: 'form',
        labelAlign: 'top',
        items: [
        {
            fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY,
            labelStyle: 'padding:0px;',
            name: 'cbday',
            id: 'cbdayid',
            readOnly: true,
            xtype: 'combo',
            mode: 'local',
            store: new Ext.data.ArrayStore(
            {
                id: 0,
                fields: ['myId', 'displayText'],
                data: datedata
            }),
            valueField: 'myId',
            displayField: 'displayText',
            minChars: 0,
            triggerAction: 'all',
            blankText: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_CHOOSE,
            allowBlank: false,
            width: 170
        }]
    }

    var stime = {
        columnWidth: .33,
        layout: 'form',
        labelAlign: 'top',
        items: [
        {
            fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_STARTTIME,
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
        items: [
        {
            fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ENDTIME,
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
    for (var i = 0; i < MySched.Mapping.room.length; i++)
    {
        roomstore.push(new Array(MySched.Mapping.room.items[i].id,
        MySched.Mapping.room.items[i].name.replace(/^\s+/, '')
            .replace(/\s+$/, '')));
    }

    var teacherstore = new Array();
    for (var i = 0; i < MySched.Mapping.teacher.length; i++)
    {
        teacherstore.push(new Array(MySched.Mapping.teacher.items[i].id,
        MySched.Mapping.teacher.items[i].name));
    }

    var classstore = new Array();

    for (var i = 0; i < MySched.Mapping.module.length; i++)
    {
        classstore.push(new Array(MySched.Mapping.module.items[i].id,
        MySched.Mapping.module.items[i].department + " - " + MySched.Mapping.module.items[i].name));
    }

    var pwin;

    var roomitem = {
        columnWidth: .33,
        layout: 'form',
        labelAlign: 'top',
        items: [
        {
            xtype: "multiselect",
            fieldLabel: "Ort",
            name: 'room',
            id: 'roomid',
            title: '',
            store: new Ext.data.ArrayStore(
            {
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
        items: [
        {
            xtype: 'textfield',
            fieldLabel: '',
            name: 'roomfield',
            id: 'roomfieldid',
            emptyText: 'Raum eintragen',
            labelStyle: 'padding:0px;',
            width: 170
        }]
    };

    var teacheritem = {
        columnWidth: .33,
        layout: 'form',
        labelAlign: 'top',
        items: [
        {
            xtype: "multiselect",
            fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER,
            name: 'teacher',
            id: 'teacherid',
            title: '',
            store: new Ext.data.ArrayStore(
            {
                fields: ['myId', 'displayText'],
                data: teacherstore
            }),
            width: 170,
            height: 80,
            cls: "ux-mselect",
            valueField: "myId",
            displayField: "displayText",
            ddReorder: true
        }]
    }

    var teacherfield = {
        columnWidth: .33,
        layout: 'form',
        labelAlign: 'top',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: '',
            name: 'teacherfield',
            id: 'teacherfieldid',
            emptyText: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER_ENTER,
            labelStyle: 'padding:0px;',
            width: 170
        }]
    };

    var clasitem = {
        columnWidth: .33,
        layout: 'form',
        labelAlign: 'top',
        items: [
        {
            xtype: "multiselect",
            fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER,
            name: 'module',
            id: 'clasid',
            title: '',
            store: new Ext.data.ArrayStore(
            {
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
        items: [
        {
            xtype: 'textfield',
            fieldLabel: '',
            name: 'clasfield',
            id: 'clasfieldid',
            emptyText: 'Semester eintragen',
            labelStyle: 'padding:0px;',
            width: 170
        }]
    };

    var addterminpanel = Ext.create('Ext.FormPanel',
    {
        frame: true,
        bodyStyle: 'padding:5px',
        width: 550,
        height: 305,
        layout: 'form',
        id: 'addterminpanel',
        defaults: {
            msgTarget: 'side'
        },
        items: [titel, notice, // Wird erstmal nicht mehr
        // verwendet
        {
            xtype: 'fieldset',
            hideLabel: true,
            width: 540,
            autoHeight: true,
            hideBorders: true,
            layout: 'column',
            items: [roomitem, teacheritem, clasitem]
        },
        {
            xtype: 'fieldset',
            hideLabel: true,
            width: 540,
            autoHeight: true,
            hideBorders: true,
            layout: 'column',
            items: [roomfield, teacherfield, clasfield]
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
        buttons: [
        {
            text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ADD,
            scope: this,
            handler: function ()
            {
                var titel = Ext.getCmp('titelid')
                    .isValid(false);
                var day = Ext.getCmp('cbdayid')
                    .isValid(false);
                var stime = Ext.getCmp('starttiid')
                    .isValid(false);
                var etime = Ext.getCmp('endtiid')
                    .isValid(false);

                if (titel && day && stime && etime)
                {
                    var blocks = timetoblocks(Ext.getCmp('starttiid')
                        .getValue(), Ext.getCmp('endtiid')
                        .getValue());
                    var date = Ext.Date.format(
                    new Date(), "d.m.Y");
                    var teachers = Ext.getCmp('teacherid')
                        .getValue();
                    var rooms = Ext.getCmp('roomid')
                        .getValue();
                    var classes = Ext.getCmp('clasid')
                        .getValue();

                    if (Ext.getCmp('teacherfieldid')
                        .getValue()
                        .replace(/^\s+/, '')
                        .replace(/\s+$/, '') != "") teachers = teachers + "," + Ext.getCmp('teacherfieldid')
                        .getValue();
                    if (Ext.getCmp('roomfieldid')
                        .getValue()
                        .replace(/^\s+/, '')
                        .replace(/\s+$/, '') != "") rooms = rooms + "," + Ext.getCmp('roomfieldid')
                        .getValue();
                    if (Ext.getCmp('clasfieldid')
                        .getValue()
                        .replace(/^\s+/, '')
                        .replace(/\s+$/, '') != "") classes = classes + "," + Ext.getCmp('clasfieldid')
                        .getValue();

                    teachers = teachers.split(",");
                    rooms = rooms.split(",");
                    classes = classes.split(",");

                    var teacher = "";
                    var room = "";
                    var module = "";

                    for (var a = 0; a < rooms.length; a++)
                    {
                        var found = false;
                        if (rooms[a] != "")
                        {
                            for (var i = 0; i < MySched.Mapping.room.length; i++)
                            {
                                if (MySched.Mapping.room.items[i].name.replace(/^\s+/, '')
                                    .replace(/\s+$/, '') == rooms[a].replace(/^\s+/, '')
                                    .replace(/\s+$/, ''))
                                {
                                    if (!room.contains(MySched.Mapping.room.items[i].id))
                                    {
                                        if (room == "") room = MySched.Mapping.room.items[i].id;
                                        else room = room + " " + MySched.Mapping.room.items[i].id;
                                    }
                                    found = true;
                                    break;
                                }
                            }
                            if (!found)
                            {
                                if (room == "") room = rooms[a].replace(/^\s+/, '')
                                    .replace(/\s+$/, '')
                                    .replace(/\s+/, '_');
                                else room = room + " " + rooms[a].replace(/^\s+/, '')
                                    .replace(/\s+$/, '')
                                    .replace(/\s+/, '_');
                            }
                        }
                    }

                    for (var a = 0; a < teachers.length; a++)
                    {
                        var found = false;
                        if (teachers[a] != "")
                        {
                            for (var i = 0; i < MySched.Mapping.teacher.length; i++)
                            {
                                if (MySched.Mapping.teacher.items[i].name == teachers[a].replace(/^\s+/, '')
                                    .replace(/\s+$/, ''))
                                {
                                    if (!teacher.contains(MySched.Mapping.teacher.items[i].id))
                                    {
                                        if (teacher == "") teacher = MySched.Mapping.teacher.items[i].id;
                                        else teacher = teacher + " " + MySched.Mapping.teacher.items[i].id;
                                    }
                                    found = true;
                                    break;
                                }
                            }
                            if (!found)
                            {
                                if (teacher == "") teacher = teachers[a].replace(/^\s+/, '')
                                    .replace(/\s+$/, '')
                                    .replace(/\s+/, '_');
                                else teacher = teacher + " " + teachers[a].replace(/^\s+/, '')
                                    .replace(/\s+$/, '')
                                    .replace(/\s+/, '_');
                            }
                        }
                    }

                    for (var a = 0; a < classes.length; a++)
                    {
                        var found = false;
                        if (classes[a] != "")
                        {
                            for (var i = 0; i < MySched.Mapping.module.length; i++)
                            {
                                if ((MySched.Mapping.module.items[i].department + " - " + MySched.Mapping.module.items[i].name) == classes[a].replace(/^\s+/, '')
                                    .replace(/\s+$/, ''))
                                {
                                    if (!module.contains(MySched.Mapping.module.items[i].id))
                                    {
                                        if (module == "") module = MySched.Mapping.module.items[i].id;
                                        else module = module + " " + MySched.Mapping.module.items[i].id;
                                    }
                                    found = true;
                                    break;
                                }
                            }
                            if (!found)
                            {
                                if (module == "") module = classes[a].replace(/^\s+/, '')
                                    .replace(/\s+$/, '')
                                    .replace(/\s+/, '_');
                                else module = module + " " + classes[a].replace(/^\s+/, '')
                                    .replace(/\s+$/, '')
                                    .replace(/\s+/, '_');
                            }
                        }
                    }

                    for (var i = 0; i < blocks['size']; i++)
                    {
                        var tkey = "";
                        if (Ext.getCmp('hiddenkey')
                            .getValue() == "")
                        {
                            tkey = ("PE_" + Ext.getCmp('hiddenowner')
                                .getValue() + "_" + Ext.getCmp('cbdayid')
                                .getValue() + "_" + blocks[i] + "_" + date)
                                .toLowerCase();
                        }
                        else
                        {
                            tkey = Ext.getCmp('hiddenkey')
                                .getValue();
                        }

                        if (blocks['size'] == 1)
                        {
                            var values = {
                                block: blocks[i],
                                module: module,
                                dow: Ext.getCmp('cbdayid')
                                    .getValue(),
                                teacher: teacher,
                                id: Ext.getCmp('titelid')
                                    .getValue(),
                                key: tkey,
                                room: room,
                                subject: Ext.getCmp('titelid')
                                    .getValue(),
                                type: "personal",
                                desc: Ext.getCmp('noticeid')
                                    .getValue(),
                                owner: Ext.getCmp('hiddenowner')
                                    .getValue(),
                                stime: Ext.getCmp('starttiid')
                                    .getValue(),
                                etime: Ext.getCmp('endtiid')
                                    .getValue(),
                                showtime: "full",
                                lock: lock,
                                responsible: MySched.selectedSchedule.id
                            };
                        }
                        else if (i == 0)
                        {
                            var blotimes = blocktotime(blocks[i]);
                            if (Ext.getCmp('endtiid')
                                .getValue() != blotimes[1]) blotimes = blotimes[1];
                            else blotimes = Ext.getCmp('endtiid')
                                .getValue();
                            var values = {
                                block: blocks[i],
                                module: module,
                                dow: Ext.getCmp('cbdayid')
                                    .getValue(),
                                teacher: teacher,
                                id: Ext.getCmp('titelid')
                                    .getValue(),
                                key: tkey,
                                room: room,
                                subject: Ext.getCmp('titelid')
                                    .getValue(),
                                type: "personal",
                                desc: Ext.getCmp('noticeid')
                                    .getValue(),
                                owner: Ext.getCmp('hiddenowner')
                                    .getValue(),
                                stime: Ext.getCmp('starttiid')
                                    .getValue(),
                                etime: blotimes,
                                showtime: "first",
                                lock: lock,
                                responsible: MySched.selectedSchedule.id
                            };
                        }
                        else if ((i + 1) == blocks['size'])
                        {
                            var blotimes = blocktotime(blocks[i]);
                            if (Ext.getCmp('starttiid')
                                .getValue() != blotimes[0]) blotimes = blotimes[0];
                            else blotimes = Ext.getCmp('starttiid')
                                .getValue();
                            var values = {
                                block: blocks[i],
                                module: module,
                                dow: Ext.getCmp('cbdayid')
                                    .getValue(),
                                teacher: teacher,
                                id: Ext.getCmp('titelid')
                                    .getValue(),
                                key: tkey,
                                room: room,
                                subject: Ext.getCmp('titelid')
                                    .getValue(),
                                type: "personal",
                                desc: Ext.getCmp('noticeid')
                                    .getValue(),
                                owner: Ext.getCmp('hiddenowner')
                                    .getValue(),
                                stime: blotimes,
                                etime: Ext.getCmp('endtiid')
                                    .getValue(),
                                showtime: "last",
                                lock: lock,
                                responsible: MySched.selectedSchedule.id
                            };
                        }
                        else
                        {
                            var blotimes = blocktotime(blocks[i]);
                            var values = {
                                block: blocks[i],
                                module: module,
                                dow: Ext.getCmp('cbdayid')
                                    .getValue(),
                                teacher: teacher,
                                id: Ext.getCmp('titelid')
                                    .getValue(),
                                key: tkey,
                                room: room,
                                subject: Ext.getCmp('titelid')
                                    .getValue(),
                                type: "personal",
                                desc: Ext.getCmp('noticeid')
                                    .getValue(),
                                owner: Ext.getCmp('hiddenowner')
                                    .getValue(),
                                stime: blotimes[0],
                                etime: blotimes[1],
                                showtime: "none",
                                lock: lock,
                                responsible: MySched.selectedSchedule.id
                            };
                        }

                        var record = new mLecture(
                        values.key, values);

                        if (MySched.selectedSchedule.id != "mySchedule")
                        {
                            // Änderungen den Stammdaten
                            // hinzufügen
                            MySched.Base.schedule.addLecture(record);
                            // Änderungen den
                            // Gesamt�nderungen der
                            // Responsibles hinzufügen
                            MySched.responsibleChanges.addLecture(record);
                        }

                        var lessons = MySched.Schedule.getLectures();
                        for (var a = 0; a < lessons.length; a++)
                        if (lessons[a].data.key == values.key)
                        {
                            MySched.Schedule.addLecture(record);
                            break;
                        }

                        // Änderungen dem aktuellen
                        // Stundenplan hinzufügen
                        MySched.selectedSchedule.addLecture(record);
                    }
                    MySched.selectedSchedule.eventsloaded = null;
                    MySched.selectedSchedule.refreshView();
                    if (pwin != null) pwin.close();

                }
            }
        },
        {
            text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CANCEL,
            handler: function (b, e)
            {
                if (pwin != null) pwin.close();
            }
        }]
    });

    pwin = Ext.create('Ext.Window',
    {
        layout: 'form',
        id: 'terminWin',
        width: 560,
        iconCls: 'lesson_add',
        title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_ADD,
        height: 337,
        modal: true,
        frame: false,
        closeAction: 'close',
        items: [addterminpanel]
    });

    if (l)
    {
        pwin.setIconClass("lesson_edit");
        pwin.setTitle(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_CHANGE);
        addterminpanel.buttons[0].text = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CHANGE;
    }

    pwin.show();
    Ext.getCmp('cbdayid')
        .setValue(daytonumber(pday));
    Ext.getCmp('hiddenowner')
        .setValue(MySched.Authorize.user);

    if (key)
    {
        Ext.getCmp('hiddenkey')
            .setValue(key);
    }

    Ext.getCmp('clasid')
        .setValue(clas_name);
    Ext.getCmp('roomid')
        .setValue(room_name);
    Ext.getCmp('teacherid')
        .setValue(teacher_name);

    if (clas_name)
    {
        setFieldValue("module", clas_name);
    }

    if (room_name)
    {
        setFieldValue("room", room_name);
    }

    if (teacher_name)
    {
        setFieldValue("teacher", teacher_name);
    }

    if (lock == "teacher")
    {
        if (!teacher_name) setFieldValue("teacher", MySched.selectedSchedule.id);
        Ext.getCmp('teacherid')
            .disable();
        Ext.getCmp('teacherfieldid')
            .disable();

    }
    else if (lock == "room")
    {
        if (!room_name) setFieldValue("room", MySched.selectedSchedule.id);
        Ext.getCmp('roomid')
            .disable();
        Ext.getCmp('roomfieldid')
            .disable();
    }
    else if (lock == "module")
    {
        if (!clas_name) setFieldValue("module", MySched.selectedSchedule.id);
        Ext.getCmp('clasid')
            .disable();
        Ext.getCmp('clasfieldid')
            .disable();
    }
}

function setFieldValue(type, str)
{
    var tempidarr = Ext.getCmp(type + 'id')
        .getValue()
        .split(",");
    var temparr = str.split(",");
    var tempstr = "";
    for (var tai = 0; tai < temparr.length; tai++)
    {
        var objtemp = MySched.Mapping.getObject(type, temparr[tai]);
        var strtemp = "";
        if (Ext.isObject(objtemp))
        {
            if (type == "module") strtemp = objtemp.department + " - " + objtemp.name;
            else strtemp = objtemp.name;
        }
        else strtemp = temparr[tai];
        if (tempstr == "")
        {
            tempstr = strtemp;
        }
        else tempstr = tempstr + "," + strtemp;
    }
    Ext.getCmp(type + 'fieldid')
        .setValue(tempstr);
}

function daytonumber(day)
{
    switch (day)
    {
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
 * 
 * @param {String}
 *            week_day englischer Tagesname
 */

function weekdayEtoD(week_day)
{
    switch (week_day)
    {
    case "monday":
        return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_MONDAY;
        break;
    case "tuesday":
        return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_TUESDAY;
        break;
    case "wednesday":
        return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_WEDNESDAY;
        break;
    case "thursday":
        return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_THURSDAY;
        break;
    case "friday":
        return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_FRIDAY;
        break;
    case "saturday":
        return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_SATURDAY;
        break;
    case "sunday":
        return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_SUNDAY;
        break;
    default:
        return false;
    }
}

function timetoblocks(stime, etime)
{
    var blocks = [];
    counter = 0;
    for (var i = 1; i <= 6; i++)
    {
        var times = blocktotime(i);
        if ((stime <= times[0] && etime >= times[1]) || (stime >= times[0] && etime <= times[1]) || (times[0] <= stime && times[1] > stime) || (times[0] < etime && times[1] >= etime))
        {
            blocks[counter] = i;
            counter++;
        }
    }
    blocks['size'] = counter;
    return blocks;
}

function blocktotime(block)
{
    if (Ext.isNumber(block) && typeof block != "undefined" && MySched.daytime[1] != null) return {
        0: MySched.daytime[1][block]["stime"],
        1: MySched.daytime[1][block]["etime"]
    };
    return {
        0: null,
        1: null
    };
}

Ext.ux.collapsedPanelTitlePlugin = function ()
{
    this.init = function (p)
    {
        if (p.collapsible)
        {
            var r = p.region;
            if ((r == 'north') || (r == 'south'))
            {
                p.on('render',

                function ()
                {
                    var ct = p.ownerCt;
                    ct.on('afterlayout',

                    function ()
                    {
                        if (ct.layout[r].collapsedEl)
                        {
                            p.collapsedTitleEl = ct.layout[r].collapsedEl.createChild(
                            {
                                tag: 'span',
                                cls: 'x-panel-header-text',
                                html: p.title,
                                style: "margin-left:5px; color:#15428B; font-family:tahoma; font-size:11px; font-weight:bold; line-height:18px;"
                            });
                            p.setTitle = Ext.Panel.prototype.setTitle.createSequence(function (
                            t)
                            {
                                p.collapsedTitleEl.dom.innerHTML = t;
                            });
                        }
                    }, false,
                    {
                        single: true
                    });
                    p.on('collapse',

                    function ()
                    {
                        if (ct.layout[r].collapsedEl && !p.collapsedTitleEl)
                        {
                            p.collapsedTitleEl = ct.layout[r].collapsedEl.createChild(
                            {
                                tag: 'span',
                                cls: 'x-panel-header-text',
                                html: p.title,
                                style: "margin-left:5px; color:#15428B; font-family:tahoma; font-size:11px; font-weight:bold; line-height:18px;"
                            });
                            p.setTitle = Ext.Panel.prototype.setTitle.createSequence(function (
                            t)
                            {
                                p.collapsedTitleEl.dom.innerHTML = t;
                            });
                        }
                    }, false,
                    {
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
MySched.Tree = function ()
{
    var tree, teacher, room, module, diff, dragNode, respChanges, curtea;

    return {
        init: function ()
        {
        	var children = [];

        	if(Ext.isObject(MySched.startup["TreeView.load"]))
        	{
	            var children = MySched.startup["TreeView.load"].data["tree"];
	            /*
	             * while(isset(children[0])) { if(isset(children[0]["id"])) break;
	             * children = children[0]; }
	             */
        	}
        	else
        	{
        		Ext.Ajax.request(
                {
                    url: _C('ajaxHandler'),
                    method: 'POST',
                    params: {
                        scheduletask: "TreeView.load"
                    },
                    failure: function (response)
                    {
                        var bla = response;
                    },
                    success: function (response)
                    {
                        var json = Ext.decode(response.responseText);
                        var newtree = json["tree"];
                        var treeData = json["treeData"];
                        /*
                         * if (accMode != 'none') { treeRoot.appendChild(children); }
                         */

                        for (var item in treeData)
                        {
                            if (Ext.isObject(treeData[item]))
                            {
                                for (var childitem in treeData[item])
                                {
                                    if (Ext.isObject(treeData[item][childitem]))
                                    {
                                        MySched.Mapping[item].add(
                                        childitem,
                                        treeData[item][childitem]);
                                    }
                                }
                            }
                        }
                        
                        var rootNode = MySched.Tree.tree.getRootNode();
                        rootNode.removeAll(true);
                        rootNode.appendChild(newtree);
                        MySched.Tree.tree.update();
                        if (loadMask)
            		    {
            				loadMask.destroy();
            		    }
                        
                        var publicDefaultNode = json["treePublicDefault"];

                        if(publicDefaultNode != null)
                        {
	                        if (publicDefaultNode["type"] == "delta")
	                        {
	//                            MySched.delta.load(_C('ajaxHandler'), 'json',
	//                            MySched.delta.loadsavedLectures, MySched.delta, "delta");
	                        }
	                        else
	                        {
	                            var nodeID = publicDefaultNode["id"];
	                            var nodeKey = publicDefaultNode["nodeKey"];
	                            var gpuntisID = publicDefaultNode["gpuntisID"];
	                            var plantypeID = publicDefaultNode["plantype"];
	                            var semesterID = publicDefaultNode["semesterID"];
	                            var type = publicDefaultNode["type"];
	
	                            MySched.Tree.showScheduleTab(nodeID, nodeKey,
	                            gpuntisID, semesterID, plantypeID, type);
	                        }
                        }
                    }
                });
        	}
        	
        	var treeStore = Ext.create('Ext.data.TreeStore',
            {
                folderSort: true,
                sorters: [{
                   property: 'text',
                   direction: 'ASC'
                }],
                root: {
                    id: 'rootTreeNode',
                    text: 'root',
                    expanded: true,
                    children: children
                }
            });

            this.tree = Ext.create('Ext.tree.Panel',
            {
                title: ' ',
                singleExpand: false,
                id: 'selectTree',
                preventHeader: true,
                height: 470,
                autoscroll: false,
                rootVisible: false,
                bodyCls: 'MySched_SelectTree',
                viewConfig: {
                    plugins: {
                        ptype: 'treeviewdragdrop',
                        ddGroup: 'lecture',
                        enableDrop: false,
                        enableDrag: true
                    },
                    autoScroll: false
                },
                layout: {
                    type: 'fit'
                },
                store: treeStore
            });

            // Bei Klick Stundenplan oeffnen
            this.tree.on('itemclick', function (me, rec, item, index, event, options)
            {
                if (rec.isLeaf())
                {
                    var title = "";
                    if (rec.raw) var data = rec.raw;
                    else var data = rec.data;

                    var nodeID = data.id;
                    var nodeKey = data.nodeKey;
                    var gpuntisID = data.gpuntisID;
                    var semesterID = data.semesterID;
                    var plantypeID = data.plantype;
                    var type = data.type;

                    MySched.Tree.showScheduleTab(nodeID, nodeKey, gpuntisID, semesterID, plantypeID, type);
                }
                else if (rec.isExpanded())
                {
                    rec.collapse();
                }
                else
                {
                    rec.expand();
                }
            });

            return this.tree;
        },
        showScheduleTab: function (nodeID, nodeKey, gpuntisID, semesterID, plantypeID, type)
        {
            showLoadMask();

            if (type === null)
            {
            	type = gpuntisID;
            }
            var department = null;
            if (type == "delta")
            {
            	title = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_CENTRAL;
            }
            else if (type == "respChanges")
            {
            	title = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_OWN;
            }
            else
            {
                var departmenttype = "field"
                var departmentfield = "description";
                var nodeFullName = nodeKey;
                if (type == "teacher")
                {                    
                    nodeFullName = getTeacherSurnameWithCutFirstName(nodeKey);
                }
                else if (type == "room")
                {
                    nodeFullName = MySched.Mapping.getRoomName(nodeKey);
                    departmenttype = "roomtype";
                }
                else if (type == "module")
                {
                    nodeFullName = MySched.Mapping.getModuleFullName(nodeKey);
                    departmenttype = "degree";
                    departmentfield = "degree";
                }
                else if (type == "subject")
                {
                    nodeFullName = MySched.Mapping.getSubjectName(nodeKey);
                }

                department = MySched.Mapping.getObjectField(type, nodeKey, departmentfield);
                departmentName = MySched.Mapping.getObjectField(departmenttype, department, "name");
                if (typeof department == "undefined" || department == "none" || department == null || department == nodeKey)
                {
                    title = nodeFullName;
                }
                else
                {
                    title = nodeFullName + " - " + departmentName
                }
            }

            if (type == "delta")
            {
                new mSchedule(nodeID, title)
                    .init(type, nodeKey)
                    .show();
            }
            else
            {
                if (MySched.loadLessonsOnStartUp == false)
                {
                    var weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker')
                        .value);
                    var currentMoFrDate = getCurrentMoFrDate();

                    Ext.Ajax.request(
                    {
                        url: _C('ajaxHandler'),
                        method: 'POST',
                        params: {
                            nodeKey: nodeKey,
                            gpuntisID: gpuntisID,
                            semesterID: semesterID,
                            scheduletask: "Ressource.load",
                            type: type,
                            startdate: Ext.Date.format(currentMoFrDate.monday, "Y-m-d"),
                            enddate: Ext.Date.format(currentMoFrDate.friday, "Y-m-d")
                        },
                        failure: function (response)
                        {
                            Ext.Msg.alert(
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR,
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_ERROR);
                        },
                        success: function (response)
                        {
                            var json = Ext.decode(response.responseText);
                            var lessonData = json["lessonData"];
                            var lessonDate = json["lessonDate"];
                            for (var item in lessonData)
                            {
                                if (Ext.isObject(lessonData[item]))
                                {
                                    var record = new mLecture(
                                    item,
                                    lessonData[item], semesterID,
                                    plantypeID);
                                    MySched.Base.schedule.addLecture(record);
                                    //												MySched.TreeManager.add(record);
                                }
                            }
                            if (Ext.isObject(lessonDate))
                            {
                                MySched.Calendar.addAll(lessonDate);
                            }

                            new mSchedule(nodeID, title)
                                .init(type, nodeKey, semesterID)
                                .show();
                        }
                    });
                }
                else
                {
                    new mSchedule(nodeID, title)
                        .init(type, nodeKey, semesterID)
                        .show();
                }
            }
        },
        /**
         * Setzt den Titel des Listenfelds
         * 
         * @param {Object}
         *            title
         * @param {Object}
         *            append
         */
        setTitle: function (title, append)
        {
            if (append == true) this.tree.setTitle(this.tree.title + title);
            else this.tree.setTitle(title);
        },
        /**
         * Refresht die Daten der Liste
         */
        refreshTreeData: function ()
        {
            if (this.teacher) this.root.removeChild(this.teacher);
            if (this.room) this.root.removeChild(this.room);
            if (this.module) this.root.removeChild(this.module);
            if (this.diff) this.root.removeChild(this.diff);
            if (this.respChanges) this.root.removeChild(this.respChanges);
            if (this.curtea) this.root.removeChild(this.curtea);
            this.loadTreeData();
        },
        /**
         * Fuellt den Baum mit den Daten von Teacherenten, Raeumen und
         * Studiengaengen/Semestern, je nach berechtigung
         */
        loadTreeData: function ()
        {
            /*MySched.TreeManager.processTreeData(
            MySched.startup["TreeView.load"].data, null, null, null,
            this.tree);*/
        },
        /**
         * Setzt die Daten im Baum
         */
        setTreeData: function (data)
        {
            var type = data.id
            this[type] = data;
            var imgs = Ext.DomQuery.select('img[class=x-tree-ec-icon x-tree-elbow-end-plus]',
            MySched.Tree.tree.body.dom);
            for (var i = 0; i < imgs.length; i++)
            {
                imgs[i].alt = "collapsed";
            }
            var imgs = Ext.DomQuery.select('img[class=x-tree-ec-icon x-tree-elbow-plus]',
            MySched.Tree.tree.body.dom);
            for (var i = 0; i < imgs.length; i++)
            {
                imgs[i].alt = "collapsed";
            }
        }
    };
}();

function checkStartup(checkfor, type)
{
    if (!Ext.isString(type)) type = null;
    if (Ext.isObject(MySched.startup) === true) if (Ext.isObject(MySched.startup[checkfor]))
    {
        if (type === null)
        {
            if (MySched.startup[checkfor].success === true)
            {
                return true;
            }
        }
        else
        {
            if (MySched.startup[checkfor][type].success === true)
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
MySched.Subscribe = function ()
{
    var data, grid, store, window;
    var grid1;
    return {
        /**
         * Speichert die uebergebenen Daten
         * 
         * @param {Object}
         *            data
         */
        setData: function (data)
        {
            this.data = new Array();
            Ext.each(data, function (v)
            {
                if (v.subscribe_possible) this.push(v);
            }, this.data);
        },
        /**
         * Zeigt das Fenster zur Auswahl der Veranstaltungen in die
         * Eingeschriebene werden soll an
         * 
         * @param {Object}
         *            data Aktueller "Mein Stundenplan"
         */
        show: function (data)
        {
            this.setData(data);

            // Erstellt Fenstern
            this.window = Ext.create('Ext.Window',
            {
                title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SUBSCRIBE,
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
        buildGrid: function ()
        {
            var sm = new Ext.grid.CheckboxSelectionModel();
            // Daten zum Einschreiben holen
            this.store = Ext.create('Ext.data.JsonStore',
            {
                fields: [
                {
                    name: 'name'
                }, 'subscribe', 'subscribe_info', 'subscribe_type']
            });
            this.store.loadData(this.data);

            // Erstellt die Tabelle
            this.grid = Ext.create('Ext.grid.GridPanel',
            {
                store: this.store,
                columns: [
                sm,
                {
                    dataIndex: 'name',
                    header: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON,
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
                    getRowClass: function (record, rowIndex, p,
                    store)
                    {
                        if (this.showPreview)
                        {
                            p.body = '<p style="padding-left:25px; text-decoration:italic;">' + record.data.subscribe_info + '</p>';
                            return 'x-grid3-row-expanded';
                        }
                        return 'x-grid3-row-collapsed';
                    }
                },
                bbar: [
                {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SAVE,
                    id: 'btnSave',
                    iconCls: 'tbSave',
                    handler: this.save,
                    scope: this
                }],
                title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_SUBSCRIBE
            });

            return this.grid;
        },
        save: function ()
        {
            // Sendet die ausgewaehlten Veranstaltungen an den Server
            // Muss noch implementiert werden
            // Selctions des Grids ermitteln, Elemente des Stores an
            // subscibe.php senden
        }
    };
}();

function isset(me)
{
    if (me === null || me == '' || typeof me == 'undefined') return false;
    else return true;
}