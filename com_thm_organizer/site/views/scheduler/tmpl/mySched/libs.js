Ext.MessageBox.buttonText.yes = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_YES;
Ext.MessageBox.buttonText.no = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NO;
Ext.ns('MySched');

// specialization of collection
MySched.Collection = function ()
{
    MySched.Collection.superclass.constructor.call(this);
};

/**
 *
 * @class MySched.Collection
 */
Ext.extend(
    MySched.Collection, Ext.util.MixedCollection,
    {
        /**
         *
         * @param el
         * @return {*}
         */
        getKey: function (el)
        {
            console.log(el);
            if (typeof el === 'object' && typeof el.getId === 'function')
            {
                return el.getId();
            }
            return el.id;
        },
        /**
         * Checks if it is empty
         *
         * @return {boolean} * If getCount returns 0 elements it returns true
         */
        isEmpty: function ()
        {
            return this.getCount() === 0;
        },
        /**
         * TODO Not sure what it is good for
         *
         * @method get
         * @param {string} key TODO Don't know what it means
         * @param {} def TODO Was alway undefined
         * @return {*}
         */
        get: function (key, def)
        {
            var ret = MySched.Collection.superclass.get.call(this, key);
            if (Ext.isEmpty(ret))
            {
                return def;
            }
            return ret;
        },
        /**
         * TODO: Maybe obsolete, it seems to be never used
         *
         * @param field
         * @return {Array}
         */
        getField: function (field)
        {
            console.log("lib.js getField: maybe never used?");
            var ret = [];
            this.each(function (e) { this.push(e[field]); }, ret);
            return ret;
        },
        /**
         * Returns the items of the schedule
         *
         * @return {object} * Object with data for every block of the schedule
         */
        asArray: function ()
        {
            return this.items;
        },
        /**
         * identical to superclass. Just MySched.Collection will be returned
         *
         * @param {Function} fn The function to be called, it will receive the args o (the object), k (the key)
         * @param {Object} scope (optional) The scope of the function (defaults to this)
         * @return {MySched.Collection} * The new filtered collection
         */
        filterBy: function (fn, scope)
        {
            var r = new MySched.Collection();
            r.getKey = this.getKey;
            var k = this.keys,
            it = this.items;
            for (var i = 0, len = it.length; i < len; i++)
            {
                if (fn.call(scope || this, it[i], k[i]))
                {
                    r.add(k[i], it[i]);
                }
            }
            return r;
        }
    }
);

// configuration object
MySched.Config = new MySched.Collection();
MySched.Calendar = new MySched.Collection();

/**
 * Fast access to the configuration object
 *
 * @method _C
 * @param {string} a The attribute
 * @return {String} * The value of the requested attribute
 */
function _C (a)
{
    return MySched.Config.get(a);
}

/**
 * Expanded the String object with the equeal method
 * TODO: Maybe obsolete, it seems to be never used
 *
 * @param {string} str String that will be compered to basis string
 * @return {bollean} * True if both string a equal otherwise false
 */
String.prototype.equal = function (str)
{
    console.log("lib.js String.prototype.equal: maybe never used?");
    return this.toLowerCase() === str.toLowerCase();
};

/**
 * Expand the array object with the addto method
 * TODO This method does the same as splice() so there is no need for it....
 *
 * @param {Integer} index Index on that the value should be added
 * @param {Object} value The object where you want to add the value
 * @return {array} newArray Returns the new array
 */
Array.prototype.AddTo = function (index, value)
{
    var newArray = [], i;
    for (i = 0; i < index; i++)
    {
        newArray[i] = this[i];
    }
    newArray[index] = value;
    for (i = index; i < this.length; i++)
    {
        newArray[i + 1] = this[i];
    }
    return newArray;
};

/**
 * Expand the array object for the contains method
 *
 * @param {Object} obj The object that should be checked
 * @return {boolean} * True if the array contains the object otherwise false
 */
Array.prototype.contains = function (obj)
{
    var i = this.length;
    while (i--)
    {
        if (this[i] === obj)
        {
            return true;
        }
    }
    return false;
};

/**
 * Expand the dragzone with the lecture object
 *
 * @param {Object} e The mouse event
 * @return {object} target The lecture which should be draged
 */
Ext.override(Ext.dd.DragZone,
{
    getDragData: function (e)
    {
        // TreeNode
        if (Ext.dd.Registry.getHandleFromEvent(e))
        {
            return Ext.dd.Registry.getHandleFromEvent(e);
        }

        // Lecture
        var target = Ext.get(e.getTarget()).findParent('.lectureBox', 3, true);
        if (target === null)
        {
            return null;
        }
        target.ddel = target.dom;
        return target;
    }

});

/**
 * Erweiterung des GridViews
 * TODO: Maybe obsolete, it seems to be never used
 */
