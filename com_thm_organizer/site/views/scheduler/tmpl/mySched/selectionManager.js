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

            if (Ext.typeOf(o) === 'object')
            {
                // Only below the given object
                var dom = o.dom || Ext.get(o).dom;
                Ext.select('.status_icons_delete', false, dom).clearListeners();
                Ext.select('.status_icons_edit', false, dom).clearListeners();
                Ext.select('.teachername', false, dom).clearListeners();
                Ext.select('.lectureBox', false, dom).clearListeners();
                Ext.select('.conMenu', false, dom).clearListeners();
                Ext.select('.MySchedEvent_joomla', false, dom).clearListeners();
                Ext.select('.lecturename', false, dom).clearListeners();
                Ext.select('.roomname', false, dom).clearListeners();
                Ext.select('.poolname', false, dom).clearListeners();
                Ext.select('.status_icons_add', false, dom).clearListeners();
            }
            else if (o === true)
            {
                // All
                Ext.select('.status_icons_delete').clearListeners();
                Ext.select('.status_icons_edit').clearListeners();
                Ext.select('.teachername').clearListeners();
                Ext.select('.lectureBox').clearListeners();
                Ext.select('.conMenu').clearListeners();
                Ext.select('.MySched_event_joomla').clearListeners();
                Ext.select('.lecturename').clearListeners();
                Ext.select('.roomname').clearListeners();
                Ext.select('.poolname').clearListeners();
                Ext.select('.status_icons_add').clearListeners();
            }
            else if (MySched.layout.tabpanel.items.getCount() > 0)
            {
                // only the active tab
                var activeTabDom = MySched.layout.tabpanel.getActiveTab().getEl().dom;
                Ext.select('.status_icons_delete', false, activeTabDom).clearListeners();
                Ext.select('.status_icons_edit', false, activeTabDom).clearListeners();
                //Ext.select('.teachername', false, activeTabDom).clearListeners();
                //Ext.select('.lectureBox', false, activeTabDom).clearListeners();
                //Ext.select('.conMenu', false, activeTabDom).clearListeners();
                Ext.select('.MySchedEvent_joomla', false, activeTabDom).clearListeners();
                //Ext.select('.lecturename', false, activeTabDom).clearListeners();
                //Ext.select('.roomname', false, activeTabDom).clearListeners();
                Ext.select('.poolname', false, activeTabDom).clearListeners();
                Ext.select('.status_icons_add', false, activeTabDom).clearListeners();
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

            // Addes events for the  teacher names
            Ext.select('.teachername', true, tab.dom)
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

            // Adds events to the subject names
            Ext.select('.lecturename', true, tab.dom)
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

            // Adds events to the room names
            Ext.select('.roomname', true, tab.dom)
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

            // Adds events to the pool names
            Ext.select('.modulename', true, tab.dom)
                .on(
                {
                    'click': function (e)
                    {
                        this.showSchedule(e, 'pool');
                    },
                    scope: this
                });

            // Subscribe events for lecture box
            // What is a lecture box?
            Ext.select('.lectureBox', true, tab.dom)
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

            // Adds events to the plus icon
            Ext.select('.status_icons_add', true, tab.dom)
                .on(
                {
                    'click': function (e)
                    {
                        e.stopEvent();
                        this.lecture2ScheduleHandler();
                    },
                    scope: this
                });


            // Assuming this is the context menu -> throws js errors right now
            Ext.select('.conMenu', true, tab.dom)
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
                            MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_NOTICE,
                            MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_LESSON_MODULENR_UNKNOWN);
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
                        MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_NOTICE,
                        MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_LESSON_MODULENR_UNKNOWN);
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

            var tabID = id.split('__')[0];
            var lessonID = id.split('__')[1];

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
