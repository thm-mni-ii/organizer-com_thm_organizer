/**
 * Loading data for schedule
 */
var loadMask = null;
/**
 *  On load function that gets the schedule data
 */
Ext.onReady(function()
{
    Ext.get('jform_params_departmentSemesterSelection').on({
        'change': function (e) {
            e.stopEvent();
            var selectBox = e.getTarget();
            var selectedItems = selectBox.getSelected();
            var selectedItem = selectedItems[0];
            var selectedItemValue = selectedItem.value;
            loadTreeData(selectedItemValue);
        }
    });
    var initselectBox = Ext.get('jform_params_departmentSemesterSelection');
    var initselectedItems = initselectBox.getValue();
    if(initselectedItems.length > 0)
    {
        loadTreeData(initselectedItems);
    }
});

/**
 * Ajax request which get the schedule data
 *
 * @param {String} selectedItemValue Id of the selected schedule
 */
function loadTreeData(selectedItemValue)
{
    Ext.get('attrib-basic').mask("Loading");

    Ext.Ajax.request(
    {
        url: externLinks.ajaxHandler,
        method: 'GET',
        params:
        {
            departmentSemesterSelection: selectedItemValue,
            scheduletask: "TreeView.load",
            menuID: menuID
        },
        success: function (response)
        {
            var json = Ext.decode(response.responseText);
            var newtree = json.tree;

            var val = document.getElementById('jform_params_id').value;
            selectBoxes.init(newtree, val);

            Ext.get('attrib-basic').unmask();
        }
    });
}