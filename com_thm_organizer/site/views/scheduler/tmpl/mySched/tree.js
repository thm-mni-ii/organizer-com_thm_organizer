/**
 * tree object for schedule lists
 *
 * @class MySched.Tree
 * @constructor
 */
MySched.Tree = function ()
{
    var tree, teacher, room, pool, diff, dragNode, respChanges, curtea;

    return {
        /**
         * Gets the data for rooms, modules, teacher and semester and creates the tree from it
         *
         * @method init
         * @return {Ext.tree.Panel} * The tree as DOM element
         */
        init: function ()
        {
            var children = [];
            if(Ext.isObject(MySched.startup["TreeView.load"]))
            {
                children = MySched.startup["TreeView.load"].data.tree;
            }
            else
            {
                Ext.Ajax.request(
                    {
                        url: _C('ajaxHandler'),
                        method: 'GET',
                        params: {
                            scheduletask: "TreeView.load",
                            departmentSemesterSelection: MySched.departmentAndSemester,
                            Itemid: MySched.joomlaItemid
                        },
                        success: function (response)
                        {
                            var json = Ext.decode(response.responseText);
                            var newtree = json.tree;
                            var treeData = json.treeData;

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

                            MySched.scheduleDataReady = true;

                            var rootNode = MySched.Tree.tree.getRootNode();
                            rootNode.removeAll(true);
                            rootNode.appendChild(newtree);

                            if (MySched.treeLoadMask)
                            {
                                MySched.treeLoadMask.destroy();
                            }

                            var publicDefaultNode = json.treePublicDefault;

                            var nodeID = null;
                            var nodeKey = null;
                            var gpuntisID = null;
                            var plantypeID = null;
                            var semesterID = null;
                            var type = null;

                            if(Ext.isObject(publicDefaultNode) && publicDefaultNode.type !== "delta")
                            {
                                nodeID = publicDefaultNode.id;
                                nodeKey = publicDefaultNode.nodeKey;
                                gpuntisID = publicDefaultNode.gpuntisID;
                                plantypeID = publicDefaultNode.plantype;
                                semesterID = publicDefaultNode.semesterID;
                                type = publicDefaultNode.type;

                                MySched.Tree.showScheduleTab(nodeID, nodeKey,
                                    gpuntisID, semesterID, plantypeID, type);
                            }

                            // Displays teacher schedules given by get parameter
                            if(MySched.requestTeacherIDs.length > 0)
                            {
                                semesterID = MySched.class_semester_id;
                                plantypeID = null;
                                type = "teacher";
                                for(var teacherIndex = 0; teacherIndex < MySched.requestTeacherIDs.length; teacherIndex++)
                                {
                                    var teacherGPUntisID = MySched.requestTeacherIDs[teacherIndex];
                                    if(!Ext.isDefined(MySched.Mapping[type].map[teacherGPUntisID]))
                                    {
                                        continue;
                                    }
                                    nodeID = teacherGPUntisID;
                                    nodeKey = teacherGPUntisID;
                                    gpuntisID = teacherGPUntisID;

                                    MySched.Tree.showScheduleTab(nodeID, nodeKey, gpuntisID, semesterID, plantypeID, type);
                                }
                            }

                            // Displays room schedules given by get parameter
                            if(MySched.requestRoomIDs.length > 0)
                            {
                                semesterID = MySched.class_semester_id;
                                plantypeID = null;
                                type = "room";
                                for(var roomIndex = 0; roomIndex < MySched.requestRoomIDs.length; roomIndex++)
                                {
                                    var roomGPUntisID = MySched.requestRoomIDs[roomIndex];

                                    if(!Ext.isDefined(MySched.Mapping[type].map[roomGPUntisID]))
                                    {
                                        continue;
                                    }
                                    nodeID = roomGPUntisID;
                                    nodeKey = roomGPUntisID;
                                    gpuntisID = roomGPUntisID;

                                    MySched.Tree.showScheduleTab(nodeID, nodeKey, gpuntisID, semesterID, plantypeID, type);
                                }
                            }

                            // Displays pool/group schedules given by get parameter
                            if(MySched.requestPoolIDs.length > 0)
                            {
                                semesterID = MySched.class_semester_id;
                                plantypeID = null;
                                type = "pool";
                                for(var poolIndex = 0; poolIndex < MySched.requestPoolIDs.length; poolIndex++)
                                {
                                    var poolGPUntisID = MySched.requestPoolIDs[poolIndex];

                                    if(!Ext.isDefined(MySched.Mapping[type].map[poolGPUntisID]))
                                    {
                                        continue;
                                    }
                                    nodeID = poolGPUntisID;
                                    nodeKey = poolGPUntisID;
                                    gpuntisID = poolGPUntisID;

                                    MySched.Tree.showScheduleTab(nodeID, nodeKey, gpuntisID, semesterID, plantypeID, type);
                                }
                            }

                            // Displays subject schedules given by get parameter
                            if(MySched.requestSubjectIDs.length > 0)
                            {
                                semesterID = MySched.class_semester_id;
                                plantypeID = null;
                                type = "subject";
                                for(var subjectIndex = 0; subjectIndex < MySched.requestSubjectIDs.length; subjectIndex++)
                                {
                                    var subjectGPUntisID = MySched.requestSubjectIDs[subjectIndex];

                                    if(!Ext.isDefined(MySched.Mapping[type].map[subjectGPUntisID]))
                                    {
                                        continue;
                                    }
                                    nodeID = subjectGPUntisID;
                                    nodeKey = subjectGPUntisID;
                                    gpuntisID = subjectGPUntisID;

                                    MySched.Tree.showScheduleTab(nodeID, nodeKey, gpuntisID, semesterID, plantypeID, type);
                                }
                            }

                            if(MySched.selectedSchedule)
                            {
                                MySched.selectedSchedule.refreshView();
                            }
                        }
                    }
                );
            }

            var treeStore = Ext.create(
                'Ext.data.TreeStore',
                {
                    folderSort: true,
                    sorters: [{ property: 'text', direction: 'ASC' }],
                    root: {
                        id: 'rootTreeNode',
                        text: 'root',
                        expanded: true,
                        children: children
                    }
                }
            );

            var hideTreePanel = false;
            if(MySched.schedulerFromMenu === false)
            {
                hideTreePanel = true;
            }

            this.tree = Ext.create('Ext.tree.Panel',
                {
                    title: 'Stand vom',
                    singleExpand: false,
                    id: 'selectTree',
                    region: 'west',
                    width: 242,
                    hidden: hideTreePanel,
                    minSize: 242,
                    maxSize: 242,
                    height: 470,
                    rootVisible: false,
                    scroll: false,
                    bodyCls: 'MySched_SelectTree',
                    viewConfig: {
                        plugins: {
                            ptype: 'treeviewdragdrop',
                            ddGroup: 'lecture',
                            enableDrop: false,
                            enableDrag: true
                        },
                        scroll: 'both',
                        autoScroll: true
                    },
                    layout: {
                        type: 'fit'
                    },
                    store: treeStore
                });

            // Opens the scheule on click
            this.tree.on(
                'itemclick', function (me, rec, item, index, event, options)
                {
                    if (rec.isLeaf())
                    {
                        var title = "", data;
                        if (rec.raw)
                        {
                            data = rec.raw;
                        }
                        else
                        {
                            data = rec.data;
                        }

                        var nodeID = data.id;
                        var nodeKey = data.nodeKey;
                        var gpuntisID = data.gpuntisID;
                        var semesterID = data.semesterID;
                        var plantypeID = data.plantype;
                        var type = data.type;

                        MySched.Tree.showScheduleTab(nodeID, nodeKey, gpuntisID, semesterID, plantypeID, type);
                    }
                    else if (rec.isExpanded())
                    {
                        rec.collapse();
                    }
                    else
                    {
                        rec.expand();
                    }
                }
            );
            return this.tree;
        },
        /**
         * Get the data from the server to the according schedule and shows it
         *
         * @method showScheduleTab
         * @param {string} nodeID The id of the resource
         * @param {string} nodeKey The nodekey of the resource
         * @param {string} gpuntisID The gpunits id
         * @param {string} semesterID The id of the semester
         * @param {string} plantypeID TODO Was always undefined at tests
         * @param {string} type The type of the ressource (e.g. teacher or room)
         */
        showScheduleTab: function (nodeID, nodeKey, gpuntisID, semesterID, plantypeID, type)
        {
            var title, config = {};
            if(nodeID === null)
            {
                nodeID = nodeKey;
            }

            if (type === null)
            {
                type = gpuntisID;
            }
            var department = null;
            if (type === "delta")
            {
                title = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_CENTRAL;
            }
            else if (type === "respChanges")
            {
                title = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DELTA_OWN;
            }
            else
            {
                var departmentType = "field", departmentField = "description", nodeFullName = nodeKey;
                var grid = "Haupt-Zeitraster";
                if (type === "teacher")
                {
                    nodeFullName = getTeacherSurnameWithCutFirstName(nodeKey);
                }
                else if (type === "room")
                {
                    nodeFullName = MySched.Mapping.getRoomName(nodeKey);
                    departmentType = "roomtype";
                }
                else if (type === "pool")
                {
                    nodeFullName = MySched.Mapping.getPoolFullName(nodeKey);
                    grid = MySched.Mapping.getGrid(nodeKey);
                    departmentType = "degree";
                    departmentField = "degree";
                }
                else if (type === "subject")
                {
                    nodeFullName = MySched.Mapping.getSubjectName(nodeKey);
                }

                department = MySched.Mapping.getObjectField(type, nodeKey, departmentField);
                var departmentName = MySched.Mapping.getObjectField(departmentType, department, "name");
                if (typeof department === "undefined" || department === "none" || department === null || department === departmentName)
                {
                    title = nodeFullName;
                }
                else
                {
                    title = nodeFullName + " - " + departmentName;
                }
            }

            config.grid = grid;

            if (type === "delta")
            {
                new ScheduleModel(nodeID, title, config).init(type, nodeKey).show();
            }
            else
            {
                if (MySched.loadLessonsOnStartUp === false)
                {
                    var weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker')
                        .value);
                    var currentMoFrDate = getCurrentMoFrDate();

                    Ext.Ajax.request(
                        {
                            url: _C('ajaxHandler'),
                            method: 'POST',
                            params: {
                                nodeKey: nodeKey,
                                gpuntisID: gpuntisID,
                                semesterID: semesterID,
                                scheduletask: "Ressource.load",
                                type: type,
                                startdate: Ext.Date.format(currentMoFrDate.monday, "Y-m-d"),
                                enddate: Ext.Date.format(currentMoFrDate.friday, "Y-m-d")
                            },
                            failure: function (response)
                            {
                                Ext.Msg.alert(
                                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR,
                                    MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_ERROR);
                            },
                            success: function (response)
                            {
                                var json = Ext.decode(response.responseText);
                                var lessonData = json.lessonData;
                                var lessonDate = json.lessonDate;
                                for (var item in lessonData)
                                {
                                    if (Ext.isObject(lessonData[item]))
                                    {
                                        var record = new LectureModel(item, lessonData[item], semesterID, plantypeID);
                                        MySched.Base.schedule.addLecture(record);
                                    }
                                }
                                if (Ext.isObject(lessonDate))
                                {
                                    MySched.Calendar.addAll(lessonDate);
                                }

                                new ScheduleModel(nodeID, title, config).init(type, nodeKey, semesterID).show();
                            }
                        }
                    );
                }
                else
                {
                    new ScheduleModel(nodeID, title, config) .init(type, nodeKey, semesterID) .show();
                }
            }
        },
        /**
         * Sets title of the list fields
         *
         * @param {string} title Title of the schedule
         * @param {boolean} append Append title to Tree title or not
         */
        setTitle: function (title, append)
        {
            if (append === true)
            {
                this.tree.setTitle(this.tree.title + title);
            }
            else
            {
                this.tree.setTitle(title);
            }
        },
        /**
         * Refresht die Daten der Liste
         * TODO: Is it in use anymore?
         */
        refreshTreeData: function ()
        {
            console.log("tree.js refreshTreeData: is it in use anymore");
            if (this.teacher)
            {
                this.root.removeChild(this.teacher);
            }
            if (this.room)
            {
                this.root.removeChild(this.room);
            }
            if (this.pool)
            {
                this.root.removeChild(this.pool);
            }
            if (this.diff)
            {
                this.root.removeChild(this.diff);
            }
            if (this.respChanges)
            {
                this.root.removeChild(this.respChanges);
            }
            if (this.curtea)
            {
                this.root.removeChild(this.curtea);
            }
            this.loadTreeData();
        },
        /**
         * Setzt die Daten im Baum
         * @param {Object} data the data used to build the tree
         *
         * TODO: Is it in use anymore?
         */
        setTreeData: function (data)
        {
            console.log("tree.js setTreeData: is it in use anymore");
            var type = data.id, i;
            this[type] = data;
            var imgs = Ext.DomQuery.select('img[class=x-tree-ec-icon x-tree-elbow-end-plus]',
                MySched.Tree.tree.body.dom);
            for (i = 0; i < imgs.length; i++)
            {
                imgs[i].alt = "collapsed";
            }
            imgs = Ext.DomQuery.select('img[class=x-tree-ec-icon x-tree-elbow-plus]',
                MySched.Tree.tree.body.dom);
            for (i = 0; i < imgs.length; i++)
            {
                imgs[i].alt = "collapsed";
            }
        }
    };
}();
