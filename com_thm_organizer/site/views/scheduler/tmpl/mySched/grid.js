/*global Ext: false, MySched: false, MySchedLanguage: false, blocktotime: false, weekdayEtoD: false, numbertoday: false, getMonday: false,
_C: false, externalLinks: false, daytonumber: false, externLinks */
/*jshint strict: false */

/**
 * Spezielles Grid Vordefiniert fuer Wochenstruktur mit Veranstaltungen
 *
 * @param {Object} schedObj
 * @param {Object} config
 */
var hideHeaders = false;

Ext.define('SchedGrid',
{
    extend: 'Ext.grid.Panel',

    loadData: function (data)
    {
        if (MySched.daytime.length > 0)
        {
            for (var i = 1; i < MySched.daytime[1].length; i++)
            {
                var index = i - 1;
                data[index].time = MySched.daytime[1][i].stime + '<br/>-<br/>' + MySched.daytime[1][i].etime;
            }
        }

        // Wenn das grid auch angezeigt ist, zeige die Sporatischen
        // Veranstaltungen dazu an
        if (MySched.selectedSchedule.grid === this)
        {
            MySched.layout.viewport.doLayout();
        }
        return this.store.loadData(data);

    },
    /**
     * Leert die aktuell vorhanden Sportaischen Veranstaltungen und setzt die
     * uebergebenen
     *
     * @param {Object} data
     */
    setSporadicLectures: function (data)
    {
        this.sporadics = [];
        if (!data || data.length === 0)
        {
            return;
        }
        Ext.each(data, function (e) { this.sporadics.push(e); }, this);
    }
});

function getSchedGrid()
{
    Ext.create('Ext.data.Store',
    {
        storeId: 'gridStore',
        fields: ['time', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
        data: {
            'items': []
        },
        proxy: {
            type: 'memory',
            reader: {
                type: 'json',
                root: 'items'
            }
        }
    });

    var rowBodyFeature = Ext.create('Ext.grid.feature.RowBody',
    {
        getAdditionalData: function (data, rowIndex, record,
        orig)
        {
            var headerCt = this.view.headerCt,
                colspan = headerCt.getColumnCount();
            if (rowIndex === 2)
            {
                return {
                    rowBody: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LUNCHTIME, // do
                    // something with record
                    rowBodyCls: 'MySched_pause',
                    rowBodyColspan: colspan
                };
            }
        }
    });

    var grid = Ext.create('SchedGrid',
    {
        title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TITLE_UNKNOWN,
        store: Ext.data.StoreManager.lookup('gridStore'),
        height: 440,
        //width: 726,
        columns:
        [
            {
                header: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TIME,
                menuDisabled: true,
                sortable: false,
                dataIndex: 'time',
                renderer: MySched.lectureCellRenderer,
                width: 35
            },
            {
                header: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_MONDAY,
                menuDisabled: true,
                sortable: false,
                dataIndex: 'monday',
                renderer: MySched.lectureCellRenderer,
                flex: 1
            },
            {
                header: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_TUESDAY,
                menuDisabled: true,
                sortable: false,
                dataIndex: 'tuesday',
                renderer: MySched.lectureCellRenderer,
                flex: 1
            },
            {
                header: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_WEDNESDAY,
                menuDisabled: true,
                sortable: false,
                dataIndex: 'wednesday',
                renderer: MySched.lectureCellRenderer,
                flex: 1
            },
            {
                header: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_THURSDAY,
                menuDisabled: true,
                sortable: false,
                dataIndex: 'thursday',
                renderer: MySched.lectureCellRenderer,
                flex: 1
            },
            {
                header: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_FRIDAY,
                menuDisabled: true,
                sortable: false,
                dataIndex: 'friday',
                renderer: MySched.lectureCellRenderer,
                flex: 1
            },
            {
                header: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_SATURDAY,
                menuDisabled: true,
                sortable: false,
                dataIndex: 'saturday',
                renderer: MySched.lectureCellRenderer,
                flex: 1
            }
        ],
        viewConfig:
        {
            features: [rowBodyFeature],
            overItemCls: '', // "disable" row over style
            disableSelection: true,
            style: { overflow: 'auto', overflowX: 'hidden' }
        },
        cls: 'MySched_ScheduleGrid',
        scroll: false

    });
    return grid;
}

function showEventdesc(index)
{
    if (Ext.ComponentMgr.get("datdescription") === null || typeof Ext.ComponentMgr.get("datdescription") === "undefined")
    {
        this.eventWindow = Ext.create('Ext.Window',
        {
            id: "datdescription",
            title: MySched.eventlist[index].title + " - Beschreibung",
            bodyStyle: "background-color: #FFF; padding: 7px;",
            frame: false,
            buttons: [
            {
                text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CLOSE,
                handler: function ()
                {
                    this.eventWindow.close();
                },
                scope: this
            }],
            html: MySched.eventlist[index].datdescription
        });
        this.eventWindow.show();
    }
}

Ext.apply(Ext.form.VTypes,
{
    daterange: function (val, field)
    {
        var date = field.parseDate(val);

        if (!date)
        {
            return;
        }
        if (field.startDateField && (!this.dateRangeMax || (date.getTime() !== this.dateRangeMax.getTime())))
        {
            var start = Ext.getCmp(field.startDateField);
            start.setMaxValue(date);
            start.validate();
            this.dateRangeMax = date;
        }
        else if (field.endDateField && (!this.dateRangeMin || (date.getTime() !== this.dateRangeMin.getTime())))
        {
            var end = Ext.getCmp(field.endDateField);
            end.setMinValue(date);
            end.validate();
            this.dateRangeMin = date;
        }
        /*
         * Always return true since we're only using this vtype to set the
         * min/max allowed values (these are tested for after the vtype test)
         */
        return true;
    },

    password: function (val, field)
    {

        if (field.initialPassField)
        {
            var pwd = Ext.getCmp(field.initialPassField);
            return (val === pwd.getValue());
        }
        return true;
    },

    passwordText: 'Passwords do not match'
});

function addNewEvent(eventid, sdate, stime, etime)
{

    if (Ext.isObject(eventid) || eventid === null || typeof eventid === "undefined")
    {
        eventid = "0";
    }
    else
    {
        eventid = eventid.split("_");
        eventid = eventid[1];
    }

    var weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker').value);

    var adds = "";
    var date = null;

    console.log(MySched.selectedSchedule);
    console.log(MySched.Mapping);

    if (Ext.isString(sdate))
    {
        var daynumber = daytonumber(sdate);

        weekpointer = getMonday(weekpointer);

        for (var i = 0; i < 7; i++)
        {
            if (weekpointer.getDay() === daynumber)
            {
                date = Ext.Date.format(weekpointer, "d.m.Y");
                break;
            }
            else
            {
                weekpointer.setDate(weekpointer.getDate() + 1);
            }
        }
    }
    else
    {
        weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker')
            .value);
        date = Ext.Date.format(weekpointer, "d.m.Y");
    }

    if (typeof etime === "undefined")
    {
        etime = "";
    }
    if (typeof stime === "undefined")
    {
        stime = "";
    }

    if(!Ext.isEmpty(date))
    {
        adds = "&startdate=" + date;
    }
    
    if(!Ext.isEmpty(stime))
    {
        adds += "&starttime=" + stime;
    }
    
    if(!Ext.isEmpty(etime))
    {
        adds += "&endtime=" + etime;
    }

    var key = MySched.selectedSchedule.key;

    if(MySched.selectedSchedule.type === "room")
    {
        var roomID = MySched.Mapping.getRoomDbID(key);
        if(roomID != key)
        {
            adds += "&roomID=" + roomID;
        }
    }
    else if(MySched.selectedSchedule.type === "teacher")
    {
        var teacherID = MySched.Mapping.getTeacherDbID(key);
        if(teacherID != key)
        {
            adds += "&teacherID=" + teacherID;
        }
    }

    window.open(externLinks.eventLink + eventid + adds);
}

