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


// Add style for "null"-value dynamically
var sStyle = '.x-checkbox-null input {\n';
sStyle += '-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=50)";\n';
sStyle += 'filter:progid:DXImageTransform.Microsoft.Alpha(Opacity=50);\n';
sStyle += 'opacity:.5;}';
Ext.util.CSS.createStyleSheet(sStyle, 'Ext.ux.form.TriCheckbox');

// Define class Ext.ux.form.TriCheckbox
Ext.define('Ext.ux.form.TriCheckbox',
{
	extend: 'Ext.form.field.Checkbox',
    alias: ['widget.xtricheckbox', "widget.tri-checkbox"],

    triState: true, // triState can dynamically be disabled using enableTriState

    values: ['null', '0', '1'], // The values which are toggled through
    checkedClasses: ['x-checkbox-null', '', Ext.baseCSSPrefix + 'form-cb-checked'], // The classes used for the different states

    currentCheck: 0, // internal use: which state we are in?

    getSubmitValue: function()
    {
    	return this.value;
    },

    getRawValue: function()
    {
    	return this.value;
    },

    getValue: function()
    {
    	return this.value;
    },

    initValue: function()
    {
    	var me = this;
        me.originalValue = me.lastValue = me.value;
        me.suspendCheckChange++;
        me.setValue(me.value);
        me.suspendCheckChange--;
    },

    setRawValue: function(v)
    {
    	var me = this;

        if (v === false) v = '0';
        if (v === true) v = '1';
        if (v == null || v == '' || v === undefined)
        {    if (!this.triState)
                    v = '0';
            else    v = 'null';
        }

        var oldCheck = me.currentCheck;
        me.currentCheck = me.getCheckIndex(v);
        me.value = me.rawValue = me.values[me.currentCheck];

        // Update classes
        var inputEl = me.inputEl;
        if (inputEl)
        {    inputEl.dom.setAttribute('aria-checked', me.value == '1'?true:false);
            me['removeCls'](me.checkedClasses[oldCheck])
            me['addCls'](me.checkedClasses[this.currentCheck]);
        }
    },

    // Returns the index from a value to a member of me.values
    getCheckIndex: function(value)
    {
    	for (var i = 0; i < this.values.length; i++)
        {    if (value === this.values[i])
            {    return i;
            }
        }
        return 0;
    },

    // Handels a click on the checkbox
    onBoxClick: function(e)
    {
    	this.toggle();
    },

    // Switches to the next checkbox-state
    toggle: function()
    {
    	var me = this;
        if (!me.disabled && !me.readOnly)
        {    var check = me.currentCheck;
            check++;
            if (check >= me.values.length) check = 0;
            this.setValue(me.values[check]);
        }
    },

    // Enables/Disables tristate-handling at runtime (enableTriState(false) gives a 'normal' checkbox)
    enableTriState: function(bTriState)
    {
    	if (bTriState == undefined) bTriState = true;
        this.triState = bTriState;
        if (!this.triState)
        {    this.setValue(this.value);
        }
    },

    // Toggles tristate-handling ar runtime
    toggleTriState: function()
    {
    	this.enableTriState(!this.triState);
    }
});