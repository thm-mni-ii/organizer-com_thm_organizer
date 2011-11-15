/**
 * Reader-Objekte spezialisiert fuer MySched
 * @author thorsten
 */

/**
 * Spezieller XmlReader
 * zum Einlesen der StundenplanRohDaten
 */
SchedXmlReader = function () {
	var recordType = Ext.data.Record.create([{
		name: 'dow',
		mapping: 'times>time>assigned_day',
		type: 'string'
	},
	{
		name: 'block',
		mapping: 'times>time>assigned_period',
		type: 'string'
	},
	{
		name: 'clas',
		mapping: 'lesson_classes',
		xmlType: 'string'
	},
	{
		name: 'room',
		mapping: 'times>time>assigned_room',
		type: 'string'
	},
	{
		name: 'doz',
		mapping: 'lesson_teacher',
		xmlType: 'string'
	},
	{
		name: 'category',
		mapping: 'text1',
		xmlType: 'string'
	}]);

	meta = {
		root: "lessons>lesson",
		subject: "subjects>subject",
		cyclic: "times",
		sporadic: "sporadics>sporadic"
	};
	SchedXmlReader.superclass.constructor.call(this, meta, recordType || meta.fields);
}
Ext.extend(SchedXmlReader, Ext.data.XmlReader, {
	extractValue: function (n, f) {
		var def = f.defaultValue || f;
		n = n[0] ? n[0] : n;
		var v = (n && n.firstChild ? n.firstChild.nodeValue : null);
		v = ((v === null || v === undefined || v === '') ? def : v);
		if (Ext.isEmpty(v)) def;
		if (typeof f == 'object') v = f.convert(v);
		return v;
	},
	readRecord: function (root, def, key, records) {
		var q = Ext.DomQuery;
		var recordType = this.recordType,
			fields = recordType.prototype.fields;
		var values = def;
		var n = root; // ein einzelnes Item
		var lessonid = key.replace("LS_", "");
		lessonid = lessonid.substring(0, lessonid.length - 2);
		// Alle Felder werden in diesem Record ausgelesen
		for (var j = 0, jlen = fields.length; j < jlen; j++) {
			var f = fields.items[j];
			// Hier werden, falls vorhanden. auch mehrere Kindelemente als Array gespeichert
			var arr = q.select(f.mapping, n);
			var val = [];
			var number = q.selectValue("periods", n);
			if ((f.name == 'room' || f.name == 'dow' || f.name == 'block') && arr.length != number) {

				for (var index = 0; index < number; index++)
				val[index] = "n.v.";

			}
			else {
				for (var d = 0, dlen = arr.length; d < dlen; d++) {
					if (f.name == 'room' || f.name == 'clas' || f.name == 'doz') {
						val[d] = arr[d].getAttribute('id');
					}
					else val[d] = this.extractValue(arr[d], f);
				}
			}
			if (f.xmlType == 'array') {
				values[f.name] = val;
			} else if (val.length == 1) {
				values[f.name] = val[0];
			} else {
				values[f.name] = val.join(' ');
			}
		}
		var mydowarray = values.dow.split(" ");
		var myblockarray = values.block.split(" ");
		var myroomarray = values.room.split(" ");
		var tempdoz = values.doz;
		for (var i = 0; i < mydowarray.length; i++) {
			values.dow = mydowarray[i];
			values.block = myblockarray[i];
			values.room = myroomarray[i];
			values.doz = tempdoz;
			// Nur mit Termin ist veranstaltung gueltig
			if (!values.dow) continue;
			// Berechnet Wochentag
			//values.dow = this.numbertoday(parseInt(values.begin));
			// Erstellt eindeutigen Key
			values.key = (def.subject + "_" + values.dow + "_" + values.block + "_" + lessonid).toLowerCase();
			// Sporatische Veranstaltungen bekommen eine extra ID
			if (def.type == 'sporadic') values.key = 'SP_' + values.key;
			values.owner = "gpuntis";
			values.showtime = "none";
			var times = blocktotime(values.block);
			values.stime = times[0];
			values.etime = times[1];
			if (records[values.key.toString()]) {
				if (records[values.key.toString()].data.doz.search(new RegExp(values.doz)) == -1) values.doz = records[values.key.toString()].data.doz + " " + values.doz;
				else values.doz = records[values.key.toString()].data.doz;

				if (records[values.key.toString()].data.room.search(new RegExp(values.room)) == -1) values.room = records[values.key.toString()].data.room + " " + values.room;
				else values.room = records[values.key.toString()].data.room;
				var record = new mLecture(values.key, values);
				record.node = n;
				records[values.key.toString()] = record;
			}
			else {
				var record = new mLecture(values.key, values);
				record.node = n;
				records[values.key.toString()] = record;
			}
		}
		return records;
	},
	readRecords: function (doc) {
		// Laed die Mappingdaten
		MySched.Mapping.load(Ext.DomQuery.select('document', doc));
		// Auslesen des Datums - Leider nicht nach ISO8601, deshalb substring
		this.version = Ext.Date.parseDate(Ext.DomQuery.selectNode('document', doc).getAttribute('date'), 'Ymd');
		this.sessionbegin = Ext.DomQuery.selectValue('document>general>schoolyearbegindate', doc);
		this.sessionend = Ext.DomQuery.selectValue('document>general>schoolyearenddate', doc);

		var records = [];
		return {
			success: success,
			records: records,
			totalRecords: totalRecords
		};

		var timeperiods = Ext.DomQuery.selectNode('document>timeperiods', doc);

		if (timeperiods.children) timeperiods = timeperiods.children;
		else timeperiods = timeperiods.childNodes;
		for (var tsi = 0; tsi < timeperiods.length; tsi++) {
			if (timeperiods[tsi].nodeType == 1) {
				if (timeperiods[tsi].children) var timeperiod = timeperiods[tsi].children;
				else var timeperiod = timeperiods[tsi].childNodes;
				if (timeperiod[0].nodeType == 3) {
					var day = timeperiod[1].firstChild.nodeValue;
					var block = timeperiod[3].firstChild.nodeValue;
					var stime = timeperiod[5].firstChild.nodeValue;
					var etime = timeperiod[7].firstChild.nodeValue;
				}
				else {
					var day = timeperiod[0].firstChild.nodeValue;
					var block = timeperiod[1].firstChild.nodeValue;
					var stime = timeperiod[2].firstChild.nodeValue;
					var etime = timeperiod[3].firstChild.nodeValue;
				}
				stime = stime.substr(0, 2) + ":" + stime.substr(2);
				etime = etime.substr(0, 2) + ":" + etime.substr(2);
				if (!MySched.daytime[day]) {
					MySched.daytime[day] = new Array();
					MySched.daytime[day]["localName"] = "day";
					MySched.daytime[day]["gerName"] = weekdayEtoD(numbertoday(day));
					MySched.daytime[day]["engName"] = numbertoday(day);
				}
				if (!MySched.daytime[day][block]) {
					MySched.daytime[day][block] = new Array();
					MySched.daytime[day][block]["localName"] = "block";
					MySched.daytime[day][block]["stime"] = stime;
					MySched.daytime[day][block]["etime"] = etime;
				}
			}
		}

		this.xmlData = doc;
		var root = doc.documentElement || doc;
		var q = Ext.DomQuery;
		var recordType = this.recordType,
			fields = recordType.prototype.fields;
		var sid = this.meta.id;
		var totalRecords = 0,
			success = true;
		if (this.meta.totalRecords) {
			totalRecords = q.selectNumber(this.meta.totalRecords, root, 0);
		}

		if (this.meta.success) {
			var sv = q.selectValue(this.meta.success, root, true);
			success = sv !== false && sv !== 'false';
		}
		var ns = q.select(this.meta.root, root);
		// Alle Lectures werden jetzt durchlaufen
		for (var i = 0, len = ns.length; i < len; i++) {
			var lec = ns[i];
			var subject = q.selectNode('lesson_subject', lec).getAttribute('id');
			records = this.readRecord(lec, {
				type: 'cyclic',
				id: subject,
				subject: subject
			}, lec.attributes[0].value, records);
		}
		var temprecords = [];
		for (var index in records) {
			if (!Ext.isFunction(records[index])) {
				temprecords[temprecords.length] = records[index];
			}
		}

		return {
			success: success,
			records: temprecords,
			totalRecords: totalRecords || temprecords.length
		};
	}
});

/**
 * Spzieller JsonReader
 * fuer Laden/Speichern des Stundenplans
 * @author thorsten
 */

SchedJsonReader = function () {
	SchedJsonReader.superclass.constructor.call(this, this.config);
}
Ext.extend(SchedJsonReader, Ext.data.JsonReader, {
	/**
	 * Create a data block containing Ext.data.Records from an XML document.
	 * @param {Object} doc A parsed XML document.
	 * @return {Object} records A data block which is used by an {@link Ext.data.Store} as
	 * a cache of Ext.data.Records.
	 */
	readRecords: function (o) {
		var records = [],
			success = true;
		if (o.success === false) {
			return {
				success: false,
				code: o.code,
				errors: o.errors
			};
		}



		Ext.Array.each(o, function (item, index, allItems) {
			if(typeof item.key !== "undefined")
				records[records.length] = new mLecture(item.key, item, item.semesterID, item.plantypeID);
		});

		if(typeof records.length === "undefined")
			records.length = 0;

		return {
			success: success,
			records: records,
			totalRecords: records.length
		};
	}

});