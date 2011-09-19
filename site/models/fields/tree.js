Ext.data.Tree.prototype.check = function(state, descend, bulk) {

	//var tree = this.getOwnerTree();

	this.data.checked = state;
	if(this.ui.checkbox)
		this.ui.checkbox.checked = state;

	if( descend && !this.isLeaf() ) {
		var cs = this.childNodes;
      	for(var i = 0; i < cs.length; i++) {
      		cs[i].check(state, true, true);
      	}
	}

	if( !bulk ) {
		this.fireEvent('check', this, state);
	}
};

Ext.tree.Panel.prototype.getChecked = function(node){
	var checked = [], i;
	if( typeof node == 'undefined' ) {
		node = this.getRootNode();
	}
	if( node.data.checked ) {
		checked.push(node.data.id);
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

		var tree = Ext.create('Ext.tree.Panel', {
		    title: ' ',
		    singleExpand: false,
		    id: 'selectTree',
	        preventHeader: true,
		    height: 470,
		    autoscroll: true,
		    rootVisible: false,
		    ddGroup: 'lecture',
	        ddConfig: {
	        	enableDrag: true
	        },
			layout: {
			    type: 'fit'
			},
		    root: {
		    	id: 'rootTreeNode',
		        text: 'root',
		        expanded: true,
	            children: children
		    },
		    listeners: {
	            checkchange: function(node, checked)
	            {
					node.expandChildren(true,
						function(){
							node.cascadeBy(
								function(childNode)
									{
										childNode.set('checked', checked);
									}
							);
						}
					);

					tree.fireEvent('check');
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