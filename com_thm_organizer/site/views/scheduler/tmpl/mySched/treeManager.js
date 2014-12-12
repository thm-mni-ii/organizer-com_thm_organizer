/**
 * It manages and creates the overview lists
 *
 * @class TreeManager
 */
// TODO: I think this class is obsolete and not in use anymore, so I stopped commenting
MySched.TreeManager = function ()
{
    "use strict";

    var teacherTree, roomTree, clasTree, curteaTree; // neu

    return {
        /**
         * 	Initialization
         *
         * 	@method init
         */
        init: function ()
        {
            this.teacherTree = new MySched.Collection();
            this.roomTree = new MySched.Collection();
            this.clasTree = new MySched.Collection();
            this.curteaTree = new MySched.Collection(); // new
        },
        /**
         * Add the received events to the eventlist
         *
         * @method afterloadEvents
         * @param eventList {Object} List of alle events
         */
        // TODO: I think it is never used and obsolete
        afterloadEvents: function (eventList)
        {
            console.log("TreeManager.afterloadEvents: maybe never used?");
            for (var e in eventList)
            {
                if (Ext.isObject(eventList[e]))
                {
                    var event = new EventModel(eventList[e].eid, eventList[e]);
                    MySched.eventlist.addEvent(event);
                }
            }
        },
        /**
         * Adds a lecture to the events
         *
         * @param {Object} lecture Lecture
         */
        // TODO: I think it is never used and obsolete
        add: function (lecture)
        {
            console.log("TreeManager.add: maybe never used?");
            if (Ext.isObject(lecture))
            {
                this.teacherTree.addAll(lecture.getTeacher().asArray());
                this.roomTree.addAll(lecture.getRoom().asArray());
                this.clasTree.addAll(lecture.getClas().asArray());
            }
        },
        /**
         * Erstellt die Teacher Uebersichtsliste
         *
         * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
         */
        // TODO: I think it is never used and obsolete
        createTeacherTree: function (tree)
        {
            return this.createTree(
                tree, 'teacher',
                this.teacherTree,
                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_TEACHER);
        },
        /**
         * Erstellt die Raum Uebersichtsliste
         *
         * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
         */
        // TODO: I think it is never used and obsolete
        createRoomTree: function (tree)
        {
            return this.createTree(tree, 'room', this.roomTree,
                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_ROOM);
        },
        /**
         * Erstellt die Studiengang Uebersichtsliste
         *
         * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
         */
        // TODO: I think it is never used and obsolete
        createPoolTree: function (tree)
        {
            return this.createTree(tree, 'pool',this.poolTree,
                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_SEMESTER);
        },
        /**
         * Erstellt die Änderungen
         *
         * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
         */
        // TODO: I think it is never used and obsolete
        createDiffTree: function (tree)
        {
            return this.createTree(tree, 'diff');
        },
        /**
         * Erstellt die Änderungen von Verantwortlichen
         *
         * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
         */
        // TODO: I think it is never used and obsolete
        createrespChangesTree: function (tree)
        {
            return this.createTree(tree, 'respChanges');
        },
        /**
         * Sucht alle Lessons
         *
         * @param {Object} tree Basis Tree dem die Liste hinzugefuegt wird
         */
        // TODO: I think it is never used and obsolete
        createCurteaTree: function (tree)
        { // neu->
            return this.createTree(
                tree, 'curtea',
                this.curteaTree,
                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_CURRICULUM); // UnsetTimes
        },
        /**
         *
         *
         * @param json
         * @param type
         * @param accMode
         * @param name
         * @param baseTree
         */
        processTreeData: function (json, type, accMode, name, baseTree)
        {
            var treeData = json.treeData;
            console.log(processTreeData);
            console.log(json, type, accMode, name, baseTree);

            /*
             * if (accMode !== 'none') { treeRoot.appendChild(children); }
             */

            for (var item in treeData)
            {
                if (Ext.isObject(treeData[item]))
                {
                    for (var childitem in treeData[item])
                    {
                        if (Ext.isObject(treeData[item][childitem]))
                        {
                            MySched.Mapping[item].add(
                                childitem,
                                treeData[item][childitem]);
                        }
                    }
                }
            }
        },
        /**
         * Erstellt eine Uebersichtsliste
         *
         * @param {Object} baseTree Baum dem die Liste hinzugefuegt wird
         * @param {Object} type Typ der Liste (teacher|pool|room)
         * @param {Object} data Daten Baum mit Elementen zum Hinzufuegen
         * @param {Object} name Name der Listengruppe
         */
        createTree: function (baseTree, type, data, name)
        {
            console.log("createTree");
            // Generelle Rechteuberpruefung auf diese Uebersichtsliste
            var accMode = MySched.Authorize.checkAccessMode(type);

            if (type !== "diff" && type !== "respChanges" && type !== "curtea")
            {
                if (checkStartup("TreeView.load") === true)
                {
                    MySched.TreeManager.processTreeData(
                        MySched.startup["TreeView.load"].data, type,
                        accMode, name, baseTree);
                }
                else
                {
                    Ext.Ajax.request(
                        {
                            url: _C('ajaxHandler'),
                            method: 'GET',
                            params: {
                                type: type,
                                semesterID: MySched.class_semester_id,
                                scheduletask: "TreeView.load"
                            },
                            failure: function (response)
                            {
                                var bla = response;
                            },
                            success: function (response)
                            {
                                try
                                {
                                    var json = Ext.decode(response.responseText);
                                    MySched.TreeManager.processTreeData(json, type,
                                        accMode, name, baseTree);
                                }
                                catch (e)
                                {}
                            }
                        });
                }
            }

            if (type === "curtea")
            { // neu->
                MySched.TreeManager.processTreeData(
                    MySched.startup["TreeView.curiculumTeachers"].data,
                    type, accMode, name, baseTree);
                return ret;
            }

            // Keine Rechte, also nicht anzeigen
            if (accMode === 'none')
            {
                return null;
            }

            var ret;
            if (type === "diff")
            {
                // Fuegt die Liste der Uebersicht an
                ret = baseTree.root.appendChild(
                    {
                        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_CENTRAL,
                        id: 'delta',
                        cls: type + '-root',
                        draggable: false,
                        leaf: true
                    });
                return ret;
            }

            if (type === "respChanges")
            {
                // Fuegt die Liste der Uebersicht an
                ret = baseTree.root.appendChild(
                    {
                        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_OWN,
                        id: 'respChanges',
                        cls: type + '-root',
                        draggable: false,
                        leaf: true
                    });
                return ret;
            }
        }
    };
}();
