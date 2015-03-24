var selectBoxes = (function(win){
    var selectBoxesElements = [], stores = [], rawData, selectedValues;
    function createSelectBoxes(data){
        this.selectBoxesElements = [];
        this.stores = [];
        //console.log(data);
        for(var i = 0; i < data.children.length; i++)
        {
            if(data.children[i].gpuntisID !== 'subject') {
                this.stores[i] = Ext.create(
                    'Ext.data.Store', {
                        model: 'SelectBoxModel',
                        data: getDataForStore(data.children[i])
                    }
                );
                //console.log(i);
                //console.log(data.children[i]);
                var parent;
                var show = false;
                if(data.children[i].gpuntisID === 'pool'){
                    var parent = Ext.get("jform_params_departmentSemesterSelection-lbl").getParent().getParent();
                }
                if(data.children[i].gpuntisID === 'room'){
                    var parent = Ext.get("jform_params_displayRoomSchedule-lbl").getParent().getParent();
                    var doEL = document.getElementsByName("jform[params][displayRoomSchedule]");
                    show = doEL[0].checked;
                    ratioClick(doEL, i);
                }
                if(data.children[i].gpuntisID === 'teacher'){
                    var parent = Ext.get("jform_params_displayTeacherSchedule-lbl").getParent().getParent();
                    var doEL = document.getElementsByName("jform[params][displayTeacherSchedule]");
                    show = doEL[0].checked;
                    ratioClick(doEL, i);
                }

                var tempSBox = Ext.create(
                    'Ext.form.field.ComboBox',
                    {
                        fieldLabel: data.children[i].text,
                        height: 60,
                        hidden: show,
                        renderTo: parent,
                        multiSelect: true,
                        width: '100%',
                        minWidth: 200,
                        cls: 'level_' + i,
                        displayField: 'name',
                        store: this.stores[i],
                        queryMode: 'local',
                        listeners: {
                            select: function (combo, records, eOpts) {
                                //MySched.SelectBoxes.changedSelectBoxValue(records[0]);
                            }
                        }
                    }
                );
                var allRecords = [];
                //console.log(this.selectedValues);
                for (var j = 0; j < this.selectedValues.length; j++) {
                    var rec = tempSBox.findRecord('id', this.selectedValues[j]);
                    if (rec) {
                        allRecords.push(rec);
                    }
                }
                tempSBox.select(allRecords);
                this.selectBoxesElements.push(tempSBox);
            }
        }
    }
    function ratioClick(ratioButtons, selectBoxId){
        //console.log(ratioButtons);
        for(var i = 0; i < ratioButtons.length; i++){
            //console.log(ratioButtons[i]);
            var hide = 0;
            if(i === 0){
                hide = 1;
            }
            ratioButtons[i].setAttribute('onclick', 'selectBoxes.toggleSelectBox(' + i + ', ' + selectBoxId + ')');
        }
    }
    function getDataForStore(storeData)
    {
        var data = [];
        for(var i=0;i < storeData.children.length;i++)
        {
            //console.log(storeData.children[i]);
            data.push({"name":storeData.children[i].text, "id":storeData.children[i].id});
        }
        return data;
    }
    function createPanel()
    {
        var mainPanel = Ext.create(
            'Ext.panel.Panel',
            {
                title: 'Select Boxes',
                id: 'selectBoxes',
                region: 'west',
                bodyPadding: 5,
                width: '100%',
                minSize: 242,
                maxSize: 242,
                height: 470,
                scroll: false
                //bodyCls: 'MySched_SelectTree',
                //store: treeStore
            }
        );
        for(var i = 0; i < this.selectBoxesElements.length; i++)
        {
            mainPanel.add(this.selectBoxesElements[i]);
            this.selectBoxesElements[i].updateLayout();
        }
        mainPanel.updateLayout();
        console.log(mainPanel);
        //return mainPanel;
    }
    function getSelection()
    {
        var ObjectString = [];
        for(var i= 0; i < this.selectBoxesElements.length; i++)
        {
            //console.log(this.selectBoxesElements[i] );
            var value = this.selectBoxesElements[i].getValue();
            if(value.length > 0)
            {
                //console.log(this.rawData[0].children[i]);
                //TODO Needed for all selected
                //ObjectString.push(this.rawData[0].children[i].id);
            }
            for(var j = 0; j < value.length; j++)
            {
             //   console.log(value[j]);
                var record = this.selectBoxesElements[i].findRecordByValue(value[j]);
           //     console.log(record.id);
                //ObjectString += '{"' + record.id + '":"slected"},';
                ObjectString.push(record.id);// = "selected";
                var index = this.selectBoxesElements[i].getStore().indexOf(record);
         //       console.log(index);
            }
        }
        //console.log(ObjectString);
        return ObjectString;
    }
    function setVariables(schedData,selected)
    {
        //console.log(selected);
        this.selectedValues = '';
        if(selected !== '') {
            this.selectedValues = Ext.decode(selected);
        }
        this.rawData = schedData;
    }
    function getSBoxes(){
        return this.selectBoxesElements;
    }
    return {
        init: function(data, selected)
        {
            //console.log(selected);
            Ext.define(
                'SelectBoxModel',
                {
                    extend: 'Ext.data.Model',
                    fields: [
                        {type: 'string', name: 'text'},
                        {type: 'string', name: 'id'}
                    ]
                }
            );
            setVariables(data, selected);
            createSelectBoxes(data[0]);
        },
        render: function()
        {
            //console.log("render");
            //console.log(this);
            return createPanel();
        },
        getSelectedValues: function()
        {
            var values = getSelection();
            //console.log(values);
            return values;

        },
        toggleSelectBox: function(hide, buttonNo)
        {
            var boxes = getSBoxes();
            if(hide){
                boxes[buttonNo].show();
            } else {
                boxes[buttonNo].hide();
            }
        }
    }
}(window));