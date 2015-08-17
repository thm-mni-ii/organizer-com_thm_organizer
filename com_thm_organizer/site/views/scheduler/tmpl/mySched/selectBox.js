/**
 * Create the select boxes with schedule data
 *
 * @class MySched.SelectBoxes
 */
MySched.SelectBoxes = function ()
{
    var selectPanel, selectBoxes = [], scheduleData, levelData = [], stores = [], maxDepth;
    return {
        /**
         * Initialization. Create panel and model. Make ajax request to get schedule data
         *
         * @method init
         * @return {Ext.panel.Panel} * Returns the main panel
         */
        init: function ()
        {
            this.selectPanel = Ext.create(
                'Ext.panel.Panel',
                {
                    plugins: 'responsive',
                    title: 'Stand vom',
                    id: 'selectBoxes',
                    region: 'west',
                    editable: false,
                    bodyPadding: 5,
                    width: 242,
                    minSize: 242,
                    maxSize: 242,
                    height: 470,
                    scroll: false,
                    responsiveConfig: {
                        'width <= 1100': {
                            collapsible: true,
                            collapsed: true
                        },
                        'width <= 400': {

                        },
                        'width > 400 && width <= 1000': {

                        }
                    }
                }
            );
            Ext.define(
                'SelectBoxModel',
                {
                    extend: 'Ext.data.Model',
                    fields: [
                        {type: 'string', name: 'name'},
                        {type: 'string', name: 'id'}
                    ]
                }
            );
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
                            MySched.SelectBoxes.createSelectBoxes(json.tree);
                            Ext.get('selectBoxes-body').unmask();
                            MySched.Schedule.fireEvent("dataLoaded", MySched.Schedule);
                        }
                    }
                );
            }
            return this.selectPanel;
        },
        /**
         * Creates all needed comboboxes depending on the depth of the tree (schedule data). Also creates the stores
         * and empty data for every store except the store for the highest level of the tree.
         *
         * @method createSelectBoxes
         * @param {object} data The data of schedule in the shape of a tree
         */
        createSelectBoxes: function(data)
        {
            var i = 0, tmpSBox, preSelectedValue = '', preSelectedId = 0;
            this.stores = [];
            this.levelData = [];
            this.selectBoxes = [];
            this.scheduleData = data;
            this.maxDepth = this.getDepthOfTree(this.scheduleData, 0);

            for (i = 0; i <= this.maxDepth; i++)
            {
                this.levelData[i] = [];
                this.stores[i] = Ext.create('Ext.data.Store',
                    {
                        model: 'SelectBoxModel',
                        data: this.levelData[i],
                        sorters: [
                            {
                                property : 'name',
                                direction: 'ASC'
                            }
                        ]
                    }
                );
                tmpSBox = Ext.create(
                    'Ext.form.field.ComboBox',
                    {
                        height:60,
                        multiSelect: false,
                        width: 220,
                        minWidth: 200,
                        cls: 'level_' + i,
                        displayField: 'name',
                        store: this.stores[i],
                        queryMode: 'local',
                        editable: false,
                        emptyText: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SELECT_OPTION,
                        listeners:
                        {
                            select: function(combo, records, eOpts)
                            {
                                MySched.SelectBoxes.changedSelectBoxValue(records[0]);
                            }
                        }
                    }
                );
                if (i > 0)
                {
                    tmpSBox.setDisabled(true);
                }
                this.selectBoxes.push(tmpSBox);
                this.selectPanel.unmask();
            }

            for(i = 0; i < this.scheduleData.length;i++)
            {
                this.levelData[0].push({"name":this.scheduleData[i].text, "id":this.scheduleData[i].id, "level":0});
                if(this.scheduleData.length === 1)
                {
                    this.selectBoxes[0].select(this.scheduleData[i].text);
                }
            }

            this.stores[0].setData(this.levelData[0]);

            // group plans (pool) are always default. If this has only one child also these child is preselected.
            var allRecords = this.stores[0].snapshot || this.stores[0].data;
            for(i = 0; i < allRecords.items.length; i++)
            {
                if(allRecords.items[i].id.indexOf('pool') >= 0)
                {
                    preSelectedValue = allRecords.items[i].data.name;
                    preSelectedId = i;
                }
            }
            if(preSelectedValue !== '')
            {
                this.selectBoxes[0].select(preSelectedValue);
            }
            MySched.SelectBoxes.changedSelectBoxValue(allRecords.items[preSelectedId]);
            this.recreate();
        },
        /**
         * Deletes all elements form the select panel and adds the select boxes.
         *
         * @method recreate
         */
        recreate: function()
        {
            // delete all children from panel
            this.selectPanel.removeAll();
            for (var i = 0; i < this.selectBoxes.length; i++)
            {
                this.selectPanel.add(this.selectBoxes[i]);
            }
            this.selectPanel.updateLayout();
        },
        /**
         * Find the depth of a tree
         *
         * @method getDepthOfTree
         * @param {object} tree A tree
         * @param {number} level The current level of the tree
         * @return {number} level The new calculated level
         */
        getDepthOfTree: function(tree, level)
        {
            level++;
            var oneHasChild = false;
            for(var i = 0; i < tree.length;i++)
            {
                if(tree[i].hasOwnProperty('children') && tree[i].children)
                {
                    oneHasChild = true;
                    this.getDepthOfTree(tree[i].children,level);
                }
            }
            if (oneHasChild)
            {
                level++;
            }
            return level;
        },
        /**
         * Fill the stores with the correct values according to the selection of the user.
         *
         * @method changedSelectBoxValue
         * @param {object} SelectedItem The selected record from the store
         */
        changedSelectBoxValue: function(SelectedItem)
        {
            var level = SelectedItem.get('level')+ 1,
                id = SelectedItem.get('id'),
                item = this.findItemInTree(id, this.scheduleData),
                plantypeID = "",
                i = 0;

            if(item.children)
            {
                for (i = level; i <= this.maxDepth; i ++){
                    this.levelData[i] = [];
                }
                var element = item;
                // if current element has just one child search for the next child that have more than just one child
                while((element.children && element.children.length <= 1))
                {
                    this.levelData[level].push({"name": element.children[0].text, "id": element.children[0].id, "level": level});
                    this.stores[level].setData(this.levelData[level]);
                    this.selectBoxes[level].select(element.children[0].text);
                    element = element.children[0];
                    level++;
                }
                if(element.children)
                {
                    for (i = 0; i < element.children.length; i++)
                    {
                        this.levelData[level].push({
                            "name": element.children[i].text,
                            "id": element.children[i].id,
                            "level": level
                        });
                    }
                    this.stores[level].setData(this.levelData[level]);
                }
                else
                {
                    MySched.Base.showScheduleTab(element.id, element.nodeKey, element.gpuntisID, element.semesterID, plantypeID, element.type);
                    this.selectBoxes[oiginalLevel - 1].setValue(this.selectBoxes[oiginalLevel - 1].getSelection().data.name + ' ');
                }

                for(i = 0; i <= this.maxDepth;i++)
                {
                    if(i <= level && this.levelData[i].length > 1)
                    {
                        this.selectBoxes[i].setDisabled(false);
                    }
                    else
                    {
                        this.stores[i].setData({});
                        this.selectBoxes[i].setDisabled(true);

                    }
                    if(i >= level)
                    {
                        this.selectBoxes[i].clearValue();
                    }
                }
            }
            else
            {
                MySched.Base.showScheduleTab(item.id, item.nodeKey, item.gpuntisID, item.semesterID, plantypeID, item.type);
                this.selectBoxes[oiginalLevel - 1].setValue(this.selectBoxes[oiginalLevel - 1].getSelection().data.name + ' ');
            }
        },
        /**
         * Searches through a tree to find an item with the given id
         *
         * @method findItemInTree
         * @param {string} id The id to find
         * @param {object} tree The tree in that should searched
         * @return {object} result False if the element was not found otherwise the element as object
         */
        findItemInTree: function(id, tree)
        {
            var result = false;
            for(var i = 0; i < tree.length; i++)
            {
                if(tree[i].id == id)
                {
                    return tree[i];
                }
                if(tree[i].hasOwnProperty('children') && tree[i].children)
                {
                    result =  this.findItemInTree(id, tree[i].children);
                    if(result !== false){
                        return result;
                    }
                }
            }
            return result;
        },
        /**
         * Set the title of the panel
         *
         * @method setTitle
         * @param {String} title The title of the panel
         * @param {Boolean} append Switch if the text should append or replace
         */
        setTitle: function(title, append)
        {
            if(append)
            {
                this.selectPanel.setTitle(this.selectPanel.title + title);
            }
            else
            {
                this.selectPanel.setTitle(title);

            }
        }
    }
}();
