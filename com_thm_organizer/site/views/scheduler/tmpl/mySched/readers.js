/*global Ext: false, MySched: false, MySchedLanguage: false, LectureModel */
/*jshint strict: false */
/**
 * JsonReader for the loading and saving of schedules
 * 
 * @author Wolf Rost
 */
var SchedJsonReader = function ()
{
    "use strict";
    SchedJsonReader.superclass.constructor.call(this, this.config);
};
Ext.extend(SchedJsonReader, Ext.data.JsonReader,
{
    /**
     * Create a data block containing Ext.data.Records from an XML document.
     * 
     * @param {Object}
     *            doc A parsed XML document.
     * @return {Object} records A data block which is used by an
     *         {@link Ext.data.Store} as a cache of Ext.data.Records.
     */
    readRecords: function (o)
    {
        var records = [],
            success = true;
        if (o.success === false)
        {
            return {
                success: false,
                code: o.code,
                errors: o.errors
            };
        }

        Ext.Object.each(o, function (key, value, myself)
        {
            var lecture = MySched.Base.getLecture(key);
            if(Ext.isDefined(lecture))
            {
                records[records.length] = lecture;
            }
            else
            {
                for (var calendarIndex in value.calendar)
                {
                    if (value.calendar.hasOwnProperty(calendarIndex))
                    {
                        var block = value.calendar[calendarIndex];
                        for (var blockIndex in block)
                        {
                            if (block.hasOwnProperty(blockIndex))
                            {
                                var data = block[blockIndex];
                                data.lessonData.delta = "removed";
                            }
                        }
                    }
                }
                records[records.length] = new LectureModel(key, value, MySched.class_semester_id, "");
            }
        });

        if (typeof records.length === "undefined")
        {
            records.length = 0;
        }

        return {
            success: success,
            records: records,
            totalRecords: records.length
        };
    }
});