Ext.override(
    Ext.grid.View,
    {
        // private
        // Nur grid als Uebergabeparameter fuer den renderer
        // hinzugefuegt
        /**
         *
         * @param cs
         * @param rs
         * @param ds
         * @param startRow
         * @param colCount
         * @param stripe
         * @return {string}
         */
        doAutoRender: function (cs, rs, ds, startRow, colCount, stripe)
        {
            console.log("lib.js doAutoRender: maybe never used?");
            var ts = this.templates,
                ct = ts.cell,
                rt = ts.row,
                last = colCount - 1,
                tstyle = 'width:' + this.getTotalWidth() + ';';

            // buffers
            var buf = [], cb, c, p = {}, rp = { tstyle: tstyle }, r;
            for (var j = 0, len = rs.length; j < len; j++)
            {
                r = rs[j];
                cb = [];
                var rowIndex = (j + startRow);

                for (var i = 0; i < colCount; i++)
                {
                    c = cs[i];
                    p.id = c.id;
                    p.css = i === 0 ? 'x-grid3-cell-first ' : (i === last ? 'x-grid3-cell-last ' : '');
                    var block;
                    if (j < 3)
                    {
                        block = j + 1;
                    }
                    else
                    {
                        block = j;
                    }
                    var blotimes;
                    if (block < rs.length)
                    {
                        blotimes = blocktotime(block);
                        p.attr = p.cellAttr = "stime=" + blotimes[0] + " etime=" + blotimes[1] + " dow=" + i;
                    }
                    // ****** Aenderung start - this.grid
                    // hinzugefuegt
                    p.value = c.renderer(r.data[c.name], p, r, rowIndex, i, ds, this.grid);
                    // ****** Aenderung stop
                    var pos = p.value.toString().indexOf('class=MySched_event');
                    if (pos !== -1)
                    {
                        p.css = p.css + "MySched_event_block ";
                    }
                    p.style = c.style;
                    if (typeof p.value === 'undefined' || p.value === "")
                    {
                        p.value = "&#160;";
                    }
                    if (r.dirty && typeof r.modified[c.name] !== 'undefined')
                    {
                        p.css += ' x-grid3-dirty-cell';
                    }
                    cb[cb.length] = ct.apply(p);

                    if (j === 3 && i === 0)
                    {
                        cb[cb.length - 1] = cb[cb.length - 1].replace("<td class=", "<td colspan=\"7\" class=");
                        break;
                    }
                }
                var alt = [];
                if (stripe && ((rowIndex + 1) % 2 === 0))
                {
                    alt[0] = "x-grid3-row-alt";
                }
                if (r.dirty)
                {
                    alt[1] = " x-grid3-dirty-row";
                }
                rp.cols = colCount;
                if (this.getRowClass)
                {
                    alt[2] = this.getRowClass(r, rowIndex, rp, ds);
                }
                rp.alt = alt.join(" ");
                rp.cells = cb.join("");
                buf[buf.length] = rt.apply(rp);
            }
            return buf.join("");
        }
    }
);

/**
 * Check wheter the event objects are corresponding with the lesson objects
 * (doz, room, clas)
 * TODO: Maybe obsolete, it seems to be never used
 *
 * @author Wolf
 * @param {Array}
 *            event An Array which represent an event
 * @param {Array}
 *            arr An Array which contains all lesson on one day
 * @param {String}
 *            selectedScheduleid The id of the current selected schedule
 * @return (Integer) Returns one of the following numbers 1: selectedScheduleid
 *         is the same as one of the event objects 2: A class of an element in
 *         arr is the same as one of the event objects 3: A teacher of an
 *         element in arr is the same as one of the event objects 4: A room of
 *         an element in arr is the same as one of the event objects 0: No
 *         condition above is true
 */
function showevent(event, arr, selectedScheduleid)
{
    console.log("grid.js showevent: maybe never used?");
    if (event.source === "estudy")
    {
        return 5;
    }
    var lessons = MySched.Base.schedule;

    var obj;
    for (obj in event.objects)
    {
        if (event.objects[obj] === selectedScheduleid)
        {
            return 1;
        }
    }
    for (var i = 0; i < arr.length; i++)
    {
        var container = document.createElement("div");
        container.innerHTML = arr[i];
        var divcollection = container.getElementsByTagName("div");
        for (var index = 0; index < divcollection.length; index++)
        {
            var id = divcollection[index].getAttribute("id");
            if (id !== null)
            {
                id = id.split("##");
                var lesson = lessons.data.map[id[1]];
                if (!Ext.isObject(lesson))
                {
                    lesson = MySched.Schedule.data.map[id[1]];
                }
                if (Ext.isObject(lesson))
                {
                    for (obj in event.objects)
                    {
                        if (event.hasOwnProperty(obj))
                        {
                            if (lessoncontains(event.objects[obj], lesson.clas.map))
                            {
                                return 2;
                            }
                            if (lessoncontains(event.objects[obj], lesson.doz.map))
                            {
                                return 3;
                            }
                            if (lessoncontains(event.objects[obj], lesson.room.map))
                            {
                                return 4;
                            }
                        }
                    }
                }
            }
        }
    }
    return 0;
}

