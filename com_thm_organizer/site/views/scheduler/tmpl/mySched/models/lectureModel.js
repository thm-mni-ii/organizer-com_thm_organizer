/**
 * model of the lecture
 *
 * @class LectureModel
 * @constructor
 */
Ext.define('LectureModel',
    {
        extend: 'MySched.Model',
        /**
         * Initialize class variables
         *
         * @param {String} id TODO: Do not know the id of what (looks like e.g MNISS_34281tuesday)
         * @param {object} data Object with information above block, pools, teacher, subject and more
         * @param {number} semesterID Id of the semester
         * @param plantypeID TODO I don't know what is is, because it was always empty
         */
        constructor: function (id, data, semesterID, plantypeID)
        {
            var teacher,room, pool, subject, cellTemplate, infoTemplate, owner = data.owner, showtime = data.showtime;

            this.superclass.constructor.call(this, id, Ext.clone(data));
            this.data.teachers = new MySched.Collection();
            this.data.teachers.addAll(data.teachers);
            this.data.rooms = new MySched.Collection();
            this.data.rooms.addAll(data.rooms);
            this.data.pools = new MySched.Collection();
            this.data.pools.addAll(data.pools);
            this.data.subjects = new MySched.Collection();
            this.data.subjects.addAll(data.subjects);

            this.semesterID = semesterID;
            this.plantypeID = plantypeID;

            if (this.data.moduleID === MySched.searchModuleID && !Ext.isEmpty(MySched.searchModuleID))
            {
                this.data.css = this.data.css + " searchSubject";
            }

            //New CellStyle
            this.setCellTemplate(null, this.data.grid);

            var infoTemplateString = '<div>' + '<small><span class="def">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOM + ':</span> {roomName}<br/>' + '<span class="def">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER + ':</span><big> {teacherName}</big><br/>' + '<span class="def">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_POOL + ':</span> <br/>{poolName}<br/>';
            infoTemplateString += '</small></div>';

            this.infoTemplate = new Ext.Template(infoTemplateString);

            this.sporadicTemplate = new Ext.Template('<div id="{parentId}##{id}" block="{lessonBlock}" dow="{lessonDow}" class="{css} sporadicBox lectureBox">' + '<b>{desc}</b> <small><i>({desc:defaultValue("' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NO_DESCRIPTION + '")})</i> ' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOM + ': {room_short} - ' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHER + ': {teacher_name} - {poolName}</small>' + '</div>');
        },
        /**
         * Returns detailed data for a resource
         *
         * @method getDetailData
         * @param {object} d An object with data of a resource
         * @return {object} * Object with detailed data
         */
        getDetailData: function (d)
        {
            return Ext.apply(this.getData(d),
                {
                    'lessonTitle': this.getLessonTitle(d),
                    'teacherName': this.getTeacherNames(d),
                    'poolName': this.getPoolName(d),
                    'roomName': this.getRoomName(d),
                    'weekday': weekdayEtoD(this.getWeekDay()),
                    'block': this.getBlock(),
                    'description': this.getDescription(),
                    'changesAll': this.getChanges(d),
                    'statusIcons': this.getStatus(d),
                    'topIcon': this.getTopIcon(d),
                    'comment': this.getComment(d),
                    'deltaStatus': this.getDeltaStatus(d),
                    'curriculumColor': this.getCurriculumColor(d),
                    'lessonEvents': this.getEvents(d),
                    'moduleNr': this.getModuleNr(d)
                }
            );
        },
        /**
         * Returns the Module number
         *
         * @method getModuleNr
         * @param {object} d Object with data above the requested resource
         * @return {string} * Number of the Module
         */
        getModuleNr: function (d)
        {
            var firstSubject = this.data.subjects.keys[0];
            var moduleNr = MySched.Mapping.getSubjectNo(firstSubject);
            if (moduleNr !== firstSubject && MySched.displayModuleNumber === true)
            {
                return "(" + moduleNr + ")";
            }
            return "";
        },
        /**
         * Returns the color of the requested resource according to the curriculum
         *
         * @method getCurriculumColor
         * @param {object} d Object with data above the requested resource
         * @return {string} * The color of the curriculum
         */
        getCurriculumColor: function (d)
        {
            if (MySched.selectedSchedule === null)
            {
                return "";
            }
            var curriculumColors = MySched.startup.curriculumColors;

            if (curriculumColors.length < 1)
            {
                return "";
            }

            var key = MySched.selectedSchedule.key;
            var moduleName = MySched.Mapping.getModuleFullName(key);
            var moduleDegree = MySched.Mapping.getModuleParent(key);
            var degreeName = MySched.Mapping.getDegreeName(moduleDegree);

            for(var index = 0; index < curriculumColors.length; index++)
            {
                var curriculumColor = curriculumColors[index];
                if (curriculumColor.semesterName === moduleName && curriculumColor.organizerMajorName === degreeName)
                {
                    return "background-color: #" + curriculumColor.hexColorCode;
                }
            }
            return "";
        },
        /**
         * TODO Returns the delta status. What the the hell this is...
         *
         * @method getDeltaStatus
         * @param {object} d Object with data above the requested resource
         * @return {string} TODO Was always empty in tests, so I do not now what it is
         */
        getDeltaStatus: function (d)
        {
            var currentMoFrDate = getCurrentMoFrDate();
            var returnValue = "";
            if (d.showDelta === true)
            {
                for(var dateIndex in this.data.calendar)
                {
                    if (this.data.calendar.hasOwnProperty(dateIndex))
                    {
                        var dateObject = convertEnglishDateStringToDateObject(dateIndex);
                        if (dateObject >= currentMoFrDate.monday && dateObject <= currentMoFrDate.friday && this.data.calendar[dateIndex][this.data.block].lessonData.delta)
                        {
                            returnValue = "delta" + this.data.calendar[dateIndex][this.data.block].lessonData.delta;
                        }
                    }
                }
            }
            return returnValue;
        },
        /**
         * Returns the title of the lesson
         *
         * @method getLessonTitle
         * @param {object} d Object with data above the requested resource
         * @return {String} * Title of the lesson
         */
        getLessonTitle: function (d)
        {
            var subjectKeys = this.data.subjects.keys;
            var subjectNames = [];
            var lessonTitle = "";

            if(subjectKeys.length === 1)
            {
                lessonTitle = MySched.Mapping.getSubjectShortName(subjectKeys[0]);
                if(lessonTitle === subjectKeys[0])
                {
                    lessonTitle = MySched.Mapping.getSubjectAbbreviation(subjectKeys[0]);
                    if(lessonTitle === subjectKeys[0])
                    {
                        lessonTitle = MySched.Mapping.getSubjectName(subjectKeys[0]);
                    }
                }
            }
            else if(subjectKeys.length > 1)
            {
                for(var index = 0; index < subjectKeys.length; index++)
                {
                    var abbreviation = MySched.Mapping.getSubjectAbbreviation(subjectKeys[index]);
                    if(abbreviation === subjectKeys[index])
                    {
                        abbreviation = MySched.Mapping.getSubjectName(subjectKeys[index]);
                    }

                    subjectNames.push(abbreviation);
                }
                lessonTitle = subjectNames.join(" / ");
            }
            else
            {
                lessonTitle = this.data.name;
            }

            return lessonTitle;
        },
        /**
         * Returns the comment
         *
         * @method getComment
         * @param {object} d Object with data above the requested resource
         * @return {string} * Returns the comment
         */
        getComment: function (d)
        {
            if (!Ext.isEmpty(d.comment) && Ext.isString(d.comment))
            {
                return "<br/>" + d.comment + "";
            }
            else
            {
                return "";
            }
        },
        /**
         * TODO Returns a list of events?
         *
         * @method getEvents
         * @param {object} d Object with data above the requested resource
         * @return {string} * TODO: Was always empty
         */
        getEvents: function (d)
        {
            var ret = "";
            ret = MySched.eventlist.getEventsForLecture(this, d.block, d.dow);
            return ret;
        },
        /**
         * Returns the dom Element as String with icon
         *
         * @method getTopIcon
         * @param {object} d Object with data above the requested resource
         * @return {string} * HTML code
         */
        getTopIcon: function (d)
        {
            if (isset(this.data.lessonChanges) && this.data.lessonChanges.status === "new")
            {
                return '<div data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_IS_NEW + '" class="top_icon_image">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NEW + '</div>';
            }

            if (isset(this.data.periodChanges))
            {
                if (this.data.periodChanges.status === "new")
                {
                    return '<div data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_IS_NEW + '" class="top_icon_image">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_NEW + '</div>';
                }
                else if (this.data.periodChanges.status === "moved")
                {
                    return '<div data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_MOVED_DESC + '" class="top_icon_image">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_MOVED + '</div>';
                }
            }

            if (this.data.css === "mysched_proposal")
            {
                return '<div data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_PROPOSAL_DESC + '" class="top_icon_image">' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_PROPOSAL + '</div><br/>';
            }

            return "";
        },
        /**
         * Returns the status as Dom element with the corresponding image
         *
         * @method getStatus
         * @param {object} d Object with data above the requested resource
         * @return {string} * HTML code with image
         */
        getStatus: function (d)
        {
            var ret = '<div class="status_icons">';

            if (Ext.isDefined(this.data.ecollaborationLink))
            {
                ret += '<a target="_blank" href="' + this.data.ecollaborationLink + '"><img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MOODLE_CALL + '" class="status_icons_ecollabLink" src="' + MySched.mainPath + 'images/collab.png" width="12" heigth="12"/></a>';
            }

            if (Ext.isDefined(MySched.Authorize.user) && MySched.Authorize.user !== "" && typeof d.parentId !== "undefined")
            {
                var parentIDArr = d.parentId.split(".");
                parentIDArr = parentIDArr[(parentIDArr.length - 1)];
                if (parentIDArr !== "delta")
                {
                    if (d.parentId === "mySchedule")
                    {
                        ret += '<img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE_LESSON_REMOVE + '" class="status_icons_add" src="' + MySched.mainPath + '/images/delete.png" width="12" heigth="12"/>';
                    }
                    else if (d.parentId !== "mySchedule" && MySched.Schedule.lectureExists(this))
                    {
                        ret += '<img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE_LESSON_REMOVE + '" class="status_icons_add" src="' + MySched.mainPath + '/images/delete.png" width="12" heigth="12"/>';
                    }
                    else
                    {
                        ret += '<img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE_LESSON_ADD + '" class="status_icons_add" src="' + MySched.mainPath + '/images/add.png" width="12" heigth="12"/>';
                    }
                }
            }

            if ((d.owner === MySched.Authorize.user || (MySched.Authorize.user === MySched.modules_semester_author && d.type === "personal")) && Ext.isDefined(MySched.Authorize.user) && MySched.Authorize.user !== "")
            {
                ret += '<img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_CHANGE + '" class="status_icons_edit" src="' + MySched.mainPath + 'images/icon-edit.png" width="12" heigth="12"/>';
                ret += '<img data-qtip="' + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_DELETE + '" class="status_icons_delete" src="' + MySched.mainPath + 'images/icon-delete.png" width="12" heigth="12"/>';
            }
            return ret + '</div>';
        },
        /**
         * TODO: Maybe get changes of the resources
         *
         * @method getChanges
         * @param {object} lec Object with data above the requested resource
         * @return {string} r+t+c TODO Was always empty so I don't know what it is
         */
        getChanges: function (lec)
        {
            var r = "", t = "", c = "", l, temp;

            if (lec && lec.changes)
            {
                if (lec.changes.rooms)
                {
                    var rooms = lec.changes.rooms;
                    r += "<span>" + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ROOMS + ":<br/>";
                    for (var room in rooms)
                    {
                        if (rooms.hasOwnProperty(room) && room !== "")
                        {
                            temp = MySched.Mapping.getObject("room", room);
                            if (!temp)
                            {
                                r += '<small class="' + rooms[room] + '"> ' + room + ' </small>, ';
                            }
                            else
                            {
                                r += '<small class="' + rooms[room] + '"> ' + temp.name + ' </small>, ';
                            }
                            r += "<br/>";
                        }
                    }
                    if (r !== "")
                    {
                        l = r.length - 2;
                        r = r.substr(0, l);
                    }
                    r += "</span><br/>";
                }
                if (lec.changes.teachers)
                {
                    var teachers = lec.changes.teachers;
                    t += "<span>" + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_TEACHERS + ":<br/>";
                    for (var teacher in teachers)
                    {
                        if (teachers.hasOwnProperty(teacher) && teacher !== "")
                        {
                            temp = MySched.Mapping.getObject("teacher", teacher);
                            if (!temp)
                            {
                                t += '<small class="' + teachers[teacher] + '"> ' + teacher + ' </small>, ';
                            }
                            else
                            {
                                t += '<small class="' + teachers[teacher] + '"> ' + temp.name + ' </small>, ';
                            }
                            t += "<br/>";
                        }
                    }
                    if (t !== "")
                    {
                        l = t.length - 2;
                        t = t.substr(0, l);
                    }
                    t += "</span><br/>";
                }
                if (lec.changes.pools)
                {
                    var pools = lec.changes.pools;
                    c += "<span>" + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_POOL + ":<br/>";
                    for (var pool in pools)
                    {
                        if (pools.hasOwnProperty(pool)  && pool !== "")
                        {
                            temp = MySched.Mapping.getObject("pool", pool);
                            if (!temp)
                            {
                                c += '<small class="' + pools[pool] + '"> ' + pool + ' </small>, ';
                            }
                            else
                            {
                                c += '<small class="' + pools[pool] + '"> ' + temp.department + " - " + temp.name + ' </small>, ';
                            }
                            c += "<br/>";
                        }
                    }
                    if (c !== "")
                    {
                        l = c.length - 2;
                        c = c.substr(0, l);
                    }
                    c += "</span><br/>";
                }
            }
            return r + t + c;
        },
        /**
         * TODO: Maybe obsolete, it seems to be never used
         *
         * @method loadTeacher
         * @param arr
         */
        loadTeacher: function (arr)
        {
            console.log("lectureModel loadTeacher: maybe never used?");
            if (arr)
            {
                var myteacher = arr.split(" ");
                Ext.each(myteacher, function (e)
                {
                    var nteacher = new TeacherModel(e);
                    this.teacher.add(nteacher);
                }, this);
            }
        },
        /**
         * TODO: Maybe obsolete, it seems to be never used
         *
         * @method loadRoom
         * @param arr
         */
        loadRoom: function (arr)
        {
            console.log("lectureModel loadRoom: maybe never used?");
            if (arr)
            {
                var myroom = arr.split(" ");
                Ext.each(myroom, function (e) { this.room.add(new RoomModel(e)); }, this);
            }
        },
        /**
         * TODO: Maybe obsolete, it seems to be never used
         *
         * @method loadSubject
         * @param arr
         */
        loadSubject: function (arr)
        {
            console.log("lectureModel loadSubject: maybe never used?");
            if (arr)
            {
                var mySubject = arr.split(" ");
                Ext.each(mySubject, function (e) { this.subject.add(new SubjectModel(e)); }, this);
            }
        },
        /**
         * TODO: Maybe obsolete, it seems to be never used
         *
         * @method loadPool
         * @param arr
         */
        loadPool: function (arr)
        {
            console.log("lectureModel loadPool: maybe never used?");
            if (arr)
            {
                var myPool = arr.split(" ");
                Ext.each(myPool, function (e) { this.pool.add(new PoolModel(e)); }, this);
            }
        },
        /**
         * Returns all data for a given resource
         *
         * @method getData
         * @param {object} addData Object with data above the requested resource
         * @return {object} * Object with all data above the resource
         */
        getData: function (addData)
        {
            if (!this.data.name)
            {
                this.data.name = this.getName();
            }
            if (!this.data.desc)
            {
                this.data.desc = this.getDesc();
            }
            return LectureModel.superclass.getData.call(this, addData);
        },
        /**
         * Returns the room name within HTML code
         *
         * @method getRoomName
         * @param d Object with data above the requested resource
         * @return {string} * Room name in HTML code
         */
        getRoomName: function (d)
        {
            var rooms = this.getRooms(this);
            var ret = [];
            var removed = [];
            var changedStatus = "";

            for (var roomIndex in rooms.map)
            {
                if (rooms.map.hasOwnProperty(roomIndex))
                {
                    var roomName = MySched.Mapping.getRoomName(roomIndex);

                    if (d.showDelta === true && rooms.map[roomIndex] !== "")
                    {
                        changedStatus = "room"+rooms.map[roomIndex];
                    }
                    else if (rooms.map[roomIndex] !== "" && rooms.map[roomIndex] !== "new")
                    {
                        continue;
                    }

                    var roomNameHTML = '<small roomID="' + roomIndex +  '" class="roomname ' + changedStatus + '">' + roomName + '</small>';
                    ret.push(roomNameHTML);
                }
            }
            return ret.join(', ') + " " + removed.join(', ');
        },
        /**
         * The method gets a lesson object and returns a collection of rooms of the given lesson.
         *
         * @method getRooms
         * @param {object} lesson Information above the lesson
         * @return {MySched.Collection} roomCollection Collection of the room
         */
        getRooms: function(lesson)
        {
            var roomCollection = new MySched.Collection();
            var currentMoFrDate = getCurrentMoFrDate();
            for(var dateIndex in lesson.data.calendar)
            {
                if (lesson.data.calendar.hasOwnProperty(dateIndex))
                {
                    var dateObject = convertEnglishDateStringToDateObject(dateIndex);
                    if (dateObject >= currentMoFrDate.monday && dateObject <= currentMoFrDate.friday)
                    {
                        roomCollection.addAll(lesson.data.calendar[dateIndex][lesson.data.block].lessonData);
                    }
                }
            }

            roomCollection.removeAtKey("delta");
            return roomCollection;
        },
        /**
         * Returns the teacher of a given resource within in HTML element
         *
         * @method getTeacherNames
         * @param {object} d Object with data above the requested resource
         * @return {string} * Teacher name in a HTML element
         */
        getTeacherNames: function (d)
        {
            var teachers = this.data.teachers.map;
            var ret = [];
            var removed = [];
            var changedStatus = "";

            for (var teacherIndex in teachers)
            {
                if (teachers.hasOwnProperty(teacherIndex))
                {
                    var teacherName = getTeacherSurnameWithCutFirstName(teacherIndex);

                    if (d.showDelta === true && teachers[teacherIndex] !== "")
                    {
                        changedStatus = "teacher" + teachers[teacherIndex];
                    }
                    else if (teachers[teacherIndex] !== "" && teachers[teacherIndex] !== "new")
                    {
                        continue;
                    }

                    var teacherNameHTML = '<small teacherID="' + teacherIndex +  '" class="teachername ' +  changedStatus + '">' + teacherName + '</small>';
                    ret.push(teacherNameHTML);
                }
            }
            return ret.join(', ') + " " + removed.join(', ');
        },
        /**
         * TODO: maybe obsolete, seems to be never called
         *
         * @method getNames
         * @param col
         * @param shortVersion
         * @return {string}
         */
        getNames: function (col, shortVersion)
        {
            console.log("LectureModel.getNames: maybe never used?");
            console.log(col);
            console.log(shortVersion);
            var ret = [];
            col.each(function (e)
            {
                // shortcut instead of complete written
                var temproom = "";
                if (shortVersion)
                {
                    temproom = e.getId();
                }
                else
                {
                    temproom = e.getName();
                }
                this.push(temproom);
            }, ret);
            // Short version without blank
            if (shortVersion)
            {
                return ret.join(',');
            }
            return ret.join(', ');
        },
        /**
         * TODO: maybe obsolete, seems to be never called
         *
         * @method getPoolFull
         * @param col
         * @return {string}
         */
        getPoolFull: function (col)
        {
            console.log("LectureModel.getPoolFull: maybe never used?");
            console.log(col);
            var ret = [];
            col.each(function (e)
            {
                // Abkuerzung anstatt Ausgeschrieben
                var temproom = e.getFullName();
                this.push(temproom);
            }, ret);
            // Bei der kurzen Varianten ohne BLANK
            return ret.join(',<br/>');
        },
        /**
         * Returns the pool name for the requested resource
         *
         * @method getPoolName
         * @param d Object with data above the requested resource
         * @return {string} * Name(s) of the Pool
         */
        getPoolName: function (d)
        {
            var pools = this.data.pools.map, ret = [], changedStatus = "", poolName, HTML;

            for (var poolIndex in pools)
            {
                if (pools.hasOwnProperty(poolIndex))
                {
                    if (pools[poolIndex] !== "" && pools[poolIndex] !== "new")
                    {
                        continue;
                    }

                    poolName = MySched.Mapping.getPoolName(poolIndex);
                    if (d.showDelta === true && pools[poolIndex] !== "")
                    {
                        changedStatus = "module" + pools[poolIndex];
                    }

                    HTML = '<small moduleID="' + poolIndex +  '" class="modulename ' + changedStatus + '">' + poolName + '</small>';
                    ret.push(HTML);
                }
            }
            return ret.join(', ');
        },
        /**
         * Returns lecture name
         *
         * @method getName
         * @return {String} * LectureName
         */
        getName: function ()
        {
            return MySched.Mapping.getLectureName(this.data.id);
        },
        /**
         * Returns description of lecture
         *
         * @method getDesc
         * @return {string} * Description of the lecture
         */
        getDesc: function ()
        {
            return MySched.Mapping.getLectureDescription(this.data.id);
        },
        /**
         * TODO: maybe obsolete, seems to be never called
         *
         * @method getTeacher
         * @return {*}
         */
        getTeacher: function ()
        {
            console.log("LectureModel.getTeacher: maybe never used?");
            return this.teacher;
        },
        /**
         * TODO: maybe obsolete, seems to be never called
         *
         * @method getPool
         * @return {*}
         */
        getPool: function ()
        {
            console.log("LectureModel.getPool: maybe never used?");
            return this.pool;
        },
        /**
         * TODO: maybe obsolete, seems to be never called
         *
         * @method getRoom
         * @return {*}
         */
        getRoom: function ()
        {
            console.log("LectureModel.getRoom: maybe never used?");
            return this.room;
        },
        /**
         * Returns the weekday
         *
         * @method getWeekDay
         * @return {string} * Weekday
         */
        getWeekDay: function ()
        {
            return this.data.dow;
        },
        /**
         * Returns the block
         *
         * @method getBlock
         * @return {number} * block
         */
        getBlock: function ()
        {
            return this.data.block;
        },
        /**
         * Returns the description
         *
         * @method getDescription
         * @return {string} * Description
         */
        getDescription: function ()
        {
            if (!Ext.isEmpty(this.data.description) && Ext.isString(this.data.description))
            {
                return "-" + this.data.description;
            }
            else
            {
                return "";
            }
        },
        /**
         * Creates a HTML template according to the given resource
         *
         * @method setCellTemplate
         * @param {string} t Resource type (e.g. room, pool)
         */
        setCellTemplate: function (t, scheduleGrid)
        {
            var time = "";

            if (scheduleGrid !== this.data.grid)
            {
                var blocktimes = blocktotime(this.data.block, this.data.grid);
                time = "<br/>(" + blocktimes[0] + "-" + blocktimes[1] + ")";
            }

            if (Ext.isObject(MySched.selectedSchedule))
            {
                if (!Ext.isString(t))
                {
                    t = MySched.selectedSchedule.type;
                }
            }

            if (t === "room")
            {
                this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" block="{lessonBlock}" dow="{lessonDow}" class="{css} {deltaStatus} scheduleBox lectureBox">' + '<b class="lecturename">{lessonTitle}{description}</b> {moduleNr} {comment}<br/>{teacherName} / {poolName} {lessonEvents}' + time + '{statusIcons}</div>');
            }
            else if (t === "teacher")
            {
                this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" block="{lessonBlock}" dow="{lessonDow}" class="{css} {deltaStatus} scheduleBox lectureBox">' + '<b class="lecturename">{lessonTitle}{description}</b> {moduleNr} {comment}<br/>{poolName} / {roomName} {lessonEvents}' + time + '{statusIcons}</div>');
            }
            else if (t === "subject")
            {
                this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" block="{lessonBlock}" dow="{lessonDow}" class="{css} {deltaStatus} scheduleBox lectureBox">' + '<b class="lecturename">{lessonTitle}{description}</b> {moduleNr} {comment}<br/>{poolName} / {teacherName} / {roomName} {lessonEvents}' + time + '{statusIcons}</div>');
            }
            else
            {
                var poolCSS = "scheduleBox", lectureCSS = "", lessonRemoved, blockRemoved;

                lessonRemoved = isset(this.data.lessonChanges) && this.data.lessonChanges.status === "removed";
                blockRemoved = isset(this.data.periodChanges) && this.data.periodChanges.status === "removed";
                if (lessonRemoved || blockRemoved)
                {
                    poolCSS += " lectureBox_dis";
                    lectureCSS = "lecturename_dis";
                }
                else
                {
                    poolCSS += " lectureBox";
                    lectureCSS = "lecturename";
                }

                this.cellTemplate = new Ext.Template('<div id="{parentId}##{key}" style="{curriculumColor}" block="{lessonBlock}" dow="{lessonDow}" class="{css} {deltaStatus} ' + poolCSS + '">' + '{topIcon}<b class="' + lectureCSS + '">{lessonTitle}{description} </b> {moduleNr} {comment}<br/>{teacherName} / {roomName} {lessonEvents}' + time + '{statusIcons}</div>');
            }
        },
        /**
         * TODO: maybe obsolete, seems to be never called
         *
         * @method setInfoTemplate
         * @param t
         */
        setInfoTemplate: function (t)
        {
            console.log("LectureModel.setInfoTemplate: maybe never used?");
            this.infoTemplate.set(t, true);
        },
        /**
         * Returns the HTML code for element which is displayed in the schedule
         *
         * @method getCellView
         * @param {object} relObj Object with a lot of different information above the schedule
         * @param {number} block Number of lesson block
         * @param {string} dow Weekday
         * @return {*}
         */
        getCellView: function (relObj, block, dow)
        {
            var showDelta = displayDelta();

            var d = this.getDetailData(
                {
                    parentId: relObj.getId(),
                    key: this.id,
                    showDelta: showDelta,
                    block: block,
                    dow: dow
                }
            );
            if (relObj.getId() !== 'mySchedule' && MySched.Schedule.lectureExists(this))
            {
                d.css = ' lectureBox_cho';
            }
            var cellView =  this.cellTemplate.apply(d);

            if (cellView.contains("MySchedEvent_reserve"))
            {
                cellView = cellView.replace("lectureBox", "lectureBox lectureBox_reserve");
            }
            return cellView;
        },
        /**
         * TODO: maybe obsolete, seems to be never called
         *
         * @method getSporadicView
         * @param relObj
         * @return {*}
         */
        getSporadicView: function (relObj)
        {
            console.log("LectureModel.getSporadicView: maybe never used?");
            var d = this.getDetailData({ parentId: relObj.getId() });
            if (relObj.getId() !== 'mySchedule' && MySched.Schedule.lectureExists(this))
            {
                d.css = ' lectureBox_cho';
            }
            return this.sporadicTemplate.apply(d);
        },
        /**
         * Returns the HTML code of the info panel
         *
         * @method showInfoPanel
         * @return {string} * Info Panel
         */
        showInfoPanel: function ()
        {
            return this.infoTemplate.apply(this.getDetailData(this));
        },
        /**
         * Returns true or false if the given resource has the given value
         *
         * @method has
         * @param {string} type Resource type like room or pool
         * @param {String} val The value of the resource
         * @return {boolean} * True if the resource has the value otherwise false
         */
        has: function (type, val)
        {
            var o = { ret: false };

            if (type === "teacher")
            {
                type = "teachers";
            }
            else if (type === "room")
            {
                type = "rooms";
            }
            else if (type === "pool")
            {
                type = "pools";
            }
            else if (type === "subject")
            {
                type = "subjects";
            }
            if (type === "rooms")
            {
                for (var calendarIndex in this.data.calendar)
                {
                    if (this.data.calendar.hasOwnProperty(calendarIndex))
                    {
                        var blocks = this.data.calendar[calendarIndex];
                        for (var blockIndex in blocks)
                        {
                            if (blocks.hasOwnProperty(blockIndex))
                            {
                                for (var roomIndex in blocks[blockIndex].lessonData)
                                {
                                    if (blocks[blockIndex].lessonData.hasOwnProperty(roomIndex) && roomIndex === val)
                                    {
                                        o.ret = true;
                                        return o.ret;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else if (this.data[type].containsKey(val))
            {
                o.ret = true;
                return o.ret;
            }

            return o.ret;
        },
        /**
         * TODO: maybe obsolete, seems to be never called
         *
         * @method isSporadic
         * @return {boolean}
         */
        isSporadic: function ()
        {
            console.log("LectureModel.isSporadic: maybe never used?");
            return this.data.type === 'sporadic';
        }
    }
);
