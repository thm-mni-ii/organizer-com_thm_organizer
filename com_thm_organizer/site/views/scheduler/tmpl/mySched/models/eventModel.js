/**
 * model of event
 *
 * @class EventModel
 * @constructor
 */
// TODO: I don't know if it used any more. It did not look like it is used anymore. So I stopped commenting.
Ext.define('EventModel',
    {
        extend: 'MySched.Model',

        /**
         * TODO
         *
         * @param id
         * @param data
         */
        constructor: function (id, data)
        {
            this.id = id;
            this.data = data;

            if (this.data.endDate === "00.00.0000")
            {
                this.data.endDate = this.data.startDate;
            }

            this.data.starttime = this.data.starttime.substring(0, 5);
            this.data.endtime = this.data.endtime.substring(0, 5);

            var MySchedEventClass = 'MySchedEvent_' + this.data.source;
            if (this.data.reserve === true)
            {
                MySchedEventClass += " MySchedEvent_reserve";
            }
            this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + '{top_icon}<b class="MySchedEvent_name">{event_name}</b><br/>{teacher} / {room}</div>');
        },
        /**
         * TODO
         *
         * @method getEventDetailData
         * @return {*}
         */
        getEventDetailData: function ()
        {
            return Ext.apply(this.getData(this),
                {
                    'event_name': this.getName(),
                    'event_info': this.getEventInfoView(),
                    'teacher': this.getTeacherName(),
                    'room': this.getRoomName()
                });
        },
        /**
         * TODO
         *
         * @method getName
         * @return {*}
         */
        getName: function ()
        {
            return this.data.title;
        },
        /**
         * TODO
         *
         * @method getTeacherName
         * @return {string}
         */
        getTeacherName: function ()
        {
            var teacherNames = "";

            this.data.objects.each(function (o, k)
            {
                if (o.type === "teacher")
                {
                    var teacherName = getTeacherSurnameWithCutFirstName(MySched.Mapping.getTeacherKeyByID(o.id));
                    if (teacherName === o.id && Ext.isDefined(o.surname) && !Ext.isEmpty(o.surname))
                    {
                        teacherName = o.surname;
                    }
                    if (teacherNames !== "")
                    {
                        teacherNames += ", ";
                    }
                    teacherNames += teacherName;
                }
            });

            return teacherNames;
        },
        /**
         * TODO
         *
         * @method getRoomName
         * @return {string}
         */
        getRoomName: function ()
        {
            var roomNames = "";

            this.data.objects.each(function (o, k)
            {
                if (o.type === "room")
                {
                    var roomName = MySched.Mapping.getRoomKeyByID(o.id);
                    if (roomName === o.id && Ext.isDefined(o.longname) && !Ext.isEmpty(o.longname))
                    {
                        roomName = o.longname;
                    }
                    if (roomNames !== "")
                    {
                        roomNames += ", ";
                    }
                    roomNames += roomName;
                }
            });

            return roomNames;
        },
        /**
         * TODO
         *
         * @method getData
         * @param addData
         * @return {*|*|string|string}
         */
        getData: function (addData)
        {
            return this.superclass.getData.call(this, addData);
        },
        /**
         * TODO
         *
         * @method getEventView
         * @param type
         * @param bl
         * @param collision
         * @return {*}
         */
        getEventView: function (type, bl, collision)
        {
            var d = this.getEventDetailData();
            if (MySched.Authorize.user !== null && MySched.Authorize.role !== 'user' && MySched.Authorize.role !== 'registered' && !this.eventTemplate.html.contains("MySchedEvent_joomla access"))
            {
                this.eventTemplate.html = this.eventTemplate.html.replace("MySchedEvent_joomla", 'MySchedEvent_joomla access');
            }

            var MySchedEventClass = 'MySchedEvent_' + this.data.source;
            if (this.data.reserve === true)
            {
                MySchedEventClass += " MySchedEvent_reserve";
            }
            var collisionIcon = "";

            if (d.reserve === true && collision === true)
            {
                if (bl < 4)
                {
                    bl++;
                }
                var blocktimes = blocktotime(bl);
                if (blocktimes[0] < d.starttime && blocktimes[1] > d.starttime)
                {
                    collisionIcon = "<img class='MySched_EventCollision' width='24px' height='16px' data-qtip='" + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_COLLISION + "' src='" + MySched.mainPath + "images/warning.png'><br/>";
                }
                if (blocktimes[0] < d.endtime && blocktimes[1] > d.endtime)
                {
                    collisionIcon = "<img class='MySched_EventCollision' width='24px' height='16px' data-qtip='" + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_COLLISION + "' src='" + MySched.mainPath + "images/warning.png'><br/>";
                }
            }

            if (type === "teacher")
            {
                this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + collisionIcon + '<b class="MySchedEvent_name">{event_name}</b><br/><small class="event_resource">{room}</small></div>');
            }
            else if (type === "room")
            {
                this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + collisionIcon + '<b class="MySchedEvent_name">{event_name}</b><br/><small class="event_resource">{teacher}</small></div>');
            }
            else
            {
                this.eventTemplate = new Ext.Template('<div id="MySchedEvent_{id}" class="' + MySchedEventClass + '">' + collisionIcon + '<b class="MySchedEvent_name">{event_name}</b><br/><small class="event_resource">{teacher} / {room}</small></div>');
            }

            return this.eventTemplate.apply(d);
        },
        /**
         * TODO
         *
         * @method getEventInfoView
         * @return {string}
         */
        getEventInfoView: function ()
        {
            return "<div id='MySchedEventInfo_" + this.id + "' class='MySchedEventInfo'>" + "<span class='MySchedEvent_desc'>" + this.data.description + "</span><br/>";
        }
    }
);
