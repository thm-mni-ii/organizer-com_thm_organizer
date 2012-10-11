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
                	treeIDs: Ext.encode(treeIDs),
                    scheduletask: "TreeView.load"
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
                    if (loadMask)
        		    {
        				loadMask.destroy();
        		    }
                }
            });
      	}
    });
});