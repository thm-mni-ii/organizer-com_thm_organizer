/**
 * Mapping from acronym to names
 *
 * @author thorsten
 * @class MySched.Mapping
 * @constructor
 */
MySched.Mapping = function ()
{
    var teacher, pool, subject, room, types, roomtype, degree, field;

    return {
        /**
         * Initialization. Creating a collection for every resource.
         *
         * @method init
         */
        init: function ()
        {
            this.teacher = new MySched.Collection();
            this.pool = new MySched.Collection();
            this.subject = new MySched.Collection();
            this.room = new MySched.Collection();
            this.roomtype = new MySched.Collection();
            this.degree = new MySched.Collection();
            this.field = new MySched.Collection();
        },
        /**
         * TODO: Do know exactly. Getting Data from an array, but why this way?
         *
         * @method def
         * @param {Object} arr Object with information like gpuntisID, name, localUnitsID, longname, degree, restriction, description
         * @param {String} val TODO: Not sure what it is
         * @param {String} def TODO: Not sure what it is
         * @returns {String} *
         */
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
        getTeacherParent: function (id)
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
            for (var teacher in this.teacher.map)
            {
                if (this.teacher.map.hasOwnProperty(teacher))
                {
                    var teacherObject = this.teacher.map[teacher];
                    if (Ext.isObject(teacherObject))
                    {
                        if (teacherObject.dbID === dbID)
                        {
                            return teacher;
                        }
                    }
                }
            }
            return dbID;
        },
        getPoolName: function (id)
        {
            return this.def(this.pool.get(id, id), 'name', id);
        },
        getPoolFullName: function (id)
        {
            return this.def(this.pool.get(id, id), 'restriction', id);
        },
        getPoolParent: function (id)
        {
            return this.def(this.pool.get(id, id), 'degree', id);
        },
        getGrid: function (id)
        {
            return this.def(this.pool.get(id, id), 'grid', id);
        },
        getRoomParent: function (id)
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
            for (var room in this.room.map)
            {
                if (this.room.map.hasOwnProperty(room))
                {
                    var roomObject = this.room.map[room];
                    if (Ext.isObject(roomObject))
                    {
                        if (roomObject.dbID === dbID)
                        {
                            return room;
                        }
                    }
                }
            }
            return dbID;
        },
        getSubjectParent: function (id)
        {
            return this.def(this.subject.get(id, id), 'description', id);
        },
        getSubjectName: function (id)
        {
            return this.def(this.subject.get(id, id), 'longname', id);
        },
        getSubjectShortName: function (id)
        {
            return this.def(this.subject.get(id, id), 'shortname', id);
        },
        getSubjectAbbreviation: function (id)
        {
            return this.def(this.subject.get(id, id), 'abbreviation', id);
        },
        getSubjectLink: function (id)
        {
            return this.def(this.subject.get(id, id), 'link', '');
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
        getDegreeName: function (id)
        {
            return this.def(this.degree.get(id, id), 'name', id);
        }
    };
}();