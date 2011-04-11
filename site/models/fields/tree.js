Ext.tree.TreeNode.prototype.check = function(state, descend, bulk) {

	var tree = this.getOwnerTree();
	var parentNode = this.parentNode;

	if( typeof bulk == 'undefined' ) {
		bulk = false;
	}
	if( typeof state == 'undefined' || state === null ) {
		state = this.ui.checkbox.checked;
		descend = !state;
		if( state ) {
			this.expand(false, false);
		}
	} else {
		if(typeof this.ui.checkbox.checked != "undefined")
			this.ui.checkbox.checked = state;
		else
			return;
	}
	if(typeof this.attributes.checked != "undefined")
		this.attributes.checked = state;
	else
		return;

	// do we have parents?
	if( parentNode !== null && state ) {
		if( !parentNode.ui.checkbox.checked ) {
			parentNode.check(state, false, true);
		}
	}
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
	        listeners: {
	            checkchange: function(node, checked)
	            {
	            	node.check();
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