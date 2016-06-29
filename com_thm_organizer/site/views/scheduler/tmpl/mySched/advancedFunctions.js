/**
 * This method is called when the user leaves the page and checks if there are unsaved changes at the schedule and
 * informs the user.
 *
 * @method onbeforeunload
 */
window.onbeforeunload = function ()
{
    var tabs, i;

    if (typeof MySched.layout.tabpanel === "undefined")
    {
        return;
    }

    tabs = MySched.layout.tabpanel.items.items;

    for (i = 0; i < tabs.length; i++)
    {
        if (tabs[i].ScheduleModel.status === "unsaved")
        {

            return MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_CHANGED;
        }
    }
};