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
    var initselectedItems = initselectBox.getValue();//initselectBox.dom.getSelected();
    if(initselectedItems.length > 0)
    {
        //var initselectedItem = initselectedItems[0];
        //var initselectedItemValue = initselectedItem.value;
        loadTreeData(initselectedItems);
    }
});

function loadTreeData(selectedItemValue)
{
    if (loadMask)
    {
        loadMask.destroy();
    }
    //loadMask = new Ext.LoadMask("selectTree", { msg: "Loading..." });
    //loadMask.show();

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


            rootNode.removeAll(true);
            rootNode.appendChild(newtree);
            tree.update();
            tree.updateLayout();
            var val = document.getElementById('jform_params_id').value;
            selectBoxes.init(newtree, val);
            var Panel = selectBoxes.render("as");
            Panel.render('tree-div');
            //tree.s
            //tree.updateBox();

            //tree.unmask();

            //checkBoxEvents();

            tree.doGray();
            //console.log(tree.child());
            //console.log(tree);
            if (loadMask)
            {
                loadMask.destroy();
            }
        }
    });
}