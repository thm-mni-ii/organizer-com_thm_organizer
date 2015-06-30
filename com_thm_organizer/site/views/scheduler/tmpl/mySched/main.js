/*global Ext, MySched, MySchedLanguage, LectureModel, ScheduleModel, EventListModel, EventModel, externLinks, addNewEvent, numbertoday,
 _C, getCurrentMoFrDate, numbertoday, getTeacherSurnameWithCutFirstName, loadMask */
/**
 * mySched - Mainclass by Thorsten Buss and Wolf Rost
 */
MySched.freeBusyState = true;
MySched.selectedSchedule = null;
MySched.version = '3.1.0';
MySched.delta = null;
MySched.responsibleChanges = null;
MySched.session = [];
MySched.daytime = [];
MySched.loadedLessons = [];
MySched.mainPath = externLinks.mainPath;
MySched.displaySemesterBeginDialog = true;
Ext.Ajax.setTimeout(60000);
MySched.Config.addAll(
    {
        // Determine how the additional information should be shown
        infoMode: 'popup',
        ajaxHandler: externLinks.ajaxHandler,
        // TODO: I think this is obsolte, maybe change to moodle?
        estudycourse: MySched.mainPath + 'php/estudy_course.php',
        infoUrl: MySched.mainPath + 'php/info.php',
        showHeader: false,
        // should the header area been shown?
        headerHTML: '<img src="http://www.mni.thm.de/templates/fh/Bilder/Header.png" title="fh-header" alt="fh-header"/>',
        enableSubscribing: false,
        // Activates the button and the function 'enlist' ('Einschreiben')
        logoutTarget: 'http://www.mni.thm.de'
    }
);

// Authorize will be initialized
// Due the given array, the role of the right side in MySched will be mapped
// CAUTION!!! Keys are used in lowercase
MySched.Authorize.init(
    {
        // Which role does an unannounced user has?
        defaultRole: 'user',
        // ALL: defines which common elements all roles have
        ALL: {
            module: '*',
            diff: '*',
            curtea: '*'
        },
        // every role can get an array with keys or string with '*'
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
    }
);

MySched.BlockMenu = [];
MySched.BlockMenu.Menu = [];

/**
 * Main object
 *
 * @class MySched.Base
 */
