/*global Ext: false, MySched: false, MySchedLanguage: false */
/*jshint strict: false */
/**
 * Mapping von Kuerzeln auf Namen
 * @author thorsten
 */
/**
 * Ersaetzt Ids zu Namen Bei Dozenten, Studiengaengen, Modulen etc.
 */
MySched.Mapping = function ()
{
    var teacher, module, subject, lecture, room, types, roomtype, degree, field;

    return {
        init: function ()
        {
            this.teacher = new MySched.Collection();
            this.module = new MySched.Collection();
            this.subject = new MySched.Collection();
            this.room = new MySched.Collection();
            this.roomtype = new MySched.Collection();
            this.degree = new MySched.Collection();
            this.field = new MySched.Collection();
            this.types = {
                teacher: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER,
                module: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER,
                room: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOM,
                subject: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SUBJECTS,
                roomtype: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOMTYPE,
                degree: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DEGREE,
                field: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_FIELD
            };

        },
        def: function (arr, val, def)
        {
            if (arr && arr[val])
            {
                return arr[val];
            }
            return def || '';
        },
        getName: function (type, id)
        {
            return this.def(this[type].get(id, id), 'surname', id);
        },
        getObjectField: function (type, id, field)
        {
            return this.def(this[type].get(id, id), field, id);
        },
        getObjects: function (type, id)
        {
            return this.def(this[type].get(id, id), 'objects', id);
        },
        getObject: function (type, id)
        {
            return this[type].get(id);
        },
        getTeacherSurname: function (id)
        {
            return this.def(this.teacher.get(id, id), 'surname', id);
        },
        getTeacherParent: function(id)
        {
            return this.def(this.teacher.get(id, id), 'description', id);
        },
        getTeacherFirstname: function (id)
        {
            return this.def(this.teacher.get(id, id), 'forename', id);
        },
        getTeacherDbID: function (id)
        {
            return this.def(this.teacher.get(id, id), 'dbID', id);
        },
        getTeacherKeyByID: function (dbID)
        {
            for(var teacher in this.teacher.map)
            {
                if(this.teacher.map.hasOwnProperty(teacher))
                {
                    var teacherObject = this.teacher.map[teacher];
                    if(Ext.isObject(teacherObject))
                    {
                        if(teacherObject.dbID === dbID)
                        {
                            return teacher;
                        }
                    }
                }
            }
            return dbID;
        },
        getModuleName: function (id)
        {
            return this.def(this.module.get(id, id), 'name', id);
        },
        getModuleFullName: function (id)
        {
            return this.def(this.module.get(id, id), 'restriction', id);
        },
        getModuleParent: function(id)
        {
            return this.def(this.module.get(id, id), 'degree', id);
        },
        getRoomParent: function(id)
        {
            return this.def(this.room.get(id, id), 'description', id);
        },
        getRoomName: function (id)
        {
            return this.def(this.room.get(id, id), 'longname', id);
        },
        getRoomDbID: function (id)
        {
            return this.def(this.room.get(id, id), 'dbID', id);
        },
        getRoomKeyByID: function (dbID)
        {
            for(var room in this.room.map)
            {
                if (this.room.map.hasOwnProperty(room))
                {
                    var roomObject = this.room.map[room];
                    if(Ext.isObject(roomObject))
                    {
                        if(roomObject.dbID === dbID)
                        {
                            return room;
                        }
                    }
                }
            }
            return dbID;
        },
        getSubjectParent: function(id)
        {
            return this.def(this.subject.get(id, id), 'description', id);
        },
        getSubjectName: function (id)
        {
            return this.def(this.subject.get(id, id), 'longname', id);
        },
        getSubjectNo: function (id)
        {
            return this.def(this.subject.get(id, id), 'subjectNo', id);
        },
        getLectureName: function (id)
        {
            return this.def(this.subject.get(id, id), 'name', id);
        },
        getLectureDescription: function (id)
        {
            return this.def(this.subject.get(id, id), 'desc', id);
        },
        getFullTypeName: function (id)
        {
            return this.types[id.toLowerCase()];
        },
        getDegreeName: function (id)
        {
            return this.def(this.degree.get(id, id), 'name', id);
        }
    };
}();