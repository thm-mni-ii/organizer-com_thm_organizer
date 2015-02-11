/**
 * basic model
 *
 * @class MySched.Model
 */
Ext.define('MySched.Model',
    {
        extend: 'Ext.util.Observable',

        /**
         * Setting class variables.
         *
         * @param {Object} id ID of the model
         * @param {Object} d Data object of the model
         */
        constructor: function (id, d)
        {
            this.id = id;
            this.data = {};
            this.eventList = new MySched.Collection();
            this.responsible = null;

            // IMPORTANT!! Generate deep copy, otherwise just the references will be copied
            if (Ext.type(d) === 'object' || Ext.type(d) === 'array')
            {
                Ext.apply(this.data, d);
            }
            else
            {
                this.data = d;
            }
        },
        /**
         * Returns the ID
         *
         * @method getId
         * @return {String} id Id
         */
        getId: function ()
        {
            return this.id;
        },
        /**
         * Returns an object with all information to an requested resource
         *
         * @method getData
         * @param {object} addData Data object according to the resource
         * @return {object} * All information to the requested resource
         */
        getData: function (addData)
        {
            if (Ext.type(addData) !== 'object')
            {
                return this.data;
            }
            return Ext.applyIf(addData, this.data);
        },
        /**
         * TODO: maybe obsolete, seems to be never called
         *
         * @param p
         */
        setParent: function (p)
        {
            console.log("model.setParent: maybe never used?");
            this.parent = p;
        },
        /**
         * TODO: maybe obsolete, seems to be never called
         *
         * @return {*}
         */
        getParent: function ()
        {
            console.log("model.getParent: maybe never used?");
            return this.parent;
        },
        /**
         * TODO: maybe obsolete, seems to be never called
         *
         * @method asArray
         * @return {*}
         */
        asArray: function ()
        {
            console.log("model.asArray: maybe never used?");
            var ret = [];
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
                if (e.asArray)
                {
                    e = e.asArray();
                }
                this[this.length] = e;
            }, ret);
            console.log(ret);
            if (ret.length === 1)
            {
                return ret[0];
            }
            return ret;
        },
        /**
         * Creates an array with the data for pdf creation
         *
         * @param {string} type Format of data
         * @param pers TODO I don't know
         * @return {array} * Data of the schedule
         */
        exportData: function (type, pers)
        {
            var exportData = {};
            if (pers === "personal")
            {
                exportData = this.asPersArray();
            }
            else
            {
                if (type === "jsonpdf")
                {
                    exportData.grid = MySched.gridData[MySched.selectedSchedule.scheduleGrid];
                    exportData.data = this.asArrayForPDF();
                    exportData.daysPerWeek = MySched.displayDaysInWeek;
                }
                else
                {
                    exportData = this.asArray();
                }
            }

            switch (type)
            {
                case 'arr':
                case 'array':
                    return exportData;
                case 'xml':
                    alert(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_XML_NOT_IMPLEMENTED);
                    exit();
                    break;
                default:
                case 'json':
                    var returnValue = Ext.encode(exportData);
                    return returnValue;
            }
        },
        /**
         * Returns the data of the schedule in a string
         *
         * @return {string} * All data above the schedule in an array as string
         */
        exportAllData: function ()
        {
            var d = [];
            d[0] = {};
            d[0].htmlView = this.htmlView;
            d[0].lessons = this.asArray();
            d[0].visibleLessons = this.visibleLessons;
            d[0].events = this.visibleEvents;
            d[0].session = {};
            d[0].session.sdate = MySched.session.begin;
            d[0].session.edate = MySched.session.end;
            d[0].session.semesterID = MySched.class_semester_id;
            return Ext.encode(d);
        }
    }
);
