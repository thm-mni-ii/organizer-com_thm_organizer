/**
 *  Method which handles the look of the calendar
 *
 */
Ext.override(
    Ext.picker.Date,
    {
        /**
         * The calendar is calculated and which dates must be shown.
         * TODO: I don't now what happen here in detail. There almost no comments. Very cruel code!!!
         *
         * @param {string} date A date
         * @param {string} active A date
         */
        fullUpdate: function (date, active)
        {
            var me = this,
                // cell of the calendar
                cells = me.cells.elements,
                // the span elements for every day
                textNodes = me.textNodes,
                // CSS class of disabled cells
                disabledCls = me.disabledCellCls,
                eDate = Ext.Date,
                i = 0,
                extraDays = 0,
                visible = me.isVisible(),
                sel = +eDate.clearTime(date, true),
                today = +eDate.clearTime(new Date()),
                min = me.minDate ? eDate.clearTime(me.minDate, true) : Number.NEGATIVE_INFINITY,
                max = me.maxDate ? eDate.clearTime(me.maxDate, true) : Number.POSITIVE_INFINITY,
                ddMatch = me.disabledDatesRE,
                // Text is shown on disabled dates
                ddText = me.disabledDatesText,
                ddays = me.disabledDays ? me.disabledDays.join('') : false,
                ddaysText = me.disabledDaysText,
                format = me.format,
                days = eDate.getDaysInMonth(date),
                firstOfMonth = eDate.getFirstDateOfMonth(date),
                startingPos = firstOfMonth.getDay() - me.startDay,
                previousMonth = eDate.add(date, eDate.MONTH, - 1),
                longDayFormat = me.longDayFormat,
                prevStart, current, disableToday, tempDate, setCellClass, html, cls, formatValue, value;

            if (startingPos < 0)
            {
                startingPos += 7;
            }

            days += startingPos;
            prevStart = eDate.getDaysInMonth(previousMonth) - startingPos;
            current = new Date(previousMonth.getFullYear(), previousMonth.getMonth(), prevStart, me.initHour);

            if (me.showToday)
            {
                tempDate = eDate.clearTime(new Date());
                disableToday = (tempDate < min || tempDate > max || (ddMatch && format && ddMatch.test(eDate.dateFormat(tempDate, format))) || (ddays && ddays.indexOf(tempDate.getDay()) !== -1));

                if (!me.disabled)
                {
                    me.todayBtn.setDisabled(disableToday);
                    me.todayKeyListener.setDisabled(disableToday);
                }
            }

            /**
             * TODO: So all of sudden we define a function with more than 100 lines of code within a function... Why not...
             * The cell (day in the calendar) is been prepared. TODO: 404 "No comments have been found"!!
             *
             * @method setCellClass
             * @param {object} cell A td element as DOM object
             */
            setCellClass = function (cell)
            {
                value = +eDate.clearTime(current, true);
                // cell.title = eDate.format(current,
                // longDayFormat);
                cell.title = '';
                // store dateValue number as an expando
                cell.firstChild.dateValue = value;
                if (value === today)
                {
                    cell.className += ' ' + me.todayCls;
                    cell.title = me.todayText;
                }
                if (value === sel)
                {
                    cell.className += ' ' + me.selectedCls;
                    me.el.dom.setAttribute('aria-activedescendant',
                    cell.id);
                    if (visible && me.floating)
                    {
                        Ext.fly(cell.firstChild).focus(50);
                    }
                }
                // disabling
                if (value < min)
                {
                    cell.className = disabledCls;
                    cell.title = me.minText;
                    return;
                }
                if (value > max)
                {
                    cell.className = disabledCls;
                    cell.title = me.maxText;
                    return;
                }
                if (ddays)
                {
                    if (ddays.indexOf(current.getDay()) !== -1)
                    {
                        cell.title = ddaysText;
                        cell.className = disabledCls;
                    }
                }
                if (ddMatch && format)
                {
                    formatValue = eDate.dateFormat(current, format);
                    if (ddMatch.test(formatValue))
                    {
                        cell.title = ddText.replace('%0',
                        formatValue);
                        cell.className = disabledCls;
                    }
                }

                var begin = MySched.session.begin.split("-");
                begin = new Date(begin[0], begin[1] - 1, begin[2]);
                var end = MySched.session.end.split("-");
                end = new Date(end[0], end[1] - 1, end[2]);

                cell.children[0].events = [];

                current.clearTime();

                var len;
                if (current >= begin && current <= end)
                {
                    len = cell.children[0].events.length;
                    if (current.compare(begin) === 0)
                    {
                        cell.children[0].events[len] = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER_BEGIN;
                    }
                    else if (current.compare(end) === 0)
                    {
                        cell.children[0].events[len] = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER_END;
                    }
                    else
                    {
                        cell.children[0].events[len] = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER;
                    }

                    cell.className += " MySched_Semester";
                    if (!cell.children[0].className.contains(" calendar_tooltip"))
                    {
                        cell.children[0].className += " calendar_tooltip";
                    }
                }

                var EL = MySched.eventlist.data;

                for (var ELindex = 0; ELindex < EL.length; ELindex++)
                {

                    var startdate = EL.items[ELindex].data.startdate.split(".");
                    startdate = new Date(startdate[2], startdate[1] - 1, startdate[0]);
                    var enddate = EL.items[ELindex].data.enddate.split(".");
                    enddate = new Date(enddate[2], enddate[1] - 1, enddate[0]);

                    current.clearTime();

                    if (startdate <= current && enddate >= current)
                    {
                        cell.className += " MySched_CalendarEvent";
                        len = cell.children[0].events.length;
                        cell.children[0].events[len] = EL.items[ELindex];
                        if (!cell.children[0].className.contains(" calendar_tooltip"))
                        {
                            cell.children[0].className += " calendar_tooltip";
                        }
                    }
                }
            };

            for (; i < me.numDays; ++i)
            {
                if (i < startingPos)
                {
                    html = (++prevStart);
                    cls = me.prevCls;
                }
                else if (i >= days)
                {
                    html = (++extraDays);
                    cls = me.nextCls;
                }
                else
                {
                    html = i - startingPos + 1;
                    cls = me.activeCls;
                }
                textNodes[i].innerHTML = html;
                cells[i].className = cls;
                current.setDate(current.getDate() + 1);
                setCellClass(cells[i]);
            }

            var calendarTooltip = Ext.select('.calendar_tooltip', false, document);
            calendarTooltip.removeAllListeners();
            calendarTooltip.on({
                'mouseover' : function(e) {
                    e.stopEvent();
                    calendar_tooltip(e);
                },
                'mouseout' : function(e) {
                    e.stopEvent();
                },
                scope : this
            });
            me.monthBtn.setText(me.monthNames[date.getMonth()] + ' ' + date.getFullYear());
        }
    }
);

