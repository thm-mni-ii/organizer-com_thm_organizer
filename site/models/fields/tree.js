Ext.override(Ext.tree.Column, {
	initComponent: function() {
        var origRenderer = this.renderer || this.defaultRenderer,
            origScope    = this.scope || window;

        this.renderer = function(value, metaData, record, rowIdx, colIdx, store, view) {
            var buf   = [],
                format = Ext.String.format,
                depth = record.getDepth(),
                treePrefix  = Ext.baseCSSPrefix + 'tree-',
                elbowPrefix = treePrefix + 'elbow-',
                expanderCls = treePrefix + 'expander',
                imgText     = '<img src="{1}" class="{0}" />',
                checkboxText= '<input id="'+record.data.id+'" type="hidden" value="'+record.data.checked+'" role="checkbox" class="{0}" {1} />';
                checkboxText+= '<img id="'+record.data.id+'_fake" class="MySched_checkbox_fake" src="'+images[record.data.checked]+'">';
                formattedValue = origRenderer.apply(origScope, arguments),
                href = record.get('href'),
                target = record.get('hrefTarget'),
                cls = record.get('cls');

            while (record) {
                if (!record.isRoot() || (record.isRoot() && view.rootVisible)) {
                    if (record.getDepth() === depth) {
                        buf.unshift(format(imgText,
                            treePrefix + 'icon ' +
                            treePrefix + 'icon' + (record.get('icon') ? '-inline ' : (record.isLeaf() ? '-leaf ' : '-parent ')) +
                            (record.get('iconCls') || ''),
                            record.get('icon') || Ext.BLANK_IMAGE_URL
                        ));
                        if (record.get('checked') !== null) {
                            buf.unshift(format(
                                checkboxText,
                                (treePrefix + 'checkbox') + (record.get('checked') ? ' ' + treePrefix + 'checkbox-checked' : ''),
                                record.get('checked') ? 'aria-checked="true"' : ''
                            ));
                            if (record.get('checked')) {
                                metaData.tdCls += (' ' + Ext.baseCSSPrefix + 'tree-checked');
                            }
                        }
                        if (record.isLast()) {
                            if (record.isExpandable()) {
                                buf.unshift(format(imgText, (elbowPrefix + 'end-plus ' + expanderCls), Ext.BLANK_IMAGE_URL));
                            } else {
                                buf.unshift(format(imgText, (elbowPrefix + 'end'), Ext.BLANK_IMAGE_URL));
                            }

                        } else {
                            if (record.isExpandable()) {
                                buf.unshift(format(imgText, (elbowPrefix + 'plus ' + expanderCls), Ext.BLANK_IMAGE_URL));
                            } else {
                                buf.unshift(format(imgText, (treePrefix + 'elbow'), Ext.BLANK_IMAGE_URL));
                            }
                        }
                    } else {
                        if (record.isLast() || record.getDepth() === 0) {
                            buf.unshift(format(imgText, (elbowPrefix + 'empty'), Ext.BLANK_IMAGE_URL));
                        } else if (record.getDepth() !== 0) {
                            buf.unshift(format(imgText, (elbowPrefix + 'line'), Ext.BLANK_IMAGE_URL));
                        }
                    }
                }
                record = record.parentNode;
            }
            if (href) {
                formattedValue = format('<a href="{0}" target="{1}">{2}</a>', href, target, formattedValue);
            }
            if (cls) {
                metaData.tdCls += ' ' + cls;
            }
            return buf.join("") + formattedValue;
        };

        this.callParent(arguments);
    }
});

changeIconHighlight = function(event){
	var elImg = event.getTarget('.MySched_checkbox_fake', 5, true);
	var elInput = elImg.dom.getPrevious();

	if(event.type == "mouseover")
	{
		elImg.dom.src = images.base+elInput.value+"_highlighted.gif";
	}
	else
		elImg.dom.src = images.base+elInput.value+".gif";
}