MySched.Base = function ()
{
    return {
        /**
         * This is the init method and will be called at the start of the schedule app. A lot of configurations are made
         * before the call. Have a look at 'site/views/schedule/tmpl/default.php'
         *
         * @method ini
         */
        init: function ()
        {
            if(screen.width < TABLET_WIDTH_MAX && window.location.href.search('tmpl=component') == -1 )
            {
                var redirectURL = '';
                if(window.location.href.search('index.php') == -1)
                {
                    redirectURL = '?tmpl=component';
                }
                else
                {
                    if(window.location.href.search(/\?/) == -1)
                    {
                        redirectURL = '?tmpl=component';
                    }
                    else
                    {
                        redirectURL = '&tmpl=component';
                    }
                }
                window.location.href = window.location.href + redirectURL;
            }
            if (Ext.isString(MySched.startup) === true)
            {
                try
                {
                    // there is a huge object saved in MySched.startup. It contains: Calendar, Events.load, Grid.load,
                    // Lessons, ScheduleDescription.load, UserSchedule.load and curriculumColors
                    MySched.startup = Ext.decode(decodeURIComponent(MySched.startup));
                }
                catch (e)
                {
                    // TODO: Nothing to catch?
                }
                MySched.Base.startMySched(MySched.startup["Grid.load"]);
            }
        },
        /**
         * This method creates an array which contains the data of all available lesson blocks in a week. It also calls
         * the Mapping and treeManager class. In the end the schedule data a read from a xml file.
         *
         * @method startMySched
         * @param {Object} GridData Contains all lesson blocks of a week with start and end time
         */
        startMySched: function (GridData)
        {
            var length = 0;
            if (Ext.isNumber(GridData.length))
            {
                length = GridData.length;
            }
            else
            {
                length = GridData.size;
            }

            MySched.gridData = GridData;

            //// Here an array is created with all block lessons of a week.
            //for (var i = 1; i <= length; i++)
            //{
            //    if (!MySched.daytime[GridData[i].day])
            //    {
            //        MySched.daytime[GridData[i].day] = [];
            //        MySched.daytime[GridData[i].day].engName = numbertoday(GridData[i].day);
            //        MySched.daytime[GridData[i].day].gerName = weekdayEtoD(numbertoday(GridData[i].day));
            //        MySched.daytime[GridData[i].day].localName = "day";
            //    }
            //    if (!MySched.daytime[GridData[i].day][GridData[i].period])
            //    {
            //        MySched.daytime[GridData[i].day][GridData[i].period] = [];
            //    }
            //    MySched.daytime[GridData[i].day][GridData[i].period].etime = GridData[i].endtime.substr(0, 5);
            //    MySched.daytime[GridData[i].day][GridData[i].period].stime = GridData[i].starttime.substr(0, 5);
            //    MySched.daytime[GridData[i].day][GridData[i].period].tpid = GridData[i].gpuntisID;
            //    MySched.daytime[GridData[i].day][GridData[i].period].localName = "block";
            //}

            // Initialize the tree and the choice control
            // TODO: There are two possibilities:
            // 1. I don't understand how and why TreeManager is used
            // 2. TreeManager is not in use anymore and was not removed from source code. And if it is so: WTF?
            //MySched.TreeManager.init();

            // Initialize the name/acronym mapping
            MySched.Mapping.init();

            // Initialize display of the Quicktips
            Ext.QuickTips.init();

            // load the XML file with the schedule data
            MySched.Base.loadLectures(_C('scheduleXml'));
        },
        /**
         * Register schedule events. This events are performed if you make changes or load "My schedule"
         *
         * @method registerScheduleEvents
         */
        registerScheduleEvents: function ()
        {
            // Control if the "empty" button should be shown - only if the schedule isn't already empty
            MySched.Schedule.on(
                {
                    // actions and what they perform
                    lectureAdd: function ()
                    {
                        Ext.ComponentMgr.get('btnEmpty').enable();
                    },
                    lectureDel: function ()
                    {
                        if (MySched.Schedule.isEmpty())
                        {
                            Ext.ComponentMgr.get('btnPdf').disable();
                            if (_C('enableSubscribing'))
                            {
                                Ext.ComponentMgr.get('btnSub').disable();
                            }
                        }
                    },
                    changed: function ()
                    {
                        var contentAnchorTip = Ext.getCmp('content-anchor-tip');
                        if (contentAnchorTip)
                        {
                            contentAnchorTip.destroy();
                        }
                        if (!MySched.Schedule.isEmpty() && MySched.FPDFInstalled)
                        {
                            Ext.ComponentMgr.get('btnPdf').enable();
                            if (_C('enableSubscribing'))
                            {
                                Ext.ComponentMgr.get('btnSub').enable();
                            }
                        }

                        Ext.ComponentMgr.get('btnSave').enable();

                        var tab = MySched.layout.tabpanel.getComponent('mySchedule');
                        tab.ScheduleModel.status = "unsaved";
                    },
                    save: function (s)
                    {
                        var tab = MySched.layout.tabpanel.getComponent('mySchedule');
                        tab.ScheduleModel.status = "saved";
                        Ext.ComponentMgr.get('btnSave').disable();
                    },
                    load: function (s)
                    {
                        MySched.Base.createUserSchedule();
                        Ext.ComponentMgr.get('btnSave').disable();
                        var tab = MySched.layout.tabpanel.getComponent('mySchedule');
                    },
                    clear: function (s)
                    {}
                }
            );
        },
        /**
         * Register schedule events
         * TODO: I don not know what is going on here. The methods are called once of you add something to your schdule,
         * bit never again, no matter what you do
         *
         * @method regScheduleEvents
         * @param {String} id Information above the tab (schedule) (e.g. MNI;SS;2013-10-07;2014-10-05;pool;BI;BI.1)
         */
        regScheduleEvents: function (id)
        {
            MySched.selectedSchedule.on(
                {
                    'changed': function ()
                    {
                        var contentAnchorTip = Ext.getCmp('content-anchor-tip');
                        if (contentAnchorTip)
                        {
                            contentAnchorTip.destroy();
                        }
                        var tab = MySched.layout.tabpanel.getComponent(id);

                        Ext.ComponentMgr.get('btnSave').enable();

                        tab.ScheduleModel.status = "unsaved";
                    },
                    'save': function (s)
                    {
                        var tab = MySched.layout.tabpanel.getComponent(id);
                        tab.ScheduleModel.status = "saved";
                        Ext.ComponentMgr.get('btnSave').disable();
                    },
                    'clear': function (s)
                    {
                        Ext.ComponentMgr.get('btnEmpty').disable();
                    }
                });
        },
        /**
         * Load the XML files and starts parsing
         *
         * @method loadLectures
         * @param {String} url XML-Datei
         */
        loadLectures: function (url)
        {
            // all schedules data will be saved in one schedule
            this.schedule = new ScheduleModel();
            this.afterLoad();
        },
        /**
         * Tasks after the XML data are successfully loaded
         *
         * @method afterLoad
         */
        afterLoad: function ()
        {
            MySched.eventlist = new EventListModel();

            MySched.Base.myschedInit();
        },
        /**
         * Creates the schedule with data, layout and events
         *
         * @method myschedInit
         */
        myschedInit: function ()
        {
            var lessonData = MySched.startup.Lessons;
            var plantypeID = "";

            // iterate through lessonData which contains very single lecture with some information and
            // make it to a lecture model and add is to the schedule
            for (var item in lessonData)
            {
                if (Ext.isObject(lessonData[item]))
                {
                    var record = new LectureModel(item, Ext.clone(lessonData[item]), MySched.class_semester_id, plantypeID);
                    MySched.Base.schedule.addLecture(record);
                }
            }

            // Initialize "My Schedule" ("Mein Stundenplan")
            MySched.Schedule = new ScheduleModel('mySchedule', MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE);

            // Initialize "Change the responsible person" ("Änderungen der Verantwortlichen")
            MySched.responsibleChanges = new ScheduleModel("respChanges", MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_OWN);

            // Register events if the the own schedules changes
            MySched.Base.registerScheduleEvents();

            // Initialize information display
            // TODO MySched.InfoPanel is maybe obsolete. This is the only line where MySched.InfoPanel is called.
            MySched.InfoPanel.init();
            MySched.SelectionManager.init();

            // Detect whether the user is running desktop, tablet or phone
            if(Ext.os.is.Phone){
                // Create the layout for Phone devices
                MySched.layout.buildMobileLayout();
            } else{
                // Create the layout for Desktop / Tablet
                MySched.layout.buildBasicLayout();
            }

            MySched.Base.setScheduleDescription(MySched.startup["ScheduleDescription.load"].data);
        },
        /**
         * Set description of the schedule and added actions to buttons
         *
         * @method setScheduleDescription
         * @param {Object} jsonData Data of the schedule
         */
        setScheduleDescription: function (jsonData)
        {
            if (Ext.isObject(jsonData))
            {
                MySched.session.begin = jsonData.startdate;
                MySched.session.end = jsonData.enddate;
                MySched.session.creationdate = jsonData.creationdate;

                MySched.SelectBoxes.setTitle(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_AS_OF + " " + MySched.session.creationdate);

                // Managed the visibility of the Add/Del Buttons at the toolbar
                MySched.SelectionManager.on('select', function (el)
                {
                    if (MySched.Schedule.lectureExists(el.id))
                    {
                        Ext.ComponentMgr.get('btnDel').enable();
                    }
                    else
                    {
                        Ext.ComponentMgr.get('btnAdd').enable();
                    }
                });
                MySched.SelectionManager.on('unselect', function ()
                {
                    Ext.ComponentMgr.get('btnDel').disable();
                    Ext.ComponentMgr.get('btnAdd').disable();
                });
                MySched.SelectionManager.on('lectureAdd', function ()
                {
                    Ext.ComponentMgr.get('btnAdd').disable();
                });
                MySched.SelectionManager.on('lectureDel', function ()
                {
                    Ext.ComponentMgr.get('btnDel').disable();
                });

                if (MySched.SessionId && MySched.schedulerFromMenu === true)
                {
                    MySched.Authorize.verifyToken(MySched.SessionId,
                        MySched.Authorize.verifySuccess, MySched.Authorize);
                }
            }
            else
            {
                Ext.ComponentMgr.get('selectTree')
                    .setTitle(
                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_INVALID);
                Ext.ComponentMgr.get('topMenu')
                    .disable();
            }
        },
        /**
         * Creates the tab "My schedule" ("Mein Stundenplan")
         *
         * @method createUserSchedule
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
                // tab 'My schedule' will be DropArea
                var tabID = MySched.layout.tabpanel.getComponent('mySchedule')
                    .tab.el.dom;
                var dropTarget = new Ext.dd.DropTarget(tabID, this.getDropConfig());
            }
        },
        /**
         * Loads the schedule that the user defined
         *
         * @method loadUserSchedule
         */
        loadUserSchedule: function ()
        {
            MySched.Schedule.load(_C('ajaxHandler'), 'json',
                MySched.Schedule.preParseLectures, MySched.Schedule,
                MySched.Authorize.user);
            MySched.layout.viewport.doLayout();
        },
        /**
         * Give the Drop configuration back to the Drag'n'Drop
         *
         * @method getDropConfig
         */
        getDropConfig: function ()
        {
            // defines the configuration for the Drag'n'Drop
            return {
                ddGroup: 'lecture',
                // accept lectures
                notifyDrop: function (dd, e, data)
                {
                    if (!Ext.isEmpty(data.records) || !Ext.isEmpty(data.patientData))
                    {
                        var n = {};
                        if(!Ext.isEmpty(data.patientData))
                        {
                            n = data.patientData;
                        }
                        else if(data.records[0].isLeaf())
                        {
                            n = data.records[0].raw;
                        }

                        if (!Ext.isEmpty(n))
                        {
                            // joins the semester plan to own schedule
                            var nodeID = n.id;
                            var nodeKey = n.nodeKey;
                            var gpuntisID = n.gpuntisID;
                            var semesterID = n.semesterID;
                            var plantypeID = n.plantype;
                            var type = n.type;

                            if (MySched.loadLessonsOnStartUp === false)
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
                                            var lessonData = json.lessonData;
                                            var lessonDate = json.lessonDate;
                                            for (var item in lessonData)
                                            {
                                                if (Ext.isObject(lessonData[item]))
                                                {
                                                    var record = new LectureModel(
                                                        item,
                                                        lessonData[item], semesterID,
                                                        plantypeID);
                                                    MySched.Base.schedule.addLecture(record);
                                                }
                                            }
                                            if (Ext.isObject(lessonDate))
                                            {
                                                MySched.Calendar.addAll(lessonDate);
                                            }

                                            var s = new ScheduleModel(
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
                                var s = new ScheduleModel(nodeID, '_tmpSchedule').init(type, nodeKey);

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
                        // joins the semester plan to own schedule
                        MySched.Schedule.addLecture(MySched.Base.schedule.getLecture(data.id));
                        MySched.selectedSchedule.eventsloaded = null;
                        MySched.selectedSchedule.refreshView();
                    }
                    return true;
                }
            };
        },
        /**
         * Returns the lecture with the id
         *
         * @method getLecture
         * @param {Object} id eventID
         * @return {Object} * A lecture with its information
         */
        getLecture: function (id)
        {
            return this.schedule.getLecture(id);
        },
        /**
         * Returns particular lectures
         *
         * @method getLectures
         *
         * @param {Object} type Above which field should bee selected
         * @param {Object} value Which value should the field have
         *
         * @return {MySched.Collection}
         */
        getLectures: function (type, value)
        {
            return this.schedule.getLectures(type, value);
        },
        /**
         * Handles the FreeBusy state - will be called if a button is switched
         *
         * @method freeBusyHandler
         *
         * @param {Object} e Event which is triggered
         * @param {Object} state State of the buton
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
            // determine new state
            MySched.freeBusyState = state;
        },
        /**
         * Get the data from the server to the according schedule and shows it
         *
         * @method showScheduleTab
         * @param {string} nodeID The id of the resource
         * @param {string} nodeKey The nodekey of the resource
         * @param {string} gpuntisID The gpunits id
         * @param {string} semesterID The id of the semester
         * @param {string} plantypeID TODO Was always undefined at tests
         * @param {string} type The type of the ressource (e.g. teacher or room)
         */
        showScheduleTab: function (nodeID, nodeKey, gpuntisID, semesterID, plantypeID, type)
        {
            var title, config = {};
            if(nodeID === null)
            {
                nodeID = nodeKey;
            }

            if (type === null)
            {
                type = gpuntisID;
            }
            var department = null;
            if (type === "delta")
            {
                title = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_CENTRAL;
            }
            else if (type === "respChanges")
            {
                title = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_OWN;
            }
            else
            {
                var departmentType = "field", departmentField = "description", nodeFullName = nodeKey;
                var grid = "Haupt-Zeitraster";
                if (type === "teacher")
                {
                    nodeFullName = getTeacherSurnameWithCutFirstName(nodeKey);
                }
                else if (type === "room")
                {
                    nodeFullName = MySched.Mapping.getRoomName(nodeKey);
                    departmentType = "roomtype";
                }
                else if (type === "pool")
                {
                    nodeFullName = MySched.Mapping.getPoolFullName(nodeKey);
                    grid = MySched.Mapping.getGrid(nodeKey);
                    departmentType = "degree";
                    departmentField = "degree";
                }
                else if (type === "subject")
                {
                    nodeFullName = MySched.Mapping.getSubjectName(nodeKey);
                }

                department = MySched.Mapping.getObjectField(type, nodeKey, departmentField);
                var departmentName = MySched.Mapping.getObjectField(departmentType, department, "name");
                if (typeof department === "undefined" || department === "none" || department === null || department === departmentName)
                {
                    title = nodeFullName;
                }
                else
                {
                    title = nodeFullName + " - " + departmentName;
                }
            }

            config.grid = grid;

            if (type === "delta")
            {
                new ScheduleModel(nodeID, title, config).init(type, nodeKey).show();
            }
            else
            {
                if (MySched.loadLessonsOnStartUp === false)
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
                                var lessonData = json.lessonData;
                                var lessonDate = json.lessonDate;
                                for (var item in lessonData)
                                {
                                    if (Ext.isObject(lessonData[item]))
                                    {
                                        var record = new LectureModel(item, lessonData[item], semesterID, plantypeID);
                                        MySched.Base.schedule.addLecture(record);
                                    }
                                }
                                if (Ext.isObject(lessonDate))
                                {
                                    MySched.Calendar.addAll(lessonDate);
                                }

                                new ScheduleModel(nodeID, title, config).init(type, nodeKey, semesterID).show();
                            }
                        }
                    );
                }
                else
                {
                    new ScheduleModel(nodeID, title, config) .init(type, nodeKey, semesterID) .show();
                }
            }
        }
    };
}();

