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