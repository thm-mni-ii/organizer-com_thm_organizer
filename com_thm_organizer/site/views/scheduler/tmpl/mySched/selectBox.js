/**
 *
 */
MySched.SelectBoxes = function ()
{
    var selectPanel, selectBoxes = [], scheduleData, levelData = [], stores = [], maxDepth;
    return {
        /**
         * TODO
         *
         * @return {Ext.panel.Panel|*}
         */
        init: function ()
        {
            // TODO: config the select Panel
            this.selectPanel = Ext.create(
                'Ext.panel.Panel',
                {
                    title: 'Select Boxes',
                    id: 'selectBoxes',
                    region: 'west',
                    bodyPadding: 5,
                    width: 242,
                    minSize: 242,
                    maxSize: 242,
                    height: 470,
                    scroll: false
                    //bodyCls: 'MySched_SelectTree',
                    //store: treeStore
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
                            //console.log(treeData);
                            MySched.scheduleDataReady = true;
                            MySched.SelectBoxes.createSelectBoxes(json.tree);
                            Ext.get('selectBoxes-body').unmask();
                        }
                    }
                );
            }
            //this.selectPanel.mask("Loading");
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
            this.stores = [];
            this.levelData = [];
            this.selectBoxes = [];
            this.scheduleData = data;
            this.maxDepth = this.getDepthOfTree(this.scheduleData, 0);

            for (var i = 0; i <= this.maxDepth; i++)
            {
                this.levelData[i] = [];
                this.stores[i] = Ext.create('Ext.data.Store', {
                    model: 'SelectBoxModel',
                    data: this.levelData[i]
                });
                var tmpSBox = Ext.create(
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
                        typeAhead: true,
                        listeners: {
                            select: function(combo, records, eOpts) {
                                MySched.SelectBoxes.changedSelectBoxValue(records[0]);
                            }
                        }
                    }
                );
                if (i > 0) {
                    tmpSBox.setDisabled(true);
                }
                this.selectBoxes.push(tmpSBox);
                this.selectPanel.unmask();
            }

            for(var i = 0; i < this.scheduleData.length;i++)
            {
                //console.log(this.scheduleData[i]);
                //var data = this.scheduleData[i];
                var org = this.scheduleData[i];//.children[0];
                /*while(data.children.length <= 1){
                    data = data.children[0];
                }*/
                //if(this.scheduleData[i].children.length <= 1){
                //    console.log("one kid");
               // }
                this.levelData[0].push({"name":org.text, "id":org.id, "level":0});
            }
            //console.log(this.levelData[0].length);
            //this.stores[0].beginUpdate();
            this.stores[0].setData(this.levelData[0]);
            //this.stores[0].endUpdate();
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
                //this.selectBoxes[i].setStore(this.stores[i]);
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
            var level = SelectedItem.get('level')+1;
            var id = SelectedItem.get('id');
            var item = this.findItemInTree(id, this.scheduleData);
            //console.log(children);
            if(item.children) {
                this.levelData[level] = [];
                //console.log(item.children.length);
                var element = item;
                while(element.children && element.children.length <= 1){
                    element = element.children[0];
                }
                for (var i = 0; i < element.children.length; i++) {
                    //console.log(element.children[i]);
                    this.levelData[level].push({"name": element.children[i].text, "id": element.children[i].id, "level": level});
                }
                this.stores[level].setData(this.levelData[level]);
                for(var i = 0; i <= this.maxDepth;i++) {
                    if(i <= level)
                    {
                        //console.log("unlock + " + i);
                        this.selectBoxes[i].setDisabled(false);
                    }
                    else
                    {
                        //this.stores[i].setValue();
                        this.stores[i].setData({});
                        this.selectBoxes[i].setDisabled(true);

                    }
                    if(i >= level){
                        this.selectBoxes[i].clearValue( );
                    }
                }
                // TODO maybe delete all stores under this level
            }
            else
            {
                //console.log("no children");
                //console.log(item);
                var plantypeID = "";
                MySched.Tree.showScheduleTab(item.id, item.nodeKey, item.gpuntisID, item.semesterID, plantypeID, item.type);
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
                //console.log(this.scheduleData[i]);
                if(tree[i].id == id){
                    //console.log("found");
                    //return tree[i].children;
                    return tree[i];
                }
                //console.log()
                if(tree[i].hasOwnProperty('children') && tree[i].children)
                {
                    //console.log(tree[i]);
                    result =  this.findItemInTree(id, tree[i].children);
                    if(result !== false){
                        return result;
                    }
                }
            }
            return result;
        }
    }
}();