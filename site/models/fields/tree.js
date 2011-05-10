Ext.tree.TreeNode.prototype.check = function(state, descend, bulk) {

	var tree = this.getOwnerTree();

	this.attributes.checked = state;
	if(this.ui.checkbox)
		this.ui.checkbox.checked = state;

	if( descend && !this.isLeaf() ) {
		var cs = this.childNodes;
      	for(var i = 0; i < cs.length; i++) {
      		cs[i].check(state, true, true);
      	}
	}

	if( !bulk ) {
		tree.fireEvent('check', this, state);
	}
};

Ext.tree.TreePanel.prototype.getChecked = function(node){
	var checked = [], i;
	if( typeof node == 'undefined' ) {
		node = this.rootVisible ? this.getRootNode() : this.getRootNode().firstChild;
	}
	if( node.attributes.checked ) {
		checked.push(node.id);
		if( !node.isLeaf() ) {
			for( i = 0; i < node.childNodes.length; i++ ) {
				checked = checked.concat( this.getChecked(node.childNodes[i]) );
			}
		}
	}
	else
		if( !node.isLeaf() ) {
			for( i = 0; i < node.childNodes.length; i++ ) {
				checked = checked.concat( this.getChecked(node.childNodes[i]) );
			}
		}
	return checked;
};

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
        	height: 400,
	        root: {
	        	id: 'rootTreeNode',
	           	leaf: false,
	           	text: 'root',
	            children: children,
	            expanded: true
	        },
	        loader: new Ext.tree.TreeLoader({preloadChildren: true}),
	        listeners: {
	            checkchange: function(node, checked)
	            {
					node.check(checked, true, false);
	            }
       	    }
		});

		tree.on('check', function() {
			var paramID = Ext.get('jform_params_id');
			var paramValue = tree.getChecked().join('/');
			paramID.dom.value = paramValue;
		}, tree);

		// render the tree
	    tree.render('tree-div');
	});