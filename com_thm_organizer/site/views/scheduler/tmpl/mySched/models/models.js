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
        this.superclass.constructor.call(this, teacher, teacher);
    },
    /**
     *
     * @method getName
     * @return {string}
     */
    getName: function ()
    {
        return MySched.Mapping.getTeacherName(this.id);
    },
    /**
     *
     * @method getObjects
     * @return {*}
     */
    getObjects: function ()
    {
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