/**
 * This function add a hidden input field to the form in the passed iframe
 *
 * @author Wolf
 * @param {object} iframe The iframe which called this function
 */

function newEventonLoad(iframe)
{

    var eventForm = Ext.DomQuery.select('form[id=eventForm]',
    iframe.contentDocument.documentElement);
    eventForm = eventForm[0];

    var cancel = Ext.DomQuery.select('button[id=btncancel]', eventForm);
    cancel = cancel[0];

    if (eventForm !== null && cancel !== null)
    {
        var formparent = eventForm.parentElement;
        if (!Ext.isObject(formparent))
        {
            formparent = eventForm.getParent();
        }
        formparent.style.cssText = "";
        var input = document.createElement("input");
        var parent = cancel.parentElement;
        if (!Ext.isObject(parent))
        {
            parent = cancel.getParent();
        }
        parent.removeChild(cancel);

        input.setAttribute("type", "hidden");
        input.setAttribute("name", "mysched");
        input.setAttribute("value", "1");

        eventForm.appendChild(input);
    }
}

/**
 * Spezieller Renderer fuer die Veranstaltungen
 *
 * @param {Object} data
 * @param {Object} meta
 * @param {Object} record
 * @param {Object} rowIndex
 * @param {Object} colIndex
 * @param {Object} store
 * @param {Object} grid
 */
MySched.lectureCellRenderer = function (data, meta, record, rowIndex, colIndex, store)
{
    function cl(css)
    {
        if (MySched.freeBusyState)
        {
            return css + ' ';
        }
        return css + '_DIS ';
    }

    if (colIndex > 0)
    {
        var times = blocktotime(rowIndex + 1);
        meta.tdAttr = "stime='" + times[0] + "' etime='" + times[1] + "'";
    }

    // show date behind the day
    if (colIndex > 0 && rowIndex === 0)
    {
        var weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker')
            .value);

        weekpointer = getMonday(weekpointer);
        weekpointer.setDate(weekpointer.getDate() + (colIndex - 1));

        var headerCt = this.ScheduleModel.grid.getView().getHeaderCt();

        var header = headerCt.getHeaderAtIndex(colIndex);

        if (Ext.Date.format(Ext.ComponentMgr.get('menuedatepicker').value, "d.m.Y") === Ext.Date.format(weekpointer, "d.m.Y"))
        {
            header.setText("<b>" + weekdayEtoD(numbertoday(colIndex)) + " (" + Ext.Date.format(weekpointer, "d.m.") + ")</b>");
        }
        else
        {
            header.setText(weekdayEtoD(numbertoday(colIndex)) + " (" + Ext.Date.format(weekpointer, "d.m.") + ")");
        }
    }

    if (colIndex === 0)
    {
        return '<div class="scheduleBox timeBox">' + data + '</div>';
    }

    var blockStatus = MySched.Schedule.getBlockStatus(colIndex, rowIndex);
    if (blockStatus === 1 && this.ScheduleModel.id !== "mySchedule")
    {
        meta.tdCls += cl('blockBusy');
        meta.tdCls += cl('conMenu');
    }
    else if (blockStatus > 1 && this.ScheduleModel.id !== "mySchedule")
    {
        meta.tdCls += cl('blockOccupied');
        meta.tdCls += cl('conMenu');
    }
    else
    {
        meta.tdCls += cl('blockFree');
        meta.tdCls += cl('conMenu');
    }

    if (Ext.isEmpty(data))
    {
        return '';
    }
    return data.join("\n");
};