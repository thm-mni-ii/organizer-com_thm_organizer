/**
 * This class creates the select boxes for the backend
 *
 * @class selectBoxes
 */
var selectBoxes = (function(win)
{
    var selectBoxesElements = [], stores = [], rawData, selectedValues;

    /**
     * Creates all select boxes for all three sections (group, room, teacher)
     *
     * @param {object} data The schedule data
     */
    function createSelectBoxes(data)
    {
        this.selectBoxesElements = [];
        this.stores = [];
        for(var i = 0; i < data.children.length; i++)
        {
            if(data.children[i].gpuntisID !== 'subject')
            {
                this.stores[i] = Ext.create(
                    'Ext.data.Store',
                    {
                        model: 'SelectBoxModel',
                        data: getDataForStore(data.children[i])
                    }
                );
                var parent;
                var show = false;
                if(data.children[i].gpuntisID === 'pool')
                {
                    var parent = Ext.get("jform_params_departmentSemesterSelection-lbl").getParent().getParent();
                }
                if(data.children[i].gpuntisID === 'room')
                {
                    parent = document.getElementById('jform_params_displayRoomSchedule').getParent().getParent();
                    var select = document.getElementById('jform_params_displayRoomSchedule').getElementsByTagName('option');
                    show = select[0].selected;
                    ratioClick('jform_params_displayRoomSchedule', i);
                }
                if(data.children[i].gpuntisID === 'teacher')
                {
                    parent = document.getElementById('jform_params_displayTeacherSchedule').getParent().getParent();
                    var select = document.getElementById('jform_params_displayTeacherSchedule').getElementsByTagName('option');
                    show = select[0].selected;
                    ratioClick('jform_params_displayTeacherSchedule', i);
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
                        queryMode: 'local',
                        listeners: {
                            click: {
                                element: 'el',
                                fn: function () {
                                    selectBoxes.checkSelection();
                                }
                            }
                        }
                    }
                );

                var allRecords = [];
                for (var j = 0; j < this.selectedValues.length; j++)
                {
                    var rec = tempSBox.findRecord('id', this.selectedValues[j]);
                    if (rec)
                    {
                        allRecords.push(rec);
                    }
                }
                tempSBox.select(allRecords);
                this.selectBoxesElements.push(tempSBox);
                if (tempSBox.getValue().length <= 0)
                {
                    tempSBox.setValue(COM_THM_ORGANIZER_SHOW_ALL);
                }
            }
        }
    }

    /**
     * Adding onclick event handler to ratio buttons
     *
     * @param {array} ratioButtons Array of radio buttons
     * @param {integer} selectBoxId
     */
    function ratioClick(selectId, selectBoxId)
    {
        var sboxes = document.getElementById(selectId + '_chzn');
        sboxes.setAttribute('onclick', 'selectBoxes.toggleSelectBox(' + selectBoxId + ', "' + selectId + '_chzn")');
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
     * Returns the selected values of a box
     *
     * @param {Integer} number Number of the box
     * @return {Object} ObjectString The selected values
     */
    function getSelectionPerBox(number)
    {
        var ObjectString = [];
        var value = this.selectBoxesElements[number].getValue();
        for(var j = 0; j < value.length; j++)
        {
            var record = this.selectBoxesElements[number].findRecordByValue(value[j]);
            ObjectString.push(record.id);
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
        if(selected !== '')
        {
            this.selectedValues = Ext.decode(selected);
        }
        this.rawData = schedData;
    }
    /**
     * Returns the select boxes
     *
     * @return {Array} * Array of the select boxes
     */
    function getSBoxes()
    {
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
         * @param {Integer} elementId id of the choosen select box
         * @param {Integer} buttonNo Nomber of the button
         */
        toggleSelectBox: function(buttonNo, elementId)
        {
            var element = document.getElementById(elementId).getElementsByTagName('a')[0], boxes = getSBoxes();
            if(element.rel.replace('value_', '') == "0")
            {
                boxes[buttonNo].hide();
            } else {
                boxes[buttonNo].show();
            }
        },
        /**
         * Add free text if nothing is selected
         */
        checkSelection: function()
        {
            var sboxes = getSBoxes();
            for(var i = 0; i < sboxes.length; i++)
            {
                if(getSelectionPerBox(i) <= 0)
                {
                    sboxes[i].setValue(COM_THM_ORGANIZER_SHOW_ALL);
                }
            }
        }
    }
}(window));