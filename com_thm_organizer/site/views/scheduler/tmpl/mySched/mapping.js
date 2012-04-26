/**
 * Mapping von Kuerzeln auf Namen
 * @author thorsten
 */


/**
 * Ersaetzt Ids zu Namen
 * Bei Dozenten, Studiengaengen, Modulen etc.
 */
MySched.Mapping = function () {
	var doz, clas, subject, lecture, room, types;

	return {
		init: function () {
			this.doz = new MySched.Collection();
			this.clas = new MySched.Collection();
			this.subject = new MySched.Collection();
			this.room = new MySched.Collection();
			this.types = {
				doz: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER,
				clas: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SEMESTER,
				room: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOM,
				subject: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SUBJECTS
			};

		},
		load: function (node) {
			this.readClas(Ext.DomQuery.select('classes', node));
			this.readDoz(Ext.DomQuery.select('teachers', node));
			this.readRoom(Ext.DomQuery.select('rooms', node));
			this.readSubject(Ext.DomQuery.select('subjects', node));
		},
		readSubject: function (n) {
			var def = Ext.data.Record.create([{
				name: 'id',
				mapping: '@id'
			},
			{
				name: 'name',
				mapping: 'longname'
			},
			{
				name: 'desc',
				mapping: 'text'
			}]);
			var xml = Ext.create('Ext.data.XmlReader', {
				record: "subject",
				id: "id"
			}, def);
			Ext.each(xml.readRecords(n).records, function (e) {
				this.subject.add(e.data.id, e.data);
			}, this);
		},
		readDoz: function (n) {
			var def = Ext.data.Record.create([{
				name: 'id',
				mapping: '@id'
			},
			{
				name: 'name',
				mapping: 'surname'
			},
			{
				name: 'objects',
				mapping: 'text'
			}]);
			var xml = Ext.create('Ext.data.XmlReader', {
				record: "teacher",
				id: "id"
			}, def);
			Ext.each(xml.readRecords(n).records, function (e) {
				this.doz.add(e.data.id, e.data);
			}, this);
		},
		readRoom: function (n) {
			var def = Ext.data.Record.create([{
				name: 'id',
				mapping: '@id'
			},
			{
				name: 'name',
				mapping: 'longname'
			},
			{
				name: 'cap',
				mapping: 'capacity'
			},
			{
				name: 'objects',
				mapping: 'text'
			}]);
			var xml = Ext.create('Ext.data.XmlReader', {
				record: "room",
				id: "id"
			}, def);
			Ext.each(xml.readRecords(n).records, function (e) {
				this.room.add(e.data.id, e.data);
			}, this);
		},
		readClas: function (n) {
			var def = Ext.data.Record.create([{
				name: 'id',
				mapping: '@id'
			},
			{
				name: 'name',
				mapping: 'longname'
			},
			{
				name: 'objects',
				mapping: 'text'
			}
			// no comma here ;)
			]);
			var xml = Ext.create('Ext.data.XmlReader', {
				record: "class",
				id: "id"
			}, def);
			Ext.each(xml.readRecords(n).records, function (e) {
				this.clas.add(e.data.id, e.data);
			}, this);
		},
		def: function (arr, val, def) {
			if (arr && arr[val]) return arr[val];
			return def || '';
		},
		getName: function (type, id) {
			return this.def(this[type].get(id, id), 'name', id);
		},
		getObjectField: function (type, id, field) {
			return this.def(this[type].get(id, id), field, id);
		},
		getObjects: function (type, id) {
			return this.def(this[type].get(id, id), 'objects', id);
		},
		getObject: function (type, id) {
			return this[type].get(id);
		},
		getDozName: function (id) {
			return this.def(this.doz.get(id, id), 'name', id);
		},
		getClasName: function (id) {
			return this.def(this.clas.get(id, id), 'name', id);
		},
		getRoomName: function (id) {
			return this.def(this.room.get(id, id), 'name', id);
		},
		getSubjectName: function (id) {
			return this.def(this.subject.get(id, id), 'name', id);
		},
		getLectureName: function (id) {
			return this.def(this.subject.get(id, id), 'name', id);
		},
		getLectureDescription: function (id) {
			return this.def(this.subject.get(id, id), 'desc');
		},
		getFullTypeName: function (id) {
			return this.types[id.toLowerCase()];
		}
	}
}();