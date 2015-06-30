/**
 * model of event list
 *
 * @class EventListModel
 * @constructor
 */
Ext.define('EventListModel',
    {
        extend: 'MySched.Model',
        /**
         * Creating a collection
         */
        constructor: function ()
        {
            var data;
            this.data = new MySched.Collection();
        },
        /**
         * Sets start and end time if not set to an event and adds to collection
         *
         * @method addEvent
         * @param {object} e Event
         */
        addEvent: function (e)
        {
            // Adds an event
            if (e.data.starttime === "00:00")
            {
                e.data.starttime = "08:00";
            }
            if (e.data.endtime === "00:00")
            {
                e.data.endtime = "19:00";
            }
            this.data.add(e.data.id, e);
        },
        /**
         *
         * @method getEvent
         * @param id
         * @return {*}
         */
        getEvent: function (id)
        {
            //TODO: never called
            var idsplit = id.split("_");
            var datas = this.data.filterBy(function (o, k)
            {
                if (k === idsplit[1])
                {
                    return true;
                }
                return false;
            }, this);

            return datas.items[0];
        },
        /**
         * Returns an object with all events of the delivered type and value
         *
         * @method getEvents
         * @param {String} type Type of the resource (e.g. teacher)
         * @param {String} value Value of the type
         * @return {Object} data.map List of events with the delivered type and value
         */
        getEvents: function (type, value)
        {
            if (Ext.isEmpty(type) && Ext.isEmpty(value))
            {
                return [];
            }

            var dbID;
            if (type === "teacher")
            {
                dbID = MySched.Mapping.getTeacherDbID(value);
            }
            else if (type === "room")
            {
                dbID = MySched.Mapping.getRoomDbID(value);
            }
            else
            {
                return [];
            }

            var data = this.data.filterBy(function (o, k)
            {
                var eventObjects = o.data.objects;

                // Events with 0 objects could not assigned to a plan and will not considered for now
                if (Ext.isArray(eventObjects) && eventObjects.length > 0)
                {
                    for (var eventIndex = 0; eventIndex < eventObjects.length; eventIndex++)
                    {
                        if (Ext.isObject(eventObjects[eventIndex]) && eventObjects[eventIndex].id === dbID && eventObjects[eventIndex].type === type)
                        {
                            return true;
                        }
                    }
                }
                return false;
            }, this);

            return data.map;
        },
        /**
         * TODO In my test it returns nothing. But maybe it should return a list of events which  belong to the given
         * lecture
         *
         * @method getEventsForLecture
         * @param {object} lecture Information above a lecture
         * @param {number} block Block number in schedule
         * @param {string} day Weekday as String
         * @return {string} TODO Id don't know what it is exactly
         */
        getEventsForLecture: function(lecture, block, day)
        {
            var ret = "";

            var data = this.data.filterBy(function (o, k)
            {
                var eventData = o.data;
                var eventStartDate = eventData.startdate;
                var eventEndDate = eventData.enddate;
                var eventStartTime = eventData.starttime;
                var eventEndTime = eventData.endtime;
                var currMOFR = getCurrentMoFrDate();

                eventStartDate = convertGermanDateStringToDateObject(eventStartDate);
                eventEndDate = convertGermanDateStringToDateObject(eventEndDate);

                var lectureData = lecture.data;
                var lectureCalendar = lectureData.calendar;

                for(var lectureCalendarIndex in lectureCalendar)
                {
                    if (Ext.isObject(lectureCalendar[lectureCalendarIndex]))
                    {
                        var lectureDate = convertEnglishDateStringToDateObject(lectureCalendarIndex);
                        if (eventStartDate <= lectureDate && eventEndDate >= lectureDate && lectureDate >= currMOFR.monday && lectureDate <= currMOFR.friday && Ext.Date.format(lectureDate, "l").toLowerCase() === day)
                        {
                            var eventBlocks = getBlocksBetweenTimes(eventStartTime, eventEndTime, eventStartDate, eventEndDate);
                            for(var eventBlocksIndex = 0; eventBlocksIndex < eventBlocks.length; eventBlocksIndex++)
                            {
                                var eventBlock = eventBlocks[eventBlocksIndex];
                                if (eventBlock === block)
                                {
                                    var eventObjects = eventData.objects;
                                    for(var eventObjectsIndex = 0; eventObjectsIndex < eventObjects.length; eventObjectsIndex++)
                                    {
                                        var eventObject = eventObjects[eventObjectsIndex];
                                        if (eventObject.type === "teacher")
                                        {
                                            var teacherName = MySched.Mapping.getTeacherKeyByID(eventObject.id);
                                            if (Ext.isString(lectureData.teachers.map[teacherName]) && lectureData.teachers.map[teacherName] !== "removed")
                                            {
                                                return true;
                                            }
                                        }
                                        else if (eventObject.type === "room")
                                        {
                                            var roomData = lectureCalendar[lectureCalendarIndex][block+1].lessonData;
                                            var roomName = MySched.Mapping.getRoomKeyByID(eventObject.id);
                                            for(var roomDataIndex in roomData)
                                            {
                                                if (roomData.hasOwnProperty(roomDataIndex) && Ext.isString(roomData[roomDataIndex]) && roomData[roomDataIndex] !== "removed" && roomName === roomDataIndex)
                                                {
                                                    return true;
                                                }
                                            }
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
                return false;
            });

            for(var dataIndex = 0; dataIndex < data.items.length; dataIndex++)
            {
                ret += data.items[dataIndex].getEventView();
            }

            return ret;
        }
    }
);
