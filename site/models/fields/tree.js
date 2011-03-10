Ext.onReady(function(){
		Ext.QuickTips.init();
		var Tree = Ext.tree;

		var tree = new Tree.TreePanel({
			useArrows: true,
	        autoScroll: true,
	        animate: true,
	        enableDD: false,
	        containerScroll: false,
        	rootVisible: false,
	        root: {
	        	id: 'rootTreeNode',
	           	leaf: false,
	           	text: 'root',
	            children: children,
	            expanded: true
	        },
	        listeners: {
	            click: function(n) {
	               	Ext.getDom('jform_request_id').value = n.getPath();
	            }
       	    }
		});

		// render the tree
	    tree.render('tree-div');
	    var request_id = Ext.getDom('jform_request_id').value;
	    tree.selectPath(request_id);
	});