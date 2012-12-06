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
	var initselectedItems = initselectBox.dom.getSelected();
	if(initselectedItems.length > 0)
	{
		var initselectedItem = initselectedItems[0];
		var initselectedItemValue = initselectedItem.value;
		loadTreeData(initselectedItemValue);
	}
});

function loadTreeData(selectedItemValue)
{
	if (loadMask)
    {
		loadMask.destroy();
    }
	loadMask = new Ext.LoadMask(
    "selectTree",
    {
        msg: "Loading..."
    });
	loadMask.show();
	
	Ext.Ajax.request(
    {
        url: externLinks.ajaxHandler,
        method: 'POST',
        params: 
        {
        	departmentSemesterSelection: selectedItemValue,
            scheduletask: "TreeView.load",
            menuID: menuID
        },
        failure: function (response)
        {
        	
        },
        success: function (response)
        {
            var json = Ext.decode(response.responseText);
            var newtree = json["tree"];
            var rootNode = tree.getRootNode();
            rootNode.removeAll(true);
            rootNode.appendChild(newtree);
            tree.update();

            checkBoxEvents();
        	
        	tree.doGray();
            
            if (loadMask)
		    {
				loadMask.destroy();
		    }
        }
    });
}