/**
 * Function to test whether an object is the same as an element in an array
 * TODO: Maybe obsolete, it seems to be never used
 *
 * @method lessoncontains
 * @param {String} obj String representation of an event object
 * @param {Array} arr Array of teachers, rooms or classes of a lesson
 * @return {Boolean} * true if an element in arr matches to obj false if no element in arr matches to obj
 */
function lessoncontains(obj, arr)
{
    console.log("grid.js lessoncontains: maybe never used?");
    if (obj !== null)
    {
        for (var lessonindex in arr)
        {
            if (arr.hasOwnProperty(lessonindex))
            {
                var arrsplit = arr[lessonindex].data.split("(");
                if (arrsplit[0] !== "")
                {
                    if (obj.search(new RegExp(arrsplit[0])) !== -1)
                    {
                        return true;
                    }
                }
            }
        }
    }
    return false;
}

/**
 * Function which transform a number to a weekday
 *
 * @method numbertoday
 * @param {number} number The number of the weekday
 * @return {String} english weekday between sunday (0) and saturday (6) (include monday and friday) {Boolean} false
 * if the number is not between 0-6(include 0 and 6)
 */
function numbertoday(number)
{
    if (number < 0 || number > 6)
    {
        return false;
    }
    var weekdays = {
        1: "monday",
        2: "tuesday",
        3: "wednesday",
        4: "thursday",
        5: "friday",
        6: "saturday",
        0: "sunday"
    };
    return weekdays[number];
}

/**
 * Function which return the monday date to a given date in a week.
 *
 * @method getMonday
 * @param {string} date A date
 * @return {string} weekpointer The date of the monday
 */
function getMonday(date)
{
    var weekpointer = null;
    if (Ext.isDate(date))
    {
        weekpointer = Ext.Date.clone(date);
        while (weekpointer.getDay() !== 1) // determine monday
        {
            weekpointer.setDate(weekpointer.getDate() - 1);
        }
    }
    Ext.Date.clearTime(weekpointer);
    return weekpointer;
}

/**
 * Function to get the monday and friday date of the current week
 *
 * @method getCurrentMoFrDate
 * @return {object} returnData The dates of monday and firday of the current weekpointer
 */
function getCurrentMoFrDate()
{
    var returnData = [];
    var weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker').value);
    var mondayWeekPointer = getMonday(weekpointer);
    var fridayWeekPointer = Ext.Date.clone(mondayWeekPointer);
    fridayWeekPointer.setDate(fridayWeekPointer.getDate() + 6);
    Ext.Date.clearTime(mondayWeekPointer);
    Ext.Date.clearTime(fridayWeekPointer);
    returnData = {
        "monday": Ext.Date.clone(mondayWeekPointer),
        "friday": Ext.Date.clone(fridayWeekPointer)
    };
    return returnData;
}

/**
 * Converts an english date string to an date object
 *
 * @method convertEnglishDateStringToDateObject
 * @param {string} dateString A date as string
 * @return {*} * If the date was in an correct format it returns an date object. Otherwise false.
 */
function convertEnglishDateStringToDateObject(dateString)
{
    var splittedDateIndex = dateString.split("-");
    if (splittedDateIndex.length === 3)
    {
        var dateObject = new Date(splittedDateIndex[0], splittedDateIndex[1] - 1, splittedDateIndex[2]);
        Ext.Date.clearTime(dateObject);
        return dateObject;
    }
    else
    {
        return false;
    }
}

/**
 * Converts an german date string to an date object
 *
 * @method convertGermanDateStringToDateObject
 * @param {string} dateString A date as string
 * @return {*} * If the date was in an correct format it returns an date object. Otherwise false.
 */
function convertGermanDateStringToDateObject(dateString)
{
    var splittedDateIndex = dateString.split(".");
    if (splittedDateIndex.length === 3)
    {
        var dateObject = new Date(splittedDateIndex[2], splittedDateIndex[1] - 1, splittedDateIndex[0]);
        Ext.Date.clearTime(dateObject);
        return dateObject;
    }
    else
    {
        return false;
    }
}

/**
 * TODO Where does the delta days come from? What is it for?
 *
 * @method displayDelta
 * @return {boolean} *
 */
