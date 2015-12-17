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

/**
 * TODO
 *
 * @class SchedGrid
 */
Ext.define('SchedGrid',
{
    extend: 'Ext.grid.Panel',

    /**
     * Gets the data with lessons and blocks for every day
     *
     * @method loadData
     * @param {object} data List of object with time for every block of the week and the lessons for every day
     * @return {*} TODO Don't now what it is
     */
    loadData: function (data)
    {
        var scheduleGrid = MySched.gridData[this.ScheduleModel.scheduleGrid],
            scheduleGridLength = Object.keys(scheduleGrid).length;

        for (var i = 1; i <= scheduleGridLength; i++)
        {
            var index = i - 1;
            data[index].time = addColonToTime(scheduleGrid[i].starttime) + '<br/>-<br/>' + addColonToTime(scheduleGrid[i].endtime);
        }

        // If the grid is also shown, show also the sporadic events
        if (MySched.selectedSchedule.grid === this)
        {
            MySched.layout.viewport.doLayout();
        }

        // TODO Seems to be always empty. Is it really useful
        return this.store.loadData(data);

    },
    /**
     * Cleans up the sporadic events and sets up the new ones
     * TODO Maybe obsolete. It seems to be not in use anymore
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

/**
 *
 * @return {SchedGrid} grid
 */
function getSchedGrid()
{
    var fields, columns, rowBodyFeature;
    // Default days in a week from mo till sa
    fields = ['time', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    columns = [
        {
            header: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TIME,
            menuDisabled: true,
            sortable: false,
            dataIndex: 'time',
            renderer: MySched.lectureCellRenderer,
            width: 50
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
        }];

    // No Saturdays
    if(MySched.displayDaysInWeek === "1")
    {
        fields.pop();
        columns.pop();
    }

    Ext.create('Ext.data.Store',
    {
        storeId: 'gridStore',
        fields: fields,
        data: {
            'items': []
        },
        proxy: {
            type: 'memory',
            reader: {
                type: 'json',
                rootProperty: 'items'
            }
        }
    });

    /**
     * TODO Bad style to create a function this way
     * Returns an object with data for the rows
     *
     */
    rowBodyFeature = Ext.create('Ext.grid.feature.RowBody',
    {
        /**
         * This method returns an object with attributes for the rows
         *
         * @param {object} data Object with information for every block of the week
         * @param {number} rowIndex Row CIndex of the grid
         * @param {object} record TODO Don't know what it is
         * @param {object} orig Object with attributes for the columns of the grid
         * @return {object} * Attributes for the rows
         */
        getAdditionalData: function (data, rowIndex, record, orig)
        {
            var headerCt = this.view.headerCt,
                colspan = headerCt.getColumnCount(),
                lunchTime = {rowBody: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LUNCHTIME, rowBodyCls: 'MySched_pause', rowBodyColspan: colspan},
                normalBreak = {rowBody: '', rowBodyCls: '', rowBodyColspan: colspan};

            if (rowIndex === 2)
            {
                return lunchTime;
            } else {
                return normalBreak;
            }
        }
    });

    /**
     * Object with attributes that creates the headers of the schedule
     *
     */
    var grid = Ext.create('SchedGrid',
    {
        title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TITLE_UNKNOWN,
        store: Ext.data.StoreManager.lookup('gridStore'),
        columns: columns,
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

/**
 * TODO: Maybe obsolete, it seems to be never used
 *
 */
Ext.apply(Ext.form.VTypes,
{
    /**
     *
     * @param val
     * @param field
     * @return {boolean}
     */
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

/**
 * Special renderer for events
 *
 * @param {string} data Start and end date of a block as string
 * @param {Object} meta Object with class and style attributes
 * @param {Object} record TODO Don't know what it is
 * @param {number} rowIndex Index of the row
 * @param {number} colIndex Index of the column
 * @param {Object} store TODO Don't know
 */
MySched.lectureCellRenderer = function (data, meta, record, rowIndex, colIndex, store)
{
    /**
     * This method appends a string to a given class name and returns it
     *
     * @method cl
     * @param {string} css A css class name
     * @return {string} * A css class name
     */
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

        var times = blocktotime(rowIndex + 1, this.ScheduleModel.scheduleGrid);
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