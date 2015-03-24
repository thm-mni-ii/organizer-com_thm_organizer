/**
 * model of the schedule
 *
 * @class ScheduleModel
 * @constructor
 */
Ext.define('ScheduleModel',
    {
        extend: 'MySched.Model',

        /**
         * Setting some variables and calling init
         *
         * @param {String} id TODO: some weired id, don't know where it come from
         * @param {String} title TODO: Looks like joomla language constant
         * @param {String} config TODO: I don't know
         */
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

            if(config && config.grid)
            {
                this.scheduleGrid = config.grid;
            }
            else
            {
                this.scheduleGrid = "Haupt-Zeitraster";
            }

            if (config && config.type && config.value)
            {
                this.init(config.type, config.value);
            }
        },
        /**
         * Setting some variable width information of the display schedule and adding events to the event list
         *
         * @method init
         * @param {String} type Requested resource
         * @param {String} value Value if the resource
         * @param {String} semesterID Id of the semester
         * @returns {ScheduleModel}
         */
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
        /**
         * Adds a lecture to "my schedule"
         *
         * @method addLecture
         * @param {object} l TODO: Looks like data for every block in the selected schedule
         */
        addLecture: function (l)
        {
            // adds a lecture
            this.data.add(l.id, l);

            // thereby, blockCache is getting inconsistent
            this.blockCache = null;
            this.markChanged();

        },
        /**
         * TODO: Don't know what for and if it in use
         *
         * @method clear
         * @returns {boolean} *
         */
        clear: function ()
        {
            this.blockCache = null;
            this.markChanged();
        },
        /**
         * Remove a lecture from "my schedule"
         *
         * @method removeLecture
         * @param {Object} l Object with information above the lecture and block
         */
        removeLecture: function (l)
        {

            if (this.blockCache && Ext.typeOf(l) === 'object')
            {
                this.blockCache[l.getWeekDay()][l.getBlock() - 1]--;
            }

            if (Ext.typeOf(l) === 'object')
            {
                this.data.removeAtKey(l.getId());
            }
            else
            {
                this.data.removeAtKey(l);
            }

            this.markChanged();
        },
        /**
         * Return the lecture with the id
         *
         * @method getLecture
         * @param {String} id The id of the lecture (includes semester, day, branch)
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
            if (Plesson !== null && Ext.isDefined(Plesson) && Ext.isDefined(Plesson.data) && Plesson.data !== null && Plesson.data.type === "personal")
            {
                return MySched.Schedule.data.get(id);
            }
            return this.data.get(id);
        },
        /**
         * checks if data is empty
         *
         * @method isEmpty
         * @returns {Boolean} * boolean if data is empty or not
         */
        isEmpty: function ()
        {
            return this.data.isEmpty();
        },
        /**
         * Returns lectures of a specified type
         *
         * @method getLectures
         * @param {Object} type Type of the ressource (e.g. room, pool, ...)
         * @param {Object} value The values of the specified type
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
        /**
         * Get all events of the current week and add them to the grid
         *
         * @method getGridData
         * @return {{}[]} TODO
         */
        getGridData: function ()
        {
            var scheduleGridLength = getGridBlocks(this.scheduleGrid);

            var ret = [];

            for (var i = 0; i < scheduleGridLength; i++)
            {
                ret.push({})
            }

            // Need a fix format for the grid
            // sporadic, not regular events
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

            // If the semester hast not began, show message box and ask if user want to jump to beginning of semester
            if (wp < begin && MySched.displaySemesterBeginDialog)
            {
                MySched.displaySemesterBeginDialog = false;
                Ext.MessageBox.show(
                    {
                        title: MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_SEMESTER_NOT_STARTED,
                        cls: "mysched_semesterbegin",
                        buttons: Ext.MessageBox.YESNO,
                        msg: MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_SEMESTER_JUMP_TO_START,
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
                    }
                );
            }

            // add sporadic events
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

                // Events was before the current week
                if (eventStartDate < currMOFR.monday && eventEndDate < currMOFR.monday)
                {
                    return true;
                }

                // Event is after the current week
                if (eventStartDate > currMOFR.friday && eventEndDate > currMOFR.friday)
                {
                    return true;
                }

                // Event runs longer than the current week
                if (eventStartDate <= currMOFR.monday && eventEndDate >= currMOFR.friday)
                {
                    eventStartDateInCurrentWeek = currMOFR.monday;
                    eventEndDateInCurrentWeek = currMOFR.friday;
                } // Event is just in this week
                else if (currMOFR.monday <= eventStartDate && currMOFR.friday >= eventEndDate)
                {
                    eventStartDateInCurrentWeek = eventStartDate;
                    eventEndDateInCurrentWeek = eventEndDate;
                } // Event begins before current week and end after the current week
                else if (eventStartDate <= currMOFR.monday && eventEndDate <= currMOFR.friday)
                {
                    eventStartDateInCurrentWeek = currMOFR.monday;
                    eventEndDateInCurrentWeek = eventEndDate;
                } // Event begins in the current week but does not end in the current week
                else if (eventStartDate >= currMOFR.monday && eventEndDate >= currMOFR.friday)
                {
                    eventStartDateInCurrentWeek = eventStartDate;
                    eventEndDateInCurrentWeek = currMOFR.friday;
                }

                // start and/or end date aren't set
                if (eventStartDateInCurrentWeek === null || eventEndDateInCurrentWeek === null)
                {
                    return true;
                }

                // start is after end date
                if (eventStartDateInCurrentWeek > eventEndDateInCurrentWeek)
                {
                    return true;
                }

                while(eventStartDateInCurrentWeek <= eventEndDateInCurrentWeek)
                {
                    // get weekday
                    var dow = Ext.Date.format(eventStartDateInCurrentWeek, "l");
                    dow = dow.toLowerCase();

                    // get block
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
            // add cyclic events
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
                                        if (Ext.isString(block.lessonData.delta))
                                        {
                                            if (block.lessonData.delta === "removed")
                                            {
                                                if (displayDelta() === false)
                                                {
                                                    continue;
                                                }
                                            }
                                        }
                                        if (this.type === "room")
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

                                        if (displayLesson === true)
                                        {
                                            block = parseInt(blockIndex) - 1;

                                            if(this.scheduleGrid !== v.data.grid)
                                            {
                                                var lessonGridOverlaps = true;
                                                var lessonTime = blocktotime(blockIndex, v.data.grid);
                                                var scheduleGridLength = getGridBlocks(this.scheduleGrid);

                                                for(var gridIndex = 1; gridIndex <= scheduleGridLength; gridIndex++)
                                                {
                                                    block = gridIndex - 1;
                                                    var scheduleTime = blocktotime(gridIndex, this.scheduleGrid);
                                                    if(scheduleTime === false)
                                                    {
                                                        lessonGridOverlaps = false;
                                                        continue;
                                                    }

                                                    var cond1 = (scheduleTime[0] < lessonTime[0] && scheduleTime[1] < lessonTime[0]);
                                                    var cond2 = (scheduleTime[0] > lessonTime[1]);

                                                    if (!(cond1 || cond2)) {
                                                        if (!ret[block][dow]) {
                                                            ret[block][dow] = [];
                                                        }

                                                        ret[(block)][dow].push(v.getCellView(this, block, dow));
                                                    }
                                                    else
                                                    {
                                                        lessonGridOverlaps = false;
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                if (!ret[block][dow])
                                                {
                                                    ret[block][dow] = [];
                                                }

                                                ret[block][dow].push(v.getCellView(this, block, dow));
                                            }

                                            this.visibleLessons.push(v.data);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }, this);
            for(var returnIndex = 0; returnIndex < ret.length; returnIndex++)
            {
                for(var dowIndex in ret[returnIndex])
                {
                    if (Ext.isArray(ret[returnIndex][dowIndex]))
                    {
                        for(var blockIndex = 0; blockIndex < ret[returnIndex][dowIndex].length; blockIndex++)
                        {
                            var singleString = ret[returnIndex][dowIndex][blockIndex];
                            if (singleString.indexOf('<div id="MySchedEvent_') === 0)
                            {
                                for(var blockIndexSearch = 0; blockIndexSearch < ret[returnIndex][dowIndex].length; blockIndexSearch++)
                                {
                                    if (blockIndex !== blockIndexSearch)
                                    {
                                        var searchString = ret[returnIndex][dowIndex][blockIndexSearch];

                                        var modifiedSingleString = singleString.replace(new RegExp('<small class="event_resource">.*', "g"), "");

                                        if (searchString.indexOf(modifiedSingleString) !== -1)
                                        {
                                            delete ret[returnIndex][dowIndex][blockIndex];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            this.htmlView = Ext.clone(ret);
            return ret;
        },
        /**
         * TODO: Get some data
         *
         * @method load
         * @param {String} url Url of the target of the ajax request
         * @param {String} type TODO: Maybe type of the request
         * @param {String} cb Callback function
         * @param {Object} scope TODO: Inforamtion above the session and the user
         * @param {String} username Username of the registered user
         * @param tmi TODO: Was undefined
         */
        load: function (url, type, cb, scope, username, tmi)
        {
            //TODO
            console.log("What is this?: " + tmi);
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
         * Check whether existing events yet exist
         * TODO: Maybe this method is not in use anymore?
         *
         * @method checkLectureVersion
         * @param {Object} against Sum of all existing events
         */
        checkLectureVersion: function (against)
        {
            var ret = {};
            console.log("scheduleModle.js checkLectureVersion: is it in use anymore?");
            this.data.each(function (v)
            {
                v.data.css = "";
            });
            var newdatas = this.data.clone();
            var funcsort = function numsort(a, b)
            {
                if (a.data.subject.toString() > b.data.subject.toString())
                {
                    return 1; // a is for b
                }
                else if (a.data.subject.toString() < b.data.subject.toString())
                {
                    return -1; // b is for a
                }
                return 0; // nothing happend
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
                    (against.data.get(this.data.items[i].id).pool.keys.toString() !== this.data.items[i].pool.keys.toString() ||
                    against.data.get(this.data.items[i].id).teacher.keys.toString() !== this.data.items[i].teacher.keys.toString() ||
                    against.data.get(this.data.items[i].id).room.keys.toString() !== this.data.items[i].room.keys.toString()))
                {
                    // Something has changed
                    newdatas.removeAtKey(this.data.items[i].id);
                    newdatas.add(this.data.items[i].id, against.data.get(this.data.items[i].id));
                    newdatas.get(this.data.items[i].id).data.css = "movedto";
                }
                else
                {
                    // event no longer exists :(
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
         * Checks different preconditions
         *
         * @param {Object} o TODO: looks like an ajax request
         * @param {Object} arg TODO: was undefined
         * @return {] * TODO; was undefined
         */
        preParseLectures: function (o, arg)
        {
            //TODO
            console.log("What is this?: " + arg);
            // Call function after Auth and delete it -> clicked save
            if (MySched.Authorize.afterAuthCallback)
            {
                MySched.Authorize.afterAuthCallback();
                MySched.Authorize.afterAuthCallback = null;
            }
            return this.parseLectures(o);
        },
        /**
         * TODO: Maybe not in use anymore
         *
         * @method loadsavedLectures
         * @param o
         * @param arg
         */
        loadsavedLectures: function (o, arg)
        {
            //TODO
            console.log("(scheduleModel) loadsavedLectures. maybe never used?");
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
         * Callback to parse the XML file from lecture
         *
         * @method parseLectures
         * @param {Object} o Ajax request
         */
        parseLectures: function (o)
        {
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
                if (MySched.scheduleDataReady === true)
                {
                    this.refreshView();
                }
            }
            else
            {
                var grid = MySched.Schedule.show(true);
                Ext.apply(grid,
                    {
                        closable: false,
                        tabTip: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE,
                        iconCls: 'myScheduleIcon'
                    }
                );
                MySched.layout.createTab('mySchedule', MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE, grid, "mySchedule");
            }
            // Activate buttons if not empty
            if (!MySched.Schedule.isEmpty() && MySched.FPDFInstalled)
            {
                Ext.ComponentMgr.get('btnEmpty').enable();
                Ext.ComponentMgr.get('btnPdf').enable();
                if (_C('enableSubscribing'))
                {
                    Ext.ComponentMgr.get('btnSub').enable();
                }
            }

            // tab 'My schedule' will be DropArea
            var tabID = MySched.layout.tabpanel.getComponent('mySchedule')
                .tab.el.dom;
            var dropTarget = new Ext.dd.DropTarget(tabID, MySched.Base.getDropConfig());
            this.blockCache = null;
            this.markUnchanged();
        },
        /**
         * Callback zum parsen der XML Datei in Lecture
         * TODO: myabe this method is not in use anymore
         *
         * @param {Object} o
         * @param {Object} arg
         */
        parseLecturesdiff: function (o, arg)
        {
            //TODO
            console.log("(scheduleModel) parseLecturesdiff. maybe never used?");
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
        /**
         * Creates a new tab at the schedule and shows it
         *
         * @method show
         * @param {boolean} ret TODO
         * @param {} closeable TODO: is it in use anymore?
         */
        show: function (ret, closeable)
        {
            // TODO
            //console.log("What is this?: " + closeable);
            //console.log(this.getGridData());
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
        /**
         * Refresh the schedule tab every time you change it
         *
         * @method refreshView
         * @return {function} this.show() Return the function that displays a tab
         */
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
        /**
         * Returns the status of the blockCache
         *
         * @param {Number} wd Weekday
         * @param {Number} block number of the block
         * @return {Number} * Returns value of blockCache or if not set 0
         */
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

            // Numeric index allowed
            if (weekdays[wd])
            {
                wd = weekdays[wd];
            }
            if (this.getBlockCache(true)[wd] && this.blockCache[wd][block])
            {
                return this.blockCache[wd][block];
            }
            return 0;
        },
        /**
         * Recreate the BlockCache new
         *
         * @method getBlockCache
         * @param {boolean} forceGenNew TODO: It has something to do with the BlockCache
         * @return {number} * BlockCache
         */
        getBlockCache: function (forceGenNew)
        {
            // Generate the BlockCache new of necessary
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
                                if (calendarDates[dateIndex][l.getBlock()].lessonData.delta !== "removed")
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
        /**
         * TODO: I think it checks which lectures are already in "My schedule"
         *
         * @param {object} l Lecture object
         * @return {boolean} * True if the lecture exists otherwise false
         */
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
        /**
         * TODO
         *
         * @method markChanged
         */
        markChanged: function ()
        {
            if (this.changed)
            {
                return;
            }
            this.changed = true;
        },
        /**
         * TODO
         *
         * @method markUnchanged
         */
        markUnchanged: function ()
        {
            if (!this.changed)
            {
                return;
            }
            this.changed = false;
        },
        /**
         * Check if the user is registered and sends an ajax request to save the data
         *
         * @method save
         * @param {String} url Target of the ajax request
         * @param {Object} success TODO: Don't know
         * @param {String} scheduletask The task that is performed
         */
        save: function (url, success, scheduletask)
        {
            // check if the user is registered
            if (MySched.Authorize.user !== null)
            {
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
                    }
                );
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
        /**
         * Converts data to an array
         *
         * @method asArray
         * @return {Array} asArrRet Data as an array
         */
        asArray: function ()
        {
            var asArrRet = {};
            var d = this.data;

            for(var index = 0; index < d.length; index++)
            {
                var lesson = d.items[index];
                if (MySched.Base.schedule.data.containsKey(lesson.id) === false)
                {
                    continue;
                }
                asArrRet[lesson.id] = Ext.clone(lesson.data);
                asArrRet[lesson.id].pools = asArrRet[lesson.id].pools.map;
                asArrRet[lesson.id].teachers = asArrRet[lesson.id].teachers.map;
                asArrRet[lesson.id].subjects = asArrRet[lesson.id].subjects.map;
                asArrRet[lesson.id].rooms = asArrRet[lesson.id].rooms.map;
            }

            return asArrRet;
        },
        /**
         * Creates a array with all blocks of my schedule.
         *
         * @method asArrayForPDF
         * @return {Array} asArrRet Array with all lectures of MySchedule
         */
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
                                        if (date[blockIndex].lessonData.delta && date[blockIndex].lessonData.delta === "removed")
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
        /**
         * Returns all keys of the lessons
         *
         * @method getLessonKeys
         * @return {Function} * Returns all keys
         */
        getLessonKeys: function ()
        {
            return this.data.keys;
        },
        /**
         * TODO: It seems to be imn use, but I don't know what is does. Looks like creating an array with type personal
         *
         * @method asPersArray
         * @return {Array} this.asArrRet
         */
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
    }
);