function displayDelta()
{
    if(!Ext.isNumber(MySched.deltaDisplayDays))
    {
        return false;
    }

    var currentDate = new Date();
    Ext.Date.clearTime(currentDate);
    var creationDate = convertEnglishDateStringToDateObject(MySched.session.creationdate);

    creationDate.setDate(creationDate.getDate() + MySched.deltaDisplayDays);
    if(creationDate < currentDate)
    {
        return false;
    }
    return true;
}

/**
 * Gets the name of the teacher by the key
 *
 * @method getTeacherSurnameWithCutFirstName
 * @param {string} teacherKey The key of a teacher
 * @return {string} teacherName The name of the teacher
 */
function getTeacherSurnameWithCutFirstName(teacherKey)
{
    var teacherName = teacherKey;
    var teacherSurname = MySched.Mapping.getTeacherSurname(teacherKey);
    var teacherFirstname = MySched.Mapping.getTeacherFirstname(teacherKey);
 
    if(Ext.isString(teacherSurname) && teacherSurname !== teacherKey && teacherSurname.length > 0)
    {
        teacherName = teacherSurname;
    }
 
    if(Ext.isString(teacherFirstname) && teacherFirstname !== teacherKey && teacherFirstname.length > 0)
    {
        teacherName += ", " + teacherFirstname.charAt(0) + ".";
    }
    return teacherName;
}

/**
 * TODO Was not able to test it but it should be in use
 *
 * @method getBlocksBetweenTimes
 * @param startTime
 * @param endTime
 * @param eventStartDate
 * @param eventEndDate
 * @return {Array}
 */
function getBlocksBetweenTimes(startTime, endTime, eventStartDate, eventEndDate)
{
    console.log(startTime);
    console.log(endTime);
    console.log(eventStartDate);
    console.log(eventEndDate);
    if(eventStartDate < eventEndDate)
    {
        endTime = "19:00";
    }

    var blockTimes =  [{"start": "08:00", "end": "09:30"},
                       {"start": "09:50", "end": "11:20"},
                       {"start": "11:30", "end": "13:00"},
                       {"start": "14:00", "end": "15:30"},
                       {"start": "15:45", "end": "17:15"},
                       {"start": "17:30", "end": "19:00"}];

    var returnBlocks = [];

    for (var blockIndex = 0; blockIndex < blockTimes.length; blockIndex++)
    {
        var blockTime = blockTimes[blockIndex];

        // event starts before the block and lasts longer than the block
        if(startTime <= blockTime.start && endTime >= blockTime.end)
        {
            returnBlocks.push(blockIndex);
        } // Event is within the block
        else if(startTime >= blockTime.start && endTime <= blockTime.end)
        {
            returnBlocks.push(blockIndex);
        } // event begins before the block but ends in it
        else if(startTime <= blockTime.start && endTime <= blockTime.end && endTime >= blockTime.start)
        {
            returnBlocks.push(blockIndex);
        } // event starts within the block and lasts longer as this block
        else if(startTime >= blockTime.start && startTime <= blockTime.end &&  endTime >= blockTime.end)
        {
            returnBlocks.push(blockIndex);
        }
    }
    console.log(returnBlocks);
    return returnBlocks;
}

/**
 * This class handles the closing of an schedule tab with the middle mouse button (mouse wheel)
 *
 * @class TabCloseOnMiddleClick
 */
Ext.define(
    'Ext.ux.TabCloseOnMiddleClick',
    {
        alias: 'plugin.TabCloseOnMiddleClick',
        mixins: {
            observable: 'Ext.util.Observable'
        },
        /**
         * Initialize
         *
         * @method init
         * @param {object} tabpanel The tabpanel as DOM element
         */
        init : function(tabpanel)
        {
            this.tabPanel = tabpanel;
            this.tabBar = tabpanel.down("tabbar");

            this.mon(this.tabPanel, {
                scope: this,
                afterlayout: this.onAfterLayout,
                single: true
            });
        },
        /**
         * TODO: Don't know what it does
         *
         * @method onAfterLayout
         */
        onAfterLayout: function()
        {
            this.mon(this.tabBar.el, {
                scope: this,
                mousedown: this.onMouseDown,
                delegate: '.x-tab'
            });
            this.mon(this.tabBar.el, {
                scope: this,
                mouseup: this.onMouseUp,
                delegate: '.x-tab'
            });
        },
        /**
         * Is called when the mouse button ius down
         *
         * @method onMouseDown
         * @param {object} e The mouse event
         */
        onMouseDown: function(e)
        {
            e.preventDefault();
        },
        /**
         * Closes the tab on that is clicked
         *
         * @method onMouseUp
         * @param {object} e The mouse event
         * @param {object} target The clicked object as DOM element
         */
        onMouseUp: function(e, target)
        {
            e.preventDefault();

            if( target && e.button === 1  )
            {
                var item = this.tabBar.getComponent(target.id);
                item.onCloseClick();
            }
        }
    }
);