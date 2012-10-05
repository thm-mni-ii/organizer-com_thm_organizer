/**
 * Mapping von Kuerzeln auf Namen
 * @author thorsten
 */

/**
 * Ersaetzt Ids zu Namen Bei Teacherenten, Studiengaengen, Modulen etc.
 */
MySched.Mapping = function() {
	var teacher, module, subject, lecture, room, types;

	return {
		init : function() {
			this.teacher = new MySched.Collection();
			this.module = new MySched.Collection();
			this.subject = new MySched.Collection();
			this.room = new MySched.Collection();
			this.types = {
				teacher : MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER,
				module : MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER,
				room : MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOM,
				subject : MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SUBJECTS
			};

		},
		load : function(node) {
			this.readModule(Ext.DomQuery.select('modules', node));
			this.readTeacher(Ext.DomQuery.select('teachers', node));
			this.readRoom(Ext.DomQuery.select('rooms', node));
			this.readSubject(Ext.DomQuery.select('subjects', node));
		},
		readSubject : function(n) {
			var def = Ext.data.Record.create([ {
				name : 'id',
				mapping : '@id'
			}, {
				name : 'name',
				mapping : 'longname'
			}, {
				name : 'desc',
				mapping : 'text'
			} ]);
			var xml = Ext.create('Ext.data.XmlReader', {
				record : "subject",
				id : "id"
			}, def);
			Ext.each(xml.readRecords(n).records, function(e) {
				this.subject.add(e.data.id, e.data);
			}, this);
		},
		readTeacher : function(n) {
			var def = Ext.data.Record.create([ {
				name : 'id',
				mapping : '@id'
			}, {
				name : 'name',
				mapping : 'surname'
			}, {
				name : 'objects',
				mapping : 'text'
			} ]);
			var xml = Ext.create('Ext.data.XmlReader', {
				record : "teacher",
				id : "id"
			}, def);
			Ext.each(xml.readRecords(n).records, function(e) {
				this.teacher.add(e.data.id, e.data);
			}, this);
		},
		readRoom : function(n) {
			var def = Ext.data.Record.create([ {
				name : 'id',
				mapping : '@id'
			}, {
				name : 'name',
				mapping : 'longname'
			}, {
				name : 'cap',
				mapping : 'capacity'
			}, {
				name : 'objects',
				mapping : 'text'
			} ]);
			var xml = Ext.create('Ext.data.XmlReader', {
				record : "room",
				id : "id"
			}, def);
			Ext.each(xml.readRecords(n).records, function(e) {
				this.room.add(e.data.id, e.data);
			}, this);
		},
		readModule : function(n) {
			var def = Ext.data.Record.create([ {
				name : 'id',
				mapping : '@id'
			}, {
				name : 'name',
				mapping : 'longname'
			}, {
				name : 'objects',
				mapping : 'text'
			}
			// no comma here ;)
			]);
			var xml = Ext.create('Ext.data.XmlReader', {
				record : "modules",
				id : "id"
			}, def);
			Ext.each(xml.readRecords(n).records, function(e) {
				this.module.add(e.data.id, e.data);
			}, this);
		},
		def : function(arr, val, def) {
			if (arr && arr[val])
				return arr[val];
			return def || '';
		},
		getName : function(type, id) {
			return this.def(this[type].get(id, id), 'surname', id);
		},
		getObjectField : function(type, id, field) {
			return this.def(this[type].get(id, id), field, id);
		},
		getObjects : function(type, id) {
			return this.def(this[type].get(id, id), 'objects', id);
		},
		getObject : function(type, id) {
			return this[type].get(id);
		},
		getTeacherSurname : function(id) {
			return this.def(this.teacher.get(id, id), 'surname', id);
		},
		getModuleName : function(id) {
			return this.def(this.module.get(id, id), 'name', id);
		},
		getRoomName : function(id) {
			return this.def(this.room.get(id, id), 'name', id);
		},
		getSubjectName : function(id) {
			return this.def(this.subject.get(id, id), 'longname', id);
		},
		getLectureName : function(id) {
			return this.def(this.subject.get(id, id), 'name', id);
		},
		getLectureDescription : function(id) {
			return this.def(this.subject.get(id, id), 'desc');
		},
		getFullTypeName : function(id) {
			return this.types[id.toLowerCase()];
		}
	}
}();