// TODO: used in different files, where to place it?
/**
 * Converts the weekday from string to the corresponding number
 *
 * @param {string} day Weekday as string
 * @return {number} * Weekday as number
 */
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
 * Converts english day names into german
 *
 * @param {String} week_day english day name
 */
// TODO: used in different files, where to place it?
function weekdayEtoD(week_day)
{
    switch (week_day)
    {
        case "monday":
            return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_MONDAY;
        case "tuesday":
            return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_TUESDAY;
        case "wednesday":
            return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_WEDNESDAY;
        case "thursday":
            return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_THURSDAY;
        case "friday":
            return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_FRIDAY;
        case "saturday":
            return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_SATURDAY;
        case "sunday":
            return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_SUNDAY;
        default:
            return false;
    }
}

/**
 *  Return the start and end time of a requested block
 *
 * @method blocktotime
 * @param {Number} block The block number
 * @returns {mixed} * The Object with start and end time else false
 */
// TODO: used in different files, where to place it?
function blocktotime(block, scheduleGrid)
{
    if (typeof block !== "undefined" && typeof MySched.gridData[scheduleGrid][block] !== "undefined")
    {
        return { 0: addColonToTime(MySched.gridData[scheduleGrid][block].starttime), 1: addColonToTime(MySched.gridData[scheduleGrid][block].endtime) };
    }
    return false;
}

