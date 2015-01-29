/**
 * Controls the selection of the events and the "hoverbuttons"
 *
 * @class MySched.SelectionManager
 * @constructor
 */
MySched.SelectionManager = Ext.apply(new Ext.util.Observable(),
    {
        selectEl: null,
        hoverEl: new MySched.Collection(),
        selectButton: null,
        selectLectureId: null,
        lectureAddButton: externLinks.lectureAddButton,
        lectureRemoveButton: externLinks.lectureRemoveButton,
        /**
         * Initialize
         *
         * @method init
         */
        init: function ()
        {
            "use strict";

            // defines which event will be thrown
            this.addEvents(
                {
                    beforeSelect: true,
                    select: true,
                    beforeUnselect: true,
                    unselect: true,
                    lectureAdd: true,
                    lectureDel: true
                }
            );
        },
        /**
         * Stops the selection
         *
         * @method stopSelection
         * @param {Object} o This can have the following values
         *                      o === empty => for the active tab
         *                      o === true => for the document
         *                      o === Ext.Element|Node => just for this
         *                      TODO: this is the literal translation and yes, it makes no sense to me too. At tests it
         *                      was always empty
         */
        stopSelection: function (o)
        {
            "use strict";

            if (Ext.type(o) === 'object')
            {
                // Only below the given object
                var dom = o.dom || Ext.get(o).dom;
                Ext.select('.status_icons_delete', false, dom).removeAllListeners();
                Ext.select('.status_icons_info', false, dom).removeAllListeners();
                Ext.select('.status_icons_edit', false, dom).removeAllListeners();
                Ext.select('.teachername', false, dom).removeAllListeners();
                Ext.select('.lectureBox', false, dom).removeAllListeners();
                Ext.select('.conMenu', false, dom).removeAllListeners();
                Ext.select('.MySchedEvent_joomla', false, dom).removeAllListeners();
                Ext.select('.lecturename', false, dom).removeAllListeners();
                Ext.select('.roomname', false, dom).removeAllListeners();
                Ext.select('.poolname', false, dom).removeAllListeners();
                Ext.select('.status_icons_add', false, dom).removeAllListeners();
                Ext.select('.status_icons_info', false, dom).removeAllListeners();
                Ext.select('.status_icons_estudy', false, dom).removeAllListeners();
            }
            else if (o === true)
            {
                // All
                Ext.select('.status_icons_delete').removeAllListeners();
                Ext.select('.status_icons_info').removeAllListeners();
                Ext.select('.status_icons_edit').removeAllListeners();
                Ext.select('.teachername').removeAllListeners();
                Ext.select('.lectureBox').removeAllListeners();
                Ext.select('.conMenu').removeAllListeners();
                Ext.select('.MySched_event_joomla').removeAllListeners();
                Ext.select('.lecturename').removeAllListeners();
                Ext.select('.roomname').removeAllListeners();
                Ext.select('.poolname').removeAllListeners();
                Ext.select('.status_icons_add').removeAllListeners();
                Ext.select('.status_icons_info').removeAllListeners();
                Ext.select('.status_icons_estudy').removeAllListeners();
            }
            else if (MySched.layout.tabpanel.items.getCount() > 0)
            {
                // only the active tab
                var activeTabDom = MySched.layout.tabpanel.getActiveTab().getEl().dom;
                Ext.select('.status_icons_delete', false, activeTabDom).removeAllListeners();
                Ext.select('.status_icons_info', false, activeTabDom).removeAllListeners();
                Ext.select('.status_icons_edit', false, activeTabDom).removeAllListeners();
                Ext.select('.teachername', false, activeTabDom).removeAllListeners();
                Ext.select('.lectureBox', false, activeTabDom).removeAllListeners();
                Ext.select('.conMenu', false, activeTabDom).removeAllListeners();
                Ext.select('.MySchedEvent_joomla', false, activeTabDom).removeAllListeners();
                Ext.select('.lecturename', false, activeTabDom).removeAllListeners();
                Ext.select('.roomname', false, activeTabDom).removeAllListeners();
                Ext.select('.poolname', false, activeTabDom).removeAllListeners();
                Ext.select('.status_icons_add', false, activeTabDom).removeAllListeners();
                Ext.select('.status_icons_info', false, activeTabDom).removeAllListeners();
                Ext.select('.status_icons_estudy', false, activeTabDom).removeAllListeners();
            }
        },
        /**
         * Starts the selection
         *
         * @method startSelection
         * @param {Object} el Tab for that the selection should be started. If it is empty, then it should start for the
         *                      active tab
         */
        startSelection: function (el)
        {
            "use strict";

            var tab = el || MySched.layout.tabpanel.getActiveTab().getEl();
            if (!tab)
            {
                return;
            }

            Ext.select('.status_icons_info', false, tab.dom)
                .on(
                {
                    'click': function (e)
                    {
                        if (e.button === 0)
                        {
                            e.stopEvent();
                            this.showModuleInformation(e);
                        }
                    },
                    scope: this
                });

            // Subscribes event for teacher names
            Ext.select('.teachername', false, tab.dom)
                .on(
                {
                    'click': function (e)
                    {
                        if (e.button === 0)
                        {
                            this.showSchedule(e, 'teacher');
                        }
                    },
                    scope: this
                });

            // Subscribe events for lecturenames
            Ext.select('.lecturename', false, tab.dom).on(
                {
                    'mouseover': function (e)
                    {
                        e.stopEvent();
                        this.showInformation(e);
                    },
                    'mouseout': function ()
                    {
                        var contentAnchorTip = Ext.getCmp('content-anchor-tip');
                        if (contentAnchorTip)
                        {
                            contentAnchorTip.destroy();
                        }
                    },
                    'click': function (e)
                    {
                        e.stopEvent();
                        this.showModuleInformation(e);
                    },
                    scope: this
                });

            // Subscribe events for event names
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
                        if (contentAnchorTip)
                        {
                            contentAnchorTip.destroy();
                        }
                    },
                    'click': function (e)
                    {
                        if (e.button === 0)
                        {
                            e.stopEvent();
                        }
                        if (MySched.Authorize.user !== null && MySched.Authorize.role !== 'user' && MySched.Authorize.role !== 'registered')
                        {
                            addNewEvent(e.target.id);
                        }
                    },
                    scope: this
                });

            // Subscribe events for room names
            Ext.select('.roomname', false, tab.dom)
                .on(
                {
                    'click': function (e)
                    {
                        // links Klick
                        if (e.button === 0)
                        {
                            this.showSchedule(e, 'room');
                        }
                    },
                    scope: this
                });

            // Subscribe events for teacher things?
            // TODO: original comment!!! From now on we guessing what our code will do... no words
            Ext.select('.modulename', false, tab.dom)
                .on(
                {
                    'click': function (e)
                    {
                        this.showSchedule(e, 'module');
                    },
                    scope: this
                });

            // Subscribe events for lecture box
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

            // Subscribe events for
            // TODO original comment: Aboniert Events der Veranstaltungsboxen
            // TODO It was copy & paste from above
            Ext.select('.status_icons_add', false, tab.dom)
                .on(
                {
                    'click': function (e)
                    {
                        e.stopEvent();
                        this.lecture2ScheduleHandler();
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
        /**
         * Gets all the data if a new schedule is opend by clicking on an item in an schedule
         *
         * @method showSchedule
         * @param {object} e Mouse Event
         * @param {string} type Type of the ressource (e.g. module)
         */
        showSchedule: function (e, type)
        {
            "use strict";

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

            if(type === "teacher")
            {
                parent = MySched.Mapping.getTeacherParent(id);
            }
            else if(type === "pool")
            {
                parent = MySched.Mapping.getPoolParent(id);
            }
            else if(type === "room")
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
        /**
         * TODO Could not been tested but should be in use
         *
         * @method showEventInformation
         * @param e
         */
        showEventInformation: function (e)
        {
            console.log(e);
            "use strict";

            var el = e.getTarget('.MySchedEvent_joomla', 5, true);
            if (!el)
            {
                el = e.getTarget('.MySchedEvent_name', 5, true);
            }
            if (Ext.getCmp('content-anchor-tip'))
            {
                Ext.getCmp('content-anchor-tip').destroy();
            }
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
        /**
         * Gets data for the clicked module and opens the curriculum of this module
         * TODO The result was an empty page. Maybe something is wrong. But does work at productive page
         *
         * @method showModuleInformation
         * @param {object} e Mouse event
         */
        showModuleInformation: function (e)
        {
            "use strict";

            var id, el;
            if (typeof e === "undefined")
            {
                if (this.selectLectureId)
                {
                    id = this.selectLectureId;
                    el = Ext.get(id);
                }
                else
                {
                    el = this.selectEl;
                    id = el.id;
                }
            }
            else
            {
                if(e.getTarget)
                {
                    el = e.getTarget('.lectureBox', 5, true);
                }
                else
                {
                    el = e;
                }
            }

            var l = MySched.selectedSchedule.getLecture(el.id);
            var subjects = l.data.subjects;
            var subjectNo = null;

            this.showSubjectNoMenu(subjects, e);
        },
        /**
         * TODO Don't know what it does exactly
         *
         * @method showSubjectNoMenu
         * @param {object} subjects Object with information about the subject
         * @param {object} e Mouse event
         */
        showSubjectNoMenu: function(subjects, e)
        {
            "use strict";

            var link;

            subjectNo = MySched.Mapping.getSubjectNo(subjects.keys[0]);

            destroyMenu();

            var menuItems = [];

            for (var subject in subjects.map)
            {
                if(Ext.isString(subject))
                {
                    if(subjects.map[subject] !== "removed")
                    {
                        menuItems[menuItems.length] = {
                            id: MySched.Mapping.getSubjectNo(subject),
                            text: MySched.Mapping.getSubjectName(subject),
                            icon: MySched.mainPath + "images/clasIcon.png",
                            handler: subjectNoHandler,
                            xtype: "button"
                        };
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
                if(menuItems.length === 1)
                {
                    var subjectNo = MySched.Mapping.getSubjectNo(subjects.keys[0]);

                    if (subjectNo === subjects.keys[0])
                    {
                        Ext.Msg.alert(
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NOTICE,
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_MODULENR_UNKNOWN);
                        return;
                    }

                    link = MySched.Mapping.getSubjectLink(subjects.keys[0]);
                    if (link !== '')
                    {
                        window.open(link);
                    }
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
        /**
         * Shows the tooltip if you hover a module at the schedule.
         *
         * @method showInformation
         * @param {object} e Mouse event
         */
        showInformation: function (e)
        {
            "use strict";

            var id, el;
            if (typeof e === "undefined")
            {
                id = "";
                if (this.selectLectureId)
                {
                    id = this.selectLectureId;
                    el = Ext.get(id);
                }
                else
                {
                    el = this.selectEl;
                    id = el.id;
                }
            }
            else
            {
                el = e.getTarget('.lectureBox', 5, true);
            }

            if (Ext.getCmp('content-anchor-tip'))
            {
                Ext.getCmp('content-anchor-tip').destroy();
            }

            var xy = el.getXY();
            xy[0] = xy[0] + el.getWidth() + 10;

            var l = MySched.selectedSchedule.getLecture(el.id);
            var title = l.data.desc;
            if (l.longname !== "")
            {
                title = l.longname;
            }

            var ttInfo = Ext.create(
                'Ext.tip.ToolTip',
                {
                    title: '<div class="mySched_lesson_tooltip"> ' + l.data.desc + " " + '</div>',
                    id: 'content-anchor-tip',
                    target: el.id,
                    anchorToTarget: true,
                    html: l.showInfoPanel(),
                    autoHide: false,
                    cls: "mySched_tooltip_index"
                }
            );

            ttInfo.showAt(xy);
        },
        /**
         * Wenn the MoudeOver event was called
         *
         * @method onMouseOver
         * @param {Object} e Mouse Event
         * TODO: I think it is not in use anymore
         */
        onMouseOver: function (e)
        {
            console.log("selectManager.js onMouseOver: is it in use anymore?");
            "use strict";

            // Determines active event
            var el = e.getTarget('.lectureBox', 5, true);
            if (el.id.substr(0, 4) !== "delta" && MySched.Authorize.user !== null && MySched.Authorize.role !== "user")
            {
                this.selectLectureId = el.id;
                // If the event exist, set HoverButton on delete
                if (MySched.Schedule.lectureExists(el.id))
                {
                    this.selectButton.dom.src = this.lectureRemoveButton;
                    this.selectButton.dom.qtip = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE_LESSON_REMOVE;
                }
                // Show HoverButton
                this.selectButton.show()
                    .alignTo(el, 'tr-tr', [-4, 5]);
            }
        },
        /**
         * Wenn das MouseOut Event ausgeloest wurde
         *
         * @param {Object} e Event
         * TODO: I think it is not in use anymore
         */
        onMouseOut: function (e)
        {
            console.log("selectManager.js onMouseOut: is it in use anymore?");
            "use strict";

            var el = Ext.get(e.getRelatedTarget());
            // Blendet HoverButton aus, und resetet ihn auf
            // hinzufuegen
            if (!el || el.id !== 'lectureSelectButton')
            {
                this.selectButton.hide();
                this.selectButton.dom.src = this.lectureAddButton;
                this.selectLectureId = null;
                this.selectButton.dom.qtip = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE_LESSON_ADD;
            }
        },
        /**
         * Will be called on click of the HoverButton or on click of the delete button or adds an event
         * TODO original comment; Beim klick auf dem HoverButton ausgeloest, be DD oder Klick auf einen Button Entfernt oder Fuegt Veranstaltung hinzu
         * TODO: I think it deletes a lecture from the schdule and refreshs the view
         *
         * @method lecture2ScheduleHandler
         */
        lecture2ScheduleHandler: function ()
        {
            "use strict";

            // Action is called by HoverButton
            var id, el;
            if (this.selectLectureId)
            {
                id = this.selectLectureId;
                el = Ext.get(id);
                // or over DD or Button list
                // TODO original comment: oder ueber DD oder ButtonLeiste
            }
            else
            {
                el = this.selectEl;
                id = el.id;
            }

            var tabID = id.split('##')[0];
            var lessonID = id.split('##')[1];

            if (el.id.substr(0, 4) !== "delta" && MySched.Authorize.user !== null && MySched.Authorize.role !== "user")
            {
                // delete event
                if (el.hasCls('lectureBox_cho') || tabID === 'mySchedule')
                {
                    if (typeof MySched.Base.getLecture(lessonID) !== "undefined")
                    {
                        MySched.Schedule.removeLecture(MySched.Base.getLecture(lessonID));
                    }
                    else
                    {
                        MySched.Schedule.removeLecture(MySched.Schedule.getLecture(lessonID));
                    }
                    this.selectLectureId = null;
                    this.fireEvent("lectureDel", el);
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
            }
        },
        /**
         * Edit a Lesson
         * TODO: is it in use anymore?
         */
        editLesson: function ()
        {
            console.log("selectionManager editLesson: is it in use anymore");
            "use strict";

            var el, id;
            if (this.selectLectureId)
            {
                id = this.selectLectureId;
                el = Ext.get(id);
                // oder ueber DD oder ButtonLeiste
            }
            else
            {
                el = this.selectEl;
                id = el.id;
            }
            var lesson = MySched.Base.getLecture(id);
            newPEvent(numbertoday(lesson.data.dow),
                lesson.data.stime, lesson.data.etime,
                lesson.data.subject, lesson.data.teacher.replace(/\s+/g, ','), lesson.data.pool.replace(/\s+/g, ','), lesson.data.room.replace(/\s+/g, ','), lesson.data.lock,
                lesson.data.key);
        },
        /**
         * Delete a Lesson
         * @param {String} id the id
         * TODO: is it in use anymore?
         *
         */
        deleteLesson: function (id)
        {
            console.log("selectionManager editLesson: is it in use anymore");
            "use strict";

            var el;
            if (!id)
            {
                if (this.selectLectureId)
                {
                    id = this.selectLectureId;
                    el = Ext.get(id);
                    // oder ueber DD oder ButtonLeiste
                }
                else
                {
                    el = this.selectEl;
                    id = el.id;
                }
            }

            var tab = MySched.layout.tabpanel.getComponent(MySched.Base.schedule.getLecture(id)
                .data.responsible);
            if (tab)
            {
                tab.ScheduleModel.removeLecture(tab.ScheduleModel.getLecture(id));
            }
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
         * Handles the MouseDown Event.
         *
         * @method onMouseDown
         * @param {Object} e Mouse Event
         */
        onMouseDown: function (e)
        {
            "use strict";

            var el = e.getTarget('.lectureBox', 5, true);
            // element is already selected
            if (el === null)
            {
                return;
            }
            // Selects element
            if (!Ext.isEmpty(this.selectEl))
            {
                this.unselect(this.selectEl);
            }
            this.select(el);
            this.selectEl = el;

        },
        /**
         * Handles the double click event
         *
         * @method ondblclick
         * @param {object} e Mouse event
         */
        ondblclick: function (e)
        {
            "use strict";

            this.lecture2ScheduleHandler();
        },
        /**
         * Selects an lecture
         *
         * @method select
         * @param {Object} el lecture element
         */
        select: function (el)
        {
            "use strict";
            if (this.fireEvent("beforeSelect", el) === false)
            {
                return el.addClass('lectureBox_sel');
            }

            this.fireEvent("select", el);
        },
        /**
         * deselect an lecture
         *
         * @method unselect
         * @param {Object} el lecture element
         */
        unselect: function (el)
        {
            "use strict";

            if (el === null && this.selectEl)
            {
                el = this.selectEl;
            }
            else
            {
                return false;
            }
            if (this.fireEvent("beforeUnselect", el) === false)
            {
                return el.removeClass('lectureBox_sel');
            }
            this.fireEvent("unselect", el);
        }
    }
);

/**
 * TODO Could not be tested, but should be in use
 *
 * @param e
 */
function showLessonMenu(e)
{
    console.log("TODO showLessonMenu: make documentation");
    "use strict";

    e.stopEvent();
    var el = e.getTarget('.lectureBox', 5, true);
    var lesson = MySched.Base.getLecture(el.id);
    if (typeof lesson === "undefined")
    {
        lesson = MySched.Schedule.getLecture(el.id);
    }

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
    };

    var deleteLesson = {
        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELETE,
        icon: MySched.mainPath + "images/icon-delete.png",
        handler: function ()
        {
            destroyMenu();
            MySched.SelectionManager.deleteLesson();
        },
        xtype: "button"
    };

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
    };

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
    };

    var estudyLesson = {
        text: "eStudy",
        icon: MySched.mainPath + "images/estudy_logo.jpg",
        handler: function (element, event)
        {
            MySched.SelectionManager.showModuleInformation(this);
        },
        scope: el,
        xtype: "button"
    };

    var infoLesson = {
        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_INFORMATION,
        icon: MySched.mainPath + "images/information.png",
        handler: function (element, event)
        {
            MySched.SelectionManager.showModuleInformation(this);
        },
        scope: el,
        xtype: "button"
    };

    var menuItems = [];

    if (MySched.Authorize.role !== "user")
    {
        // menuItems[menuItems.length] = estudyLesson;
        if (MySched.selectedSchedule.id === "mySchedule" || el.hasCls('lectureBox_cho'))
        {
            menuItems[menuItems.length] = delLesson;
        }
        else if (MySched.selectedSchedule.type !== 'delta')
        {
            menuItems[menuItems.length] = addLesson;
        }

    }
    if ((lesson.data.owner === MySched.Authorize.user && Ext.isDefined(lesson.data.owner)) && lesson.data.owner !== "gpuntis")
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

/**
 * TODO Don't know when it is called
 * @param element
 */
function subjectNoHandler (element)
{
    console.log("TODO subjectNoHandler: make documentation");
    console.log(element);
    var link = MySched.Mapping.getSubjectLink(element.id);
    if (link !== '')
    {
        window.open(link);
    }
}

/**
 * TODO Don't know when it is called and what it does
 * @param e
 */
function showBlockMenu(e)
{
    console.log("TODO showBlockMenu: make documentation");
    "use strict";
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

    if (MySched.BlockMenu.Menu.length > 0)
    {
        menu.showAt(e.getXY());
    }
}

/**
 * TODO Don't know when it is called and what it does
 *
 */
function destroyMenu()
{
    console.log("TODO destroyMenu: make documentation");
    "use strict";
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