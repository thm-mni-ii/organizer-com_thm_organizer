/*global Ext: false, MySched: false, MySchedLanguage: false */
"use strict";
var oldMainToolbar = MySched.layout.getMainToolbar;

MySched.layout.getMainToolbar = function ()
{
    var btnEvent = Ext.create('Ext.Button',
    {
        // Event anlegen
        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EVENT_CREATE,
        id: 'btnEvent',
        hidden: true,
        iconCls: 'tbEvent',
        handler: addNewEvent
    });
    var ToolbarObjects = oldMainToolbar();
    var newMainToolbar = ToolbarObjects.AddTo(3, btnEvent);
    return newMainToolbar;
};

var addEvent = {
    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EVENT_CREATE,
    icon: MySched.mainPath + "images/calendar_add.png",
    handler: function ()
    {
        addNewEvent(null, MySched.BlockMenu.day, MySched.BlockMenu.stime,
        MySched.BlockMenu.etime);
    },
    xtype: "button"
}

//MySched.BlockMenu.Menu[MySched.BlockMenu.Menu.length] = addEvent;

window.onbeforeunload = function ()
{
    if (typeof MySched.layout.tabpanel === "undefined") return;
    var tabs = MySched.layout.tabpanel.items.items;
    var temptabs = tabs;
    var check = false;
    var tosave = false;
    var i = 0;
    var ti = 0;

    for (i = 0; i < tabs.length; i++)
    {
        if (tabs[i].mSchedule.status === "unsaved")
        {
            check = confirm(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULER_CHANGED);
            if (check === true)
            {
                for (ti = 0; ti < temptabs.length; ti++)
                {
                    if (temptabs[ti].mSchedule.status === "unsaved")
                    {
                        if (temptabs[ti].mSchedule.id === "mySchedule")
                        {
                            temptabs[ti].mSchedule.save(_C('ajaxHandler'),
                            false, "UserSchedule.save");
                        }
                        else
                        {
                            temptabs[ti].mSchedule.save(_C('ajaxHandler'),
                            false, "saveScheduleChanges");
                        }
                        tosave = true;
                    }
                }
                break;
            }
            else
            {
                break;
            }
        }
    }

    if (check === true && tosave === true)
    {
        var jetzt = new Date();
        var sek = jetzt.getSeconds();
        var undjetzt = new Date();
        var undsek = undjetzt.getSeconds();
        sek = sek + 3;
        while (sek % 60 > undsek)
        {
            undjetzt = new Date();
            undsek = undjetzt.getSeconds();
        }
    }
};