/**
 *  Takes a time string (xxxx) and will return xx:xx
 *
 * @method addColonToTime
 * @param {string} time The time string
 * @returns {string} * The time string with a colon
 */
function addColonToTime(time)
{
    var colon = ":"
    var position = 2;
    return [time.slice(0, position), colon, time.slice(position)].join('');
}

/**
 *  Function to get the blocks of a schedule grid
 *
 * @method getGridLength
 * @param {string} scheduleGrid The name of a schedule grid
 * @returns {integer} * The number of blocks in a schedule grid if the grid is undefined returns false.
 */
function getGridBlocks(scheduleGrid)
{
    if(typeof MySched.gridData[scheduleGrid] !== "undefined")
    {
        return Object.keys(MySched.gridData[scheduleGrid]).length;
    }
    return false;
}

// I don't know if it is even called once, so I don't care what it is doing for now
Ext.ux.collapsedPanelTitlePlugin = function ()
{
    this.init = function (p)
    {
        if (p.collapsible)
        {
            var r = p.region;
            if ((r === 'north') || (r === 'south'))
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
};

// TODO: Something is checked here but I do not know what or what the sense of this is
function checkStartup(checkfor, type)
{
    if (!Ext.isString(type))
    {
        type = null;
    }
    if (Ext.isObject(MySched.startup) === true && Ext.isObject(MySched.startup[checkfor]))
    {
        if (type === null && MySched.startup[checkfor].success === true)
        {
            return true;
        }
        else if (MySched.startup[checkfor][type].success === true)
        {
            return true;
        }
    }
    return false;
}