/**
 * Shows the calendar tooltip
 *
 * @method calendar_tooltip
 * @param {object} e The mouse event with its information
 */
function calendar_tooltip (e)
{
    var el = e.getTarget('.calendar_tooltip', 5, true);
    var calendarTip = Ext.getCmp('mySched_calendar-tip');
    if (calendarTip)
    {
        calendarTip.destroy();
    }
    var xy = el.getXY();
    xy[0] = xy[0] + el.getWidth();

    var events = el.dom.events;
    var htmltext = "";
    for (var i = 0; i < events.length; i++)
    {
        if (Ext.isObject(events[i]))
        {

            htmltext += events[i].data.title;
            var name = "";

            var eventObjects = events[i].data.objects;

            for (var eventIndex = 0; eventIndex < eventObjects.length; eventIndex++)
            {
                var o = eventObjects[eventIndex];
                if (name !== "")
                {
                    name += ", ";
                }

                if (o.type === "teacher")
                {
                    var teacherName = MySched.Mapping.getTeacherKeyByID(o.id);

                    if(teacherName === o.id && Ext.isDefined(o.surname) && !Ext.isEmpty(o.surname))
                    {
                        teacherName = o.surname;
                    }

                    name += "<small class='dozname'>" + getTeacherSurnameWithCutFirstName(teacherName) + "</small>";
                }
                else if (o.type === "room")
                {
                    var roomName = MySched.Mapping.getRoomKeyByID(o.id);

                    if(roomName === o.id && Ext.isDefined(o.longname) && !Ext.isEmpty(o.longname))
                    {
                        roomName = o.longname;
                    }
                    name += "<small class='roomshortname'>" + MySched.Mapping.getRoomName(roomName) + "</small>";
                }
            }

            if (name !== "")
            {
                htmltext += " (" + name + ")<br/>";
            }
            else
            {
                htmltext += "<br/>";
            }
        }
        else
        {
            htmltext += events[i] + "<br/>";
        }
    }
    if (events.length > 0)
    {
        var parentID = el.dom.getParent()
            .id;
        var ttInfo = Ext.create('Ext.tip.ToolTip',
        {
            title: '<div class="mySched_tooltip_calendar_title">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EVENTS + '</div>',
            id: 'mySched_calendar-tip',
            target: parentID,
            autoHide: false,
            html: htmltext,
            cls: "mySched_tooltip_calendar"
        });

        ttInfo.on('afterrender', function ()
        {
            Ext.select('.dozname', false, this.el.dom)
                .on(
            {
                'click': function (e)
                {
                    if (e.button === 0)
                    {
                        MySched.SelectionManager.showSchedule(e, 'doz');
                    }
                },
                scope: this
            });

            Ext.select('.roomshortname', false, this.el.dom).on(
            {
                'click': function (e)
                {
                    if (e.button === 0)
                    {
                        MySched.SelectionManager.showSchedule(e, 'room');
                    }
                },
                scope: this
            });
        });

        ttInfo.on('beforedestroy', function ()
        {
            Ext.select('.dozname', false, this.el.dom).removeAllListeners();
            Ext.select('.roomshortname', false, this.el.dom).removeAllListeners();
        });

        ttInfo.showAt(xy);
    }
}