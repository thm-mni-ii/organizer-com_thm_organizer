/*globals Ext: false, externLinks: false, menuID: false, tree: false, checkBoxEvents: false */
/*jshint strict: false */
var loadMask = null;

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

function loadTreeData(selectedItemValue)
{
    Ext.get('myTabContent').mask("Loading");

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
            var rootNode = tree.getRootNode();

            var val = document.getElementById('jform_params_id').value;
            //console.log(val);
            selectBoxes.init(newtree, val);
            var Panel = selectBoxes.render();
            //console.log(Panel);
            //Panel.render('tree-div');

            //Panel.updateLayout();
            //tree.s
            //tree.updateBox();

            //tree.unmask();

            //checkBoxEvents();

            //console.log(tree.child());
            //console.log(tree);
            Ext.get('myTabContent').unmask();
        }
    });
}