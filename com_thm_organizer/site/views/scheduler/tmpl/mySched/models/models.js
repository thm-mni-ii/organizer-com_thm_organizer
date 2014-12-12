/**
 * TODO Collection of models. But maybe all of them are obsolete. But the isset function in the end seems to be in use.
 */

/**
 * TODO: Maybe obsolete, it seems to be never used
 * Teacher Model
 *
 * @class TeacherModel
 * @constructor
 */
Ext.define('TeacherModel',
{
    extend: 'MySched.Model',

    /**
     * Creating a teacher collection
     *
     * @param teacher
     */
    constructor: function (teacher)
    {
        console.log("TeacherModel constructor: maybe never used?");
        this.superclass.constructor.call(this, teacher, teacher);
    },
    /**
     *
     * @method getName
     * @return {string}
     */
    getName: function ()
    {
        console.log(MySched.Mapping.getTeacherName(this.id));
        return MySched.Mapping.getTeacherName(this.id);
    },
    /**
     *
     * @method getObjects
     * @return {*}
     */
    getObjects: function ()
    {
        console.log(MySched.Mapping.getObjects("teacher", this.id));
        return MySched.Mapping.getObjects("teacher", this.id);
    }
});

/**
 * TODO: Maybe obsolete, it seems to be never used
 * RoomModel
 * @param {Object} room
 */
Ext.define('RoomModel',
{
    extend: 'MySched.Model',

    constructor: function (room)
    {
        console.log("RoomModel constructor: maybe never used?");
        this.superclass.constructor.call(this, room, room);
    },
    getName: function ()
    {
        return MySched.Mapping.getRoomName(this.id);
    },
    getObjects: function ()
    {
        return MySched.Mapping.getObjects("room", this.id);
    }
});

/**
 * TODO: Maybe obsolete, it seems to be never used
 * PoolModel
 * @param {Object} module
 */
Ext.define('PoolModel',
{
    extend: 'MySched.Model',

    constructor: function (pool)
    {
        console.log("RoomModel constructor: maybe never used?");
        this.superclass.constructor.call(this, pool, pool);
    },
    getName: function ()
    {
        return MySched.Mapping.getPoolName(this.id);
    },
    getFullName: function ()
    {
        return MySched.Mapping.getObjectField("pool", this.id, "parentName") + " - " + MySched.Mapping.getObjectField("pool", this.id, "name");
    },
    getObjects: function ()
    {
        return MySched.Mapping.getObjects("pool", this.id);
    }
});

/**
 * TODO: Maybe obsolete, it seems to be never used
 * SubjectModel
 * @param {Object} module
 */
Ext.define('SubjectModel',
{
    extend: 'MySched.Model',

    constructor: function (subject)
    {
        console.log("SubjectModel constructor: maybe never used?");
        this.superclass.constructor.call(this, subject, subject);
    },
    getName: function ()
    {
        return MySched.Mapping.getSubjectName(this.id);
    },
    getFullName: function ()
    {
        return MySched.Mapping.getObjectField("subject", this.id, "parentName") + " - " + MySched.Mapping.getObjectField("subject", this.id, "name");
    },
    getObjects: function ()
    {
        return MySched.Mapping.getObjects("subject", this.id);
    }
});

/**
 * TODO: Maybe not in use anymore
 * @param mninr
 */
