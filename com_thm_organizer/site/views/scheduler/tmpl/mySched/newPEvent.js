/**
 * TODO: This is only called from a method in selectionManager (editLesson) that seemed to be not in use anymore.
 * TODO: So I stopped commenting.
 *
 * @param pday
 * @param pstime
 * @param petime
 * @param title
 * @param teacher_name
 * @param pool_name
 * @param room_name
 * @param l
 * @param key
 */
function newPEvent(pday, pstime, petime, title, teacher_name, pool_name, room_name, l, key)
{
    console.log("newPEvent");
    var lock;
    if (l)
    {
        lock = l;
    }
    else
    {
        lock = MySched.selectedSchedule.type;
    }
    var titel = {
        layout: 'form',
        width: 550,
        labelAlign: 'top',
        items: [
            {
                xtype: 'textfield',
                fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TITLE,
                width: 525,
                name: 'titel',
                id: 'titelid',
                value: title,
                emptyText: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EMPTY_LESSON_TITLE,
                blankText: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EMPTY_LESSON_TITLE,
                allowBlank: false
            }]
    };

    // Wird erstmal nicht mehr verwendet
    var notice = {
        layout: 'form',
        defaultType: 'htmleditor',
        width: 550,
        height: 160,
        hidden: true,
        // Verstecken
        items: [
            {
                fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DESCRIPTION,
                labelSeparator: '',
                width: 420,
                height: 170,
                name: 'notice',
                id: 'noticeid'
            }]
    };

    var datedata = [];
    for (var ddi = 1; ddi < MySched.daytime.length; ddi++)
    {
        datedata[datedata.length] = [ddi, MySched.daytime[ddi].gerName];
    }

    var date = {
        columnWidth: 0.33,
        layout: 'form',
        labelAlign: 'top',
        items: [
            {
                fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY,
                labelStyle: 'padding:0px;',
                name: 'cbday',
                id: 'cbdayid',
                readOnly: true,
                xtype: 'combo',
                mode: 'local',
                store: new Ext.data.ArrayStore(
                    {
                        id: 0,
                        fields: ['.myId', 'displayText'],
                        data: datedata
                    }),
                valueField: 'myId',
                displayField: 'displayText',
                minChars: 0,
                triggerAction: 'all',
                blankText: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DAY_CHOOSE,
                allowBlank: false,
                width: 170
            }]
    };

    var stime = {
        columnWidth: 0.33,
        layout: 'form',
        labelAlign: 'top',
        items: [
            {
                fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_STARTTIME,
                labelStyle: 'padding:0px;',
                name: 'startti',
                id: 'starttiid',
                xtype: 'timefield',
                value: pstime,
                blankText: 'Format hh:mm',
                emptyText: 'hh:mm',
                minValue: '8:00',
                maxValue: '19:00',
                format: 'H:i',
                vtype: 'ValidTime',
                allowBlank: false,
                width: 170
            }]
    };

    var etime = {
        columnWidth: 0.33,
        layout: 'form',
        labelAlign: 'top',
        items: [
            {
                fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ENDTIME,
                labelStyle: 'padding:0px;',
                name: 'endti',
                id: 'endtiid',
                xtype: 'timefield',
                blankText: 'Format hh:mm',
                emptyText: 'hh:mm',
                value: petime,
                minValue: '8:00',
                maxValue: '19:00',
                format: 'H:i',
                vtype: 'ValidTime',
                allowBlank: false,
                width: 170
            }]
    };

    var roomstore = [], i;
    for (i = 0; i < MySched.Mapping.room.length; i++)
    {
        roomstore.push(new Array(MySched.Mapping.room.items[i].id,
            MySched.Mapping.room.items[i].name.replace(/^\s+/, '')
                .replace(/\s+$/, '')));
    }

    var teacherstore = [];
    for (i = 0; i < MySched.Mapping.teacher.length; i++)
    {
        teacherstore.push(new Array(MySched.Mapping.teacher.items[i].id,
            MySched.Mapping.teacher.items[i].name));
    }

    var classstore = [];

    for (i = 0; i < MySched.Mapping.pool.length; i++)
    {
        classstore.push(new Array(MySched.Mapping.pool.items[i].id,
            MySched.Mapping.pool.items[i].department + " - " + MySched.Mapping.pool.items[i].name));
    }

    var pwin;

    var roomitem = {
        columnWidth: 0.33,
        layout: 'form',
        labelAlign: 'top',
        items: [
            {
                xtype: "multiselect",
                fieldLabel: "Ort",
                name: 'room',
                id: 'roomid',
                title: '',
                store: new Ext.data.ArrayStore(
                    {
                        fields: ['.myId', 'displayText'],
                        data: roomstore
                    }),
                width: 170,
                height: 80,
                cls: "ux-mselect",
                valueField: "myId",
                displayField: "displayText",
                ddReorder: true
            }]
    };

    var roomfield = {
        columnWidth: 0.33,
        layout: 'form',
        labelAlign: 'top',
        items: [
            {
                xtype: 'textfield',
                fieldLabel: '',
                name: 'roomfield',
                id: 'roomfieldid',
                emptyText: 'Raum eintragen',
                labelStyle: 'padding:0px;',
                width: 170
            }]
    };

    var teacheritem = {
        columnWidth: 0.33,
        layout: 'form',
        labelAlign: 'top',
        items: [
            {
                xtype: "multiselect",
                fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER,
                name: 'teacher',
                id: 'teacherid',
                title: '',
                store: new Ext.data.ArrayStore(
                    {
                        fields: ['.myId', 'displayText'],
                        data: teacherstore
                    }),
                width: 170,
                height: 80,
                cls: "ux-mselect",
                valueField: "myId",
                displayField: "displayText",
                ddReorder: true
            }]
    };

    var teacherfield = {
        columnWidth: 0.33,
        layout: 'form',
        labelAlign: 'top',
        items: [
            {
                xtype: 'textfield',
                fieldLabel: '',
                name: 'teacherfield',
                id: 'teacherfieldid',
                emptyText: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER_ENTER,
                labelStyle: 'padding:0px;',
                width: 170
            }]
    };

    var poolitem = {
        columnWidth: 0.33,
        layout: 'form',
        labelAlign: 'top',
        items: [
            {
                xtype: "multiselect",
                fieldLabel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_POOL,
                name: 'pool',
                id: 'poolid',
                title: '',
                store: new Ext.data.ArrayStore(
                    {
                        fields: ['.myId', 'displayText'],
                        data: classstore
                    }),
                width: 170,
                height: 80,
                cls: "ux-mselect",
                valueField: "myId",
                displayField: "displayText",
                ddReorder: true
            }]
    };

    var poolfield = {
        columnWidth: 0.33,
        layout: 'form',
        labelAlign: 'top',
        items: [
            {
                xtype: 'textfield',
                fieldLabel: '',
                name: 'clasfield',
                id: 'clasfieldid',
                emptyText: 'Modulpool eintragen',
                labelStyle: 'padding:0px;',
                width: 170
            }]
    };

    var addterminpanel = Ext.create('Ext.FormPanel',
        {
            frame: true,
            bodyStyle: 'padding:5px',
            width: 550,
            height: 305,
            layout: 'form',
            id: 'addterminpanel',
            defaults: {
                msgTarget: 'side'
            },
            items: [titel, notice, // Wird erstmal nicht mehr
                                   // verwendet
                {
                    xtype: 'fieldset',
                    hideLabel: true,
                    width: 540,
                    autoHeight: true,
                    hideBorders: true,
                    layout: 'column',
                    items: [roomitem, teacheritem, poolitem]
                },
                {
                    xtype: 'fieldset',
                    hideLabel: true,
                    width: 540,
                    autoHeight: true,
                    hideBorders: true,
                    layout: 'column',
                    items: [roomfield, teacherfield, poolfield]
                },
                {
                    xtype: 'fieldset',
                    hideLabel: true,
                    width: 540,
                    autoHeight: true,
                    hideBorders: true,
                    layout: 'column',
                    items: [date, stime, etime]
                },
                {
                    xtype: 'hidden',
                    id: "hiddenowner",
                    hidden: true
                },
                {
                    xtype: 'hidden',
                    id: "hiddenkey",
                    hidden: true
                }],
            buttonAlign: 'center',
            buttons: [
                {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ADD,
                    scope: this,
                    handler: function ()
                    {
                        var titel = Ext.getCmp('titelid')
                            .isValid(false);
                        var day = Ext.getCmp('cbdayid')
                            .isValid(false);
                        var stime = Ext.getCmp('starttiid')
                            .isValid(false);
                        var etime = Ext.getCmp('endtiid')
                            .isValid(false);

                        if (titel && day && stime && etime)
                        {
                            var blocks = timetoblocks(Ext.getCmp('starttiid')
                                .getValue(), Ext.getCmp('endtiid')
                                .getValue());
                            var date = Ext.Date.format(
                                new Date(), "d.m.Y");
                            var teachers = Ext.getCmp('teacherid')
                                .getValue();
                            var rooms = Ext.getCmp('roomid')
                                .getValue();
                            var pools = Ext.getCmp('poolid')
                                .getValue();

                            if (Ext.getCmp('teacherfieldid').getValue().replace(/^\s+/, '').replace(/\s+$/, '') !== "")
                            {
                                teachers = teachers + "," + Ext.getCmp('teacherfieldid').getValue();
                            }
                            if (Ext.getCmp('roomfieldid').getValue().replace(/^\s+/, '').replace(/\s+$/, '') !== "")
                            {
                                rooms = rooms + "," + Ext.getCmp('roomfieldid').getValue();
                            }
                            if (Ext.getCmp('poolfieldid').getValue().replace(/^\s+/, '').replace(/\s+$/, '') !== "")
                            {
                                pools = pools + "," + Ext.getCmp('poolfieldid').getValue();
                            }

                            teachers = teachers.split(",");
                            rooms = rooms.split(",");
                            pools = pools.split(",");

                            var teacher = "";
                            var room = "";
                            var pool = "";

                            var a, i, found;
                            for (a = 0; a < rooms.length; a++)
                            {
                                found = false;
                                if (rooms[a] !== "")
                                {
                                    for (i = 0; i < MySched.Mapping.room.length; i++)
                                    {
                                        if (MySched.Mapping.room.items[i].name.replace(/^\s+/, '').replace(/\s+$/, '') === rooms[a].replace(/^\s+/, '').replace(/\s+$/, ''))
                                        {
                                            if (!room.contains(MySched.Mapping.room.items[i].id))
                                            {
                                                if (room === "")
                                                {
                                                    room = MySched.Mapping.room.items[i].id;
                                                }
                                                else
                                                {
                                                    room = room + " " + MySched.Mapping.room.items[i].id;
                                                }
                                            }
                                            found = true;
                                            break;
                                        }
                                    }
                                    if (!found)
                                    {
                                        if (room === "")
                                        {
                                            room = rooms[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
                                        }
                                        else
                                        {
                                            room = room + " " + rooms[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
                                        }
                                    }
                                }
                            }

                            for (a = 0; a < teachers.length; a++)
                            {
                                found = false;
                                if (teachers[a] !== "")
                                {
                                    for (i = 0; i < MySched.Mapping.teacher.length; i++)
                                    {
                                        if (MySched.Mapping.teacher.items[i].name === teachers[a].replace(/^\s+/, '')
                                                .replace(/\s+$/, ''))
                                        {
                                            if (!teacher.contains(MySched.Mapping.teacher.items[i].id))
                                            {
                                                if (teacher === "")
                                                {
                                                    teacher = MySched.Mapping.teacher.items[i].id;
                                                }
                                                else
                                                {
                                                    teacher = teacher + " " + MySched.Mapping.teacher.items[i].id;
                                                }
                                            }
                                            found = true;
                                            break;
                                        }
                                    }
                                    if (!found)
                                    {
                                        if (teacher === "")
                                        {
                                            teacher = teachers[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
                                        }
                                        else
                                        {
                                            teacher = teacher + " " + teachers[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
                                        }
                                    }
                                }
                            }

                            for (a = 0; a < pools.length; a++)
                            {
                                found = false;
                                if (pools[a] !== "")
                                {
                                    for (i = 0; i < MySched.Mapping.pool.length; i++)
                                    {
                                        if ((MySched.Mapping.pool.items[i].department + " - " + MySched.Mapping.pool.items[i].name) ===
                                            pools[a].replace(/^\s+/, '').replace(/\s+$/, ''))
                                        {
                                            if (!pool.contains(MySched.Mapping.pool.items[i].id))
                                            {
                                                if (pool === "")
                                                {
                                                    pool = MySched.Mapping.pool.items[i].id;
                                                }
                                                else
                                                {
                                                    pool = pool + " " + MySched.Mapping.pool.items[i].id;
                                                }
                                            }
                                            found = true;
                                            break;
                                        }
                                    }
                                    if (!found)
                                    {
                                        if (pool === "")
                                        {
                                            pool = pools[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
                                        }
                                        else
                                        {
                                            pool = pool + " " + pools[a].replace(/^\s+/, '').replace(/\s+$/, '').replace(/\s+/, '_');
                                        }
                                    }
                                }
                            }

                            for (i = 0; i < blocks.size; i++)
                            {
                                var tkey = "";
                                if (Ext.getCmp('hiddenkey').getValue() === "")
                                {
                                    tkey = ("PE_" + Ext.getCmp('hiddenowner').getValue() + "_" + Ext.getCmp('cbdayid').getValue() + "_" + blocks[i] + "_" + date).toLowerCase();
                                }
                                else
                                {
                                    tkey = Ext.getCmp('hiddenkey').getValue();
                                }

                                var values, blotimes;
                                if (blocks.size === 1)
                                {
                                    values = {
                                        block: blocks[i],
                                        pool: pool,
                                        dow: Ext.getCmp('cbdayid')
                                            .getValue(),
                                        teacher: teacher,
                                        id: Ext.getCmp('titelid')
                                            .getValue(),
                                        key: tkey,
                                        room: room,
                                        subject: Ext.getCmp('titelid')
                                            .getValue(),
                                        type: "personal",
                                        desc: Ext.getCmp('noticeid')
                                            .getValue(),
                                        owner: Ext.getCmp('hiddenowner')
                                            .getValue(),
                                        stime: Ext.getCmp('starttiid')
                                            .getValue(),
                                        etime: Ext.getCmp('endtiid')
                                            .getValue(),
                                        showtime: "full",
                                        lock: lock,
                                        responsible: MySched.selectedSchedule.id
                                    };
                                }
                                else if (i === 0)
                                {
                                    blotimes = blocktotime(blocks[i]);
                                    if (Ext.getCmp('endtiid').getValue() !== blotimes[1])
                                    {
                                        blotimes = blotimes[1];
                                    }
                                    else
                                    {
                                        blotimes = Ext.getCmp('endtiid').getValue();
                                    }
                                    values = {
                                        block: blocks[i],
                                        pool: pool,
                                        dow: Ext.getCmp('cbdayid')
                                            .getValue(),
                                        teacher: teacher,
                                        id: Ext.getCmp('titelid')
                                            .getValue(),
                                        key: tkey,
                                        room: room,
                                        subject: Ext.getCmp('titelid')
                                            .getValue(),
                                        type: "personal",
                                        desc: Ext.getCmp('noticeid')
                                            .getValue(),
                                        owner: Ext.getCmp('hiddenowner')
                                            .getValue(),
                                        stime: Ext.getCmp('starttiid')
                                            .getValue(),
                                        etime: blotimes,
                                        showtime: "first",
                                        lock: lock,
                                        responsible: MySched.selectedSchedule.id
                                    };
                                }
                                else if ((i + 1) === blocks.size)
                                {
                                    blotimes = blocktotime(blocks[i]);
                                    if (Ext.getCmp('starttiid').getValue() !== blotimes[0])
                                    {
                                        blotimes = blotimes[0];
                                    }
                                    else
                                    {
                                        blotimes = Ext.getCmp('starttiid').getValue();
                                    }
                                    values = {
                                        block: blocks[i],
                                        pool: pool,
                                        dow: Ext.getCmp('cbdayid')
                                            .getValue(),
                                        teacher: teacher,
                                        id: Ext.getCmp('titelid')
                                            .getValue(),
                                        key: tkey,
                                        room: room,
                                        subject: Ext.getCmp('titelid')
                                            .getValue(),
                                        type: "personal",
                                        desc: Ext.getCmp('noticeid')
                                            .getValue(),
                                        owner: Ext.getCmp('hiddenowner')
                                            .getValue(),
                                        stime: blotimes,
                                        etime: Ext.getCmp('endtiid')
                                            .getValue(),
                                        showtime: "last",
                                        lock: lock,
                                        responsible: MySched.selectedSchedule.id
                                    };
                                }
                                else
                                {
                                    blotimes = blocktotime(blocks[i]);
                                    values = {
                                        block: blocks[i],
                                        pool: pool,
                                        dow: Ext.getCmp('cbdayid')
                                            .getValue(),
                                        teacher: teacher,
                                        id: Ext.getCmp('titelid')
                                            .getValue(),
                                        key: tkey,
                                        room: room,
                                        subject: Ext.getCmp('titelid')
                                            .getValue(),
                                        type: "personal",
                                        desc: Ext.getCmp('noticeid')
                                            .getValue(),
                                        owner: Ext.getCmp('hiddenowner')
                                            .getValue(),
                                        stime: blotimes[0],
                                        etime: blotimes[1],
                                        showtime: "none",
                                        lock: lock,
                                        responsible: MySched.selectedSchedule.id
                                    };
                                }

                                var record = new LectureModel(values.key, values);

                                if (MySched.selectedSchedule.id !== "mySchedule")
                                {
                                    // Änderungen den Stammdaten hinzufügen
                                    MySched.Base.schedule.addLecture(record);
                                    // Änderungen den Gesamtänderungen der Responsibles hinzufügen
                                    MySched.responsibleChanges.addLecture(record);
                                }

                                var lessons = MySched.Schedule.getLectures();
                                for (a = 0; a < lessons.length; a++)
                                {
                                    if (lessons[a].data.key === values.key)
                                    {
                                        MySched.Schedule.addLecture(record);
                                        break;
                                    }
                                }

                                // Änderungen dem aktuellen
                                // Stundenplan hinzufügen
                                MySched.selectedSchedule.addLecture(record);
                            }
                            MySched.selectedSchedule.eventsloaded = null;
                            MySched.selectedSchedule.refreshView();
                            if (pwin !== null)
                            {
                                pwin.close();
                            }

                        }
                    }
                },
                {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CANCEL,
                    handler: function (b, e)
                    {
                        if (pwin !== null)
                        {
                            pwin.close();
                        }
                    }
                }]
        });

    pwin = Ext.create('Ext.Window',
        {
            layout: 'form',
            id: 'terminWin',
            width: 560,
            iconCls: 'lesson_add',
            title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_ADD,
            height: 337,
            modal: true,
            frame: false,
            closeAction: 'close',
            items: [addterminpanel]
        });

    if (l)
    {
        pwin.setIconClass("lesson_edit");
        pwin.setTitle(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_CHANGE);
        addterminpanel.buttons[0].text = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CHANGE;
    }

    pwin.show();
    Ext.getCmp('cbdayid')
        .setValue(daytonumber(pday));
    Ext.getCmp('hiddenowner')
        .setValue(MySched.Authorize.user);

    if (key)
    {
        Ext.getCmp('hiddenkey').setValue(key);
    }

    Ext.getCmp('poolid').setValue(pool_name);
    Ext.getCmp('roomid').setValue(room_name);
    Ext.getCmp('teacherid').setValue(teacher_name);

    if (pool_name)
    {
        setFieldValue("pool", pool_name);
    }

    if (room_name)
    {
        setFieldValue("room", room_name);
    }

    if (teacher_name)
    {
        setFieldValue("teacher", teacher_name);
    }

    if (lock === "teacher")
    {
        if (!teacher_name)
        {
            setFieldValue("teacher", MySched.selectedSchedule.id);
        }
        Ext.getCmp('teacherid').disable();
        Ext.getCmp('teacherfieldid').disable();

    }
    else if (lock === "room")
    {
        if (!room_name)
        {
            setFieldValue("room", MySched.selectedSchedule.id);
        }
        Ext.getCmp('roomid').disable();
        Ext.getCmp('roomfieldid').disable();
    }
    else if (lock === "pool")
    {
        if (!pool_name)
        {
            setFieldValue("pool", MySched.selectedSchedule.id);
        }
        Ext.getCmp('poolid').disable();
        Ext.getCmp('poolfieldid').disable();
    }
}


function setFieldValue(type, str)
{
    var tempidarr = Ext.getCmp(type + 'id').getValue().split(",");
    var temparr = str.split(",");
    var tempstr = "";
    for (var tai = 0; tai < temparr.length; tai++)
    {
        var objtemp = MySched.Mapping.getObject(type, temparr[tai]);
        var strtemp = "";
        if (Ext.isObject(objtemp))
        {
            if (type === "pool")
            {
                strtemp = objtemp.department + " - " + objtemp.name;
            }
            else
            {
                strtemp = objtemp.name;
            }
        }
        else
        {
            strtemp = temparr[tai];
        }
        if (tempstr === "")
        {
            tempstr = strtemp;
        }
        else
        {
            tempstr = tempstr + "," + strtemp;
        }
    }
    Ext.getCmp(type + 'fieldid').setValue(tempstr);
}

function timetoblocks(stime, etime)
{
    var blocks = [], counter = 0;
    for (var i = 1; i <= 6; i++)
    {
        var times = blocktotime(i);
        if ((stime <= times[0] && etime >= times[1]) ||
            (stime >= times[0] && etime <= times[1]) ||
            (times[0] <= stime && times[1] > stime) ||
            (times[0] < etime && times[1] >= etime))
        {
            blocks[counter] = i;
            counter++;
        }
    }
    blocks.size = counter;
    return blocks;
}