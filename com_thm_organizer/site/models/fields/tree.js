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
                imgText     = '<img src="{1}" class="{0}" />';

			var checkboxText = "";

			if(record.data.publicDefault)
			{
	            checkboxText += '<input id="'+record.data.id+'_default" type="hidden" value="'+record.data.publicDefault+'" role="checkbox" />';
				checkboxText += '<img id="'+record.data.id+'_default_fake" class="MySched_checkbox_default_fake" src="'+images[record.data.publicDefault]+'">';
			}
			if(record.data.checked)
			{
	           	checkboxText += '<input id="'+record.data.id+'" type="hidden" value="'+record.data.checked+'" role="checkbox" class="{0}" {1} />';
	           	checkboxText += '<img id="'+record.data.id+'_fake" class="MySched_checkbox_fake" src="'+images[record.data.checked]+'">';	           	
	        }

            var formattedValue = origRenderer.apply(origScope, arguments),
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

changePublicDefaultHighlight = function(event){
	//Not implemented ( Keine macht mir nen Highlighted Icon :( )
}

setPublicDefaultStatus = function(event){
	var elImg = event.getTarget('.MySched_checkbox_default_fake', 5, true);
	var elInput = elImg.dom.getPrevious();
	var newStatus = null;
	var treeRoot = tree.getRootNode();

	var record = treeRoot.findChild('id',elInput.id.replace("_default", ""),true);

	if(elInput.value == "default")
		newStatus = "notdefault";
	else
	{
		var nodes = Ext.query('.MySched_checkbox_default_fake');
		Ext.each(nodes, function (item, index, allItems) {
		   item.src = images["notdefault"];
		   item.getPrevious().value = "notdefault";
		   var nodeTemp = treeRoot.findChild('id',item.id.replace("_default_fake", ""),true);
		   nodeTemp.raw.publicDefault = "notdefault";
		});
		newStatus = "default";
	}

	elInput.value = newStatus;
	elImg.dom.src = images[elInput.value];

	record.data.publicDefault = elInput.value;
}

setStatus = function(event)
{
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
		//var nodeID = node.data.id.replace(" ", "").replace("(", "").replace(")", "");
		var nodeID = node.data.id;
		checked[nodeID] = node.data.checked;
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

Ext.tree.Panel.prototype.getPublicDefault = function(node, checkedArr){
	if(checkedArr == null)
		var checked = {};
	else
		checked = checkedArr;
	var i;
	if( typeof node == 'undefined' ) {
		node = this.getRootNode();
	}

	if( Ext.isObject(node.raw) &&  node.raw.publicDefault == "default") {
		var nodeID = node.data.id.replace(" ", "").replace("(", "").replace(")", "");
		checked[nodeID] = node.raw.publicDefault;
	}
	else
		if( !node.isLeaf() ) {
			for( i = 0; i < node.childNodes.length; i++ ) {
				var checkedChildren = this.getPublicDefault(node.childNodes[i]);
				for (var attrname in checkedChildren) { checked[attrname] = checkedChildren[attrname];}
			}
		}
	return checked;
};

Ext.tree.Panel.prototype.doGray = function(node){
	var elImg = null;
	var elInput = null;
	
	if( typeof node == 'undefined' ) {
		node = this.getRootNode();
	}
	var id = node.data.id+"_fake";
	elImg = Ext.DomQuery.selectNode("[id="+id+"]", tree.dom);
	if(Ext.isDefined(elImg))
	{		
		elImg.setOpacity(1);
		elImg.setStyle('border', 'none');
	}
	var gray = false;
	if(node.hasChildNodes() === true)
		node.childNodes.each(function(v, k) {
			var state = tree.doGray(v);
			if(state === true)
				gray = state;
		});
	
	if(gray === true)
	{
		var elImg = null;
		var elInput = null;
		var id = node.data.id+"_fake";
		elImg = Ext.DomQuery.selectNode("[id="+id+"]", tree.dom);
		if(Ext.isDefined(elImg))
		{			
			elImg.setOpacity(0.4);
			elImg.setStyle('border', '1px solid gray');
		}
	}
	
	if(node.data.checked === "checked" || node.data.checked === "selected" || node.data.checked === "intermediate")
		gray = true;
	
	return gray;
};

var tree = null;

Ext.onReady(function(){
	Ext.QuickTips.init();

	tree = Ext.create('Ext.tree.Panel', {
	    title: ' ',
	    id: 'selectTree',
        preventHeader: true,
	    height: 470,
	    autoscroll: true,
	    rootVisible: false,
        pathSeparator: '#',
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
			          		tree.doGray();
			        	}
			      	}
			    });

			    Ext.select('.MySched_checkbox_default_fake').removeAllListeners();
			    Ext.select('.MySched_checkbox_default_fake').on({
					'mouseover': function (e) {
						e.stopEvent();
						changePublicDefaultHighlight(e);
			      	},
			      	'mouseout': function (e) {
			        	e.stopEvent();
						changePublicDefaultHighlight(e);
			      	},
			      	'click': function (e) {
			        	if (e.button == 0) //links Klick
			        	{
			          		e.stopEvent();
			          		setPublicDefaultStatus(e);
			        	}
			      	}
			    });
            },
            afterrender: function() {
            	tree.doGray();
            	
            	var publicDefault = tree.getPublicDefault();
			    for(var item in publicDefault)
			    {
			    	publicDefault = item;
			    	break;
			    }

			    if(Ext.isString(publicDefault))
			    {
					publicDefault = publicDefault.split(".");
			    }
			    else
			    {
			    	publicDefault = [];
            	}

				var nodePath = [];

				for(var i = 0; i < publicDefault.length; i++)
				{
					var length = nodePath.length;
					if(length == 0)
						nodePath.push(publicDefault[i]);
					else
						nodePath.push(nodePath[(length - 1)]+"."+publicDefault[i]);
				}

				nodePath = "#"+tree.root.id+"#"+nodePath.join("#");
    			tree.expandPath(nodePath, "id", "#");
            }            
		}
	});

	// render the tree
    tree.render('tree-div');
    
    var treeView = tree.getView();
    treeView.on('itemadd', function() {
    	tree.doGray();
    });
    treeView.on('itemremove', function() {
    	tree.doGray();
    });
    tree.on('itemclick', function (me, rec, item, index, event, options) {
        if(rec.isExpanded()) {
      	  rec.collapse();
        } else {
      	  rec.expand();
        }
    });

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

    Ext.select('.MySched_checkbox_default_fake').on({
		'mouseover': function (e) {
			e.stopEvent();
			changePublicDefaultHighlight(e);
      	},
      	'mouseout': function (e) {
        	e.stopEvent();
			changePublicDefaultHighlight(e);
      	},
      	'click': function (e) {
        	if (e.button == 0) //links Klick
        	{
          		e.stopEvent();
          		setPublicDefaultStatus(e);
        	}
      	}
    });
});