function getSubjectdesc(mninr)
{
    if (Ext.getCmp('content-anchor-tip'))
    {
        Ext.getCmp('content-anchor-tip').destroy();
    }
    var waitDesc = Ext.MessageBox.show(
    {
        cls: 'mySched_noBackground',
        closable: false,
        msg: '<img  src="' + MySched.mainPath + 'images/ajax-loader.gif" />'
    });
    Ext.Ajax.request(
    {
        url: _C('getSubject'),
        method: 'POST',
        params: { nrmni: mninr },
        scope: waitDesc,
        failure: function ()
        {
            waitDesc.hide();
            Ext.Msg.show(
            {
                minWidth: 400,
                fn: function ()
                {
                    Ext.MessageBox.hide();
                },
                buttons: Ext.MessageBox.OK,
                title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR,
                msg: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DESCRIPTION_ERROR
            });
        },
        success: function (response)
        {
            var responseData = [];
            try
            {
                responseData = Ext.decode(response.responseText);
                waitDesc.hide();

                //Modulnummer wurde gefunden :)
                if (responseData.success === true)
                {
                    Ext.Msg.show(
                    {
                        minWidth: 600,
                        fn: function ()
                        {
                            Ext.MessageBox.hide();
                        },
                        buttons: Ext.MessageBox.OK,
                        title: responseData.nrmni + " - " + responseData.title,
                        msg: responseData.html
                    });
                }

                //Modulnummer wurde nicht gefunden :(
                else
                {
                    Ext.Msg.show(
                    {
                        minWidth: 250,
                        fn: function ()
                        {
                            Ext.MessageBox.hide();
                        },
                        buttons: Ext.MessageBox.OK,
                        title: responseData.nrmni,
                        msg: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NO_DATA_FOUND + "!"
                    });
                }
            }
            catch (e)
            {
                waitDesc.hide();
                Ext.Msg.show(
                {
                    minWidth: 250,
                    fn: function ()
                    {
                        Ext.MessageBox.hide();
                    },
                    buttons: Ext.MessageBox.OK,
                    title: responseData.nrmni,
                    msg: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NO_DATA_FOUND + "!"
                });
            }

        }
    });
}

// TODO: Maybe it is not in use anymore
function zeigeTermine(rooms)
{
    console.log("function zeigeTermine: Maybe it is not in use anymore");
    if (Ext.ComponentMgr.get('sporadicPanel').collapsed)
    {
        Ext.ComponentMgr.get('sporadicPanel').expand();
    }

    var counterall = 0;
    var allrooms = Ext.ComponentMgr.get('sporadicPanel').body.select("p[id]");
    var index;
    for (index in allrooms.elements)
    {
        if (!Ext.isFunction(allrooms.elements[index]) && allrooms.elements[index].style !== null)
        {
            allrooms.elements[index].style.display = "none";
            counterall++;
        }
    }

    rooms = rooms.replace(/<[^>]*>/g, "").replace(/[\n\r]/g, '').replace(/ +/g, ' ').replace(/^\s+/g, '').replace(/\s+$/g, '').split(",");
    var counter = 0, room;
    for (var i = 0; i < rooms.length; i++)
    {
        room = rooms[i].replace(/[\n\r]/g, '').replace(/ +/g, ' ').replace(/^\s+/g, '').replace(/\s+$/g, '');
        var pos = room.search(/\s/);
        if (pos !== -1)
        {
            room = room.substring(0, pos);
        }
        var selectedroomevents = Ext.ComponentMgr.get('sporadicPanel').body.select("p[id^=" + room + "_]");
        for (index in selectedroomevents.elements)
        {
            if (selectedroomevents.elements.hasOwnProperty(index) &&
                !Ext.isFunction(selectedroomevents.elements[index]) &&
                selectedroomevents.elements[index].style !== null)
            {
                selectedroomevents.elements[index].style.display = "block";
                counter++;
            }
        }
    }

    if (counter !== 0)
    {
        Ext.ComponentMgr.get('sporadicPanel').setTitle(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SINGLE_EVENT + ' - ' + room + ' (' + counter + ')');
    }
}

/**
 * Checks if the given variable is not empty or undefined
 *
 * @param {*} me Variable that should be checked
 * @return {boolean} * False if given variable is empty, null or undefined
 */
function isset(me)
{
    if (me === null || me === '' || typeof me === 'undefined')
    {
        return false;
    }
    else
    {
        return true;
    }
}