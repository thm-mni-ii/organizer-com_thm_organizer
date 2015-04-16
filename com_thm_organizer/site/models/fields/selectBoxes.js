/**
 * This class creates the select boxes for the backend
 *
 * @class selectBoxes
 */
var selectBoxes = (function(win){
    var selectBoxesElements = [], stores = [], rawData, selectedValues;

    /**
     * Creates all select boxes for all three sections (group, room, teacher)
     *
     * @param {object} data The schedule data
     */
    function createSelectBoxes(data){
        this.selectBoxesElements = [];
        this.stores = [];
        for(var i = 0; i < data.children.length; i++)
        {
            if(data.children[i].gpuntisID !== 'subject') {
                this.stores[i] = Ext.create(
                    'Ext.data.Store', {
                        model: 'SelectBoxModel',
                        data: getDataForStore(data.children[i])
                    }
                );
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
                        editable: false,
                        width: '100%',
                        minWidth: 200,
                        cls: 'level_' + i,
                        displayField: 'name',
                        store: this.stores[i],
                        queryMode: 'local'
                    }
                );

                var allRecords = [];
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

    /**
     * Adding onclick event handler to ratio buttons
     *
     * @param {array} ratioButtons Array of radio buttons
     * @param {integer} selectBoxId
     */
    function ratioClick(ratioButtons, selectBoxId){
        for(var i = 0; i < ratioButtons.length; i++){
            var hide = 0;
            if(i === 0){
                hide = 1;
            }
            ratioButtons[i].setAttribute('onclick', 'selectBoxes.toggleSelectBox(' + i + ', ' + selectBoxId + ')');
        }
    }

    /**
     * Fill the store with the correct data
     *
     * @param {array} storeData Data for the store
     * @return {Array} data Array of objects for the store with schedule data
     */
    function getDataForStore(storeData)
    {
        var data = [];
        for(var i=0;i < storeData.children.length;i++)
        {
            data.push({"name":storeData.children[i].text, "id":storeData.children[i].id});
        }
        return data;
    }
    /**
     * Get the selected values of the checkboxes
     *
     * @return {Array} ObjectString Array of ids of selected values
     */
    function getSelection()
    {
        var ObjectString = [];
        for(var i= 0; i < this.selectBoxesElements.length; i++)
        {
            var value = this.selectBoxesElements[i].getValue();
            for(var j = 0; j < value.length; j++)
            {
                var record = this.selectBoxesElements[i].findRecordByValue(value[j]);
                ObjectString.push(record.id);
            }
        }
        return ObjectString;
    }

    /**
     * Save data to variables
     *
     * @param {Object} schedData Schedule data
     * @param {String} selected Saved selected ids
     */
    function setVariables(schedData,selected)
    {
        this.selectedValues = '';
        if(selected !== '') {
            this.selectedValues = Ext.decode(selected);
        }
        this.rawData = schedData;
    }
    /**
     * Returns the select boxes
     *
     * @return {Array} * Array of the select boxes
     */
    function getSBoxes(){
        return this.selectBoxesElements;
    }
    return {
        /**
         * Initialization
         *
         * @param {object} data schedule data
         * @param {string} selected selected ids
         */
        init: function(data, selected)
        {
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
        /**
         * Return the selected values
         *
         * @return {Array} values selected values
         */
        getSelectedValues: function()
        {
            var values = getSelection();
            return values;

        },
        /**
         * Hide and show the select boxes
         *
         * @param {Integer} hide switch to hide and show
         * @param {Integer} buttonNo Nomber of the button
         */
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