/**
 * Mapping von Kuerzeln auf Namen
 * @author thorsten
 */

/**
 * Ersaetzt Ids zu Namen Bei Teacherenten, Studiengaengen, Modulen etc.
 */
MySched.Mapping = function() {
	var teacher, module, subject, lecture, room, types, roomtype, degree, field;

	return {
		init : function() {
			this.teacher = new MySched.Collection();
			this.module = new MySched.Collection();
			this.subject = new MySched.Collection();
			this.room = new MySched.Collection();
			this.roomtype = new MySched.Collection();
			this.degree = new MySched.Collection();
			this.field = new MySched.Collection();
			this.types = {
				teacher : MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER,
				module : MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER,
				room : MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOM,
				subject : MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SUBJECTS,
				roomtype : MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOMTYPE,
				degree : MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DEGREE,
				field : MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_FIELD
			};

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
		getModuleFullName : function(id) {
			return this.def(this.module.get(id, id), 'restriction', id);
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