setStatus = function(event){
	var elImg = event.getTarget('.MySched_checkbox_fake', 5, true);
	var elInput = elImg.dom.getPrevious();

	var record = tree.getRootNode().findChild('id',elInput.id,true);

	if(record.isLeaf() == true)
	{
		if(elInput.value == "unchecked")
		{
			elInput.value = "checked";
			elImg.dom.src = images.base+elInput.value+"_highlighted.gif";
		}
		else if(elInput.value == "checked")
		{
			elInput.value = "unchecked";
			elImg.dom.src = images.base+elInput.value+"_highlighted.gif";
		}
	}
	else
	{
		if(elInput.value == "unchecked")
		{
			elInput.value = "checked";
			elImg.dom.src = images.base+elInput.value+"_highlighted.gif";
		}
		else if(elInput.value == "checked")
		{
			elInput.value = "selected";
			elImg.dom.src = images.base+elInput.value+"_highlighted.gif";
		}
		else if(elInput.value == "selected")
		{
			elInput.value = "intermediate";
			elImg.dom.src = images.base+elInput.value+"_highlighted.gif";
		}
		else //intermediate
		{
			elInput.value = "unchecked";
			elImg.dom.src = images.base+elInput.value+"_highlighted.gif";
		}
	}
	record.data.checked = elInput.value;
	//tree.fireEvent('check');
}

Ext.data.Tree.prototype.check = function(state, descend, bulk) {
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

Ext.tree.Panel.prototype.getChecked = function(node, checkedArr){
	if(checkedArr == null)
		var checked = {};
	else
		checked = checkedArr;
	var i;
	if( typeof node == 'undefined' ) {
		node = this.getRootNode();
	}
	if( node.data.checked != "unchecked" && node.data.checked != null) {
		checked[node.data.id] = node.data.checked;
		if( !node.isLeaf() ) {
			for( i = 0; i < node.childNodes.length; i++ ) {
				var checkedChildren = this.getChecked(node.childNodes[i]);
				for (var attrname in checkedChildren) { checked[attrname] = checkedChildren[attrname];}
			}
		}
	}
	else
		if( !node.isLeaf() ) {
			for( i = 0; i < node.childNodes.length; i++ ) {
				var checkedChildren = this.getChecked(node.childNodes[i]);
				for (var attrname in checkedChildren) { checked[attrname] = checkedChildren[attrname];}
			}
		}
	return checked;
};

var tree = null;

Ext.onReady(function(){
	Ext.QuickTips.init();

	tree = Ext.create('Ext.tree.Panel', {
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
            /*checkchange: function(node, checked)
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
            },*/
            itemmouseleave: function()
            {
            	Ext.select('.MySched_checkbox_fake').removeAllListeners();
				Ext.select('.MySched_checkbox_fake').on({
					'mouseover': function (e) {
						e.stopEvent();
						changeIconHighlight(e);
			      	},
			      	'mouseout': function (e) {
			        	e.stopEvent();
						changeIconHighlight(e);
			      	},
			      	'click': function (e) {
			        	if (e.button == 0) //links Klick
			        	{
			          		e.stopEvent();
			          		setStatus(e);
			        	}
			      	}
			    });
            }
		}
	});

	/*tree.on('check', function() {
		var paramID = Ext.get('jform_params_id');
		var treeChecked = tree.getChecked();
		var paramValue = Ext.encode(treeChecked);
		paramID.dom.value = paramValue;
	}, tree);*/

	// render the tree
    tree.render('tree-div');

    Ext.select('.MySched_checkbox_fake').on({
		'mouseover': function (e) {
			e.stopEvent();
			changeIconHighlight(e);
      	},
      	'mouseout': function (e) {
        	e.stopEvent();
			changeIconHighlight(e);
      	},
      	'click': function (e) {
        	if (e.button == 0) //links Klick
        	{
          		e.stopEvent();
          		setStatus(e);
        	}
      	}
    });
});

Joomla.submitbutton = function(task, type)
{
	if(task == "item.apply" || task == "item.save" || task == "item.save2new" || task == "item.save2copy")
	{
		var paramID = Ext.get('jform_params_id');
		var treeChecked = tree.getChecked();
		var paramValue = Ext.encode(treeChecked);
		paramID.dom.value = paramValue;
	}

	if (task == 'item.setType' || task == 'item.setMenuType') {
	if(task == 'item.setType') {
		document.id('item-form').elements['jform[type]'].value = type;
		document.id('fieldtype').value = 'type';
	} else {
		document.id('item-form').elements['jform[menutype]'].value = type;
	}
	Joomla.submitform('item.setType', document.id('item-form'));
	} else if (task == 'item.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
		Joomla.submitform(task, document.id('item-form'));
	} else {
		// special case for modal popups validation response
		$$('#item-form .modal-value.invalid').each(function(field){
			var idReversed = field.id.split("").reverse().join("");
			var separatorLocation = idReversed.indexOf('_');
			var name = idReversed.substr(separatorLocation).split("").reverse().join("")+'name';
			document.id(name).addClass('invalid');
		});
	}

}