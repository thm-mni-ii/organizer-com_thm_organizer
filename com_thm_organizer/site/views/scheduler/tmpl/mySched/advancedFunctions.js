/**
 * This is a collection of function which deals with the creating a event button and the save changes if you leave.
 *
 */
var oldMainToolbar = MySched.layout.getMainToolbar;

/**
 * Creates the "create event" button and returns the toolbar
 *
 * @method MySched.layout.getMainToolbar
 * @return {object} newMainToolbar
 */
MySched.layout.getMainToolbar = function ()
{
    var btnEvent = Ext.create('Ext.Button',
    {
        // Create event
        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EVENT_CREATE,
        id: 'btnEvent',
        hidden: false,
        iconCls: 'tbEvent',
        handler: addNewEvent
    });
    var ToolbarObjects = oldMainToolbar();
    var newMainToolbar = ToolbarObjects.AddTo(5, btnEvent);
    return newMainToolbar;
};

/**
 * Object for the add event button
 */
var addEvent = {
    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EVENT_CREATE,
    icon: MySched.mainPath + "images/calendar_add.png",
    handler: function ()
    {
        addNewEvent(null, MySched.BlockMenu.day, MySched.BlockMenu.stime,
        MySched.BlockMenu.etime);
    },
    xtype: "button"
};

MySched.BlockMenu.Menu[MySched.BlockMenu.Menu.length] = addEvent;

/**
 * This method is called when the user leaves the page and checks if there are unsaved changes at the schedule and
 * informs the user.
 *
 * @method onbeforeunload
 */
window.onbeforeunload = function ()
{
    if (typeof MySched.layout.tabpanel === "undefined")
    {
        return;
    }
    var tabs = MySched.layout.tabpanel.items.items;
    var temptabs = tabs;
    var check = false;
    var tosave = false;
    var i = 0;
    var ti = 0;

    for (i = 0; i < tabs.length; i++)
    {
        if (tabs[i].ScheduleModel.status === "unsaved")
        {

            return MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULER_CHANGED;
        }
    }
};