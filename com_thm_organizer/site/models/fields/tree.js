/*global Ext, MySched, MySchedLanguage, changePublicDefaultHighlight, images */
/*jshint strict: false */
Ext.override(Ext.tree.Column,
{
    initComponent: function()
    {
        var origRenderer = this.renderer || this.defaultRenderer;
        var origScope  = this.scope || window;

        this.renderer = function(value, metaData, record, rowIdx, colIdx, store, view)
        {
            var buf = [];
            var format = Ext.String.format;
            var depth = record.getDepth();
            var treePrefix  = Ext.baseCSSPrefix + 'tree-';
            var elbowPrefix = treePrefix + 'elbow-';
            var expanderCls = treePrefix + 'expander';
            var imgText = '<img src="{1}" class="{0}" />';

            var checkboxText = "";

            if(record.raw && record.raw.publicDefault)
            {
                checkboxText += '<input id="'+record.data.id+'_default" type="hidden" value="'+record.raw.publicDefault+'" role="checkbox" />';
                checkboxText += '<img id="'+record.data.id+'_default_fake" class="MySched_checkbox_default_fake" src="'+images[record.raw.publicDefault]+'">';
            }

            if(record.data.checked)
            {
                checkboxText += '<input id="' + record.data.id + '" type="hidden" value="' + record.data.checked + '" role="checkbox" class="{0}" {1} />';
                checkboxText += '<img id="' + record.data.id + '_fake" class="MySched_checkbox_fake" src="' + images[record.data.checked] + '">';
            }

            var formattedValue = origRenderer.apply(origScope, arguments);
            var href = record.get('href');
            var target = record.get('hrefTarget');
            var cls = record.get('cls');

            while (record)
            {
                if (!record.isRoot() || (record.isRoot() && view.rootVisible))
                {
                    if (record.getDepth() === depth)
                    {
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
                            if (record.get('checked'))
                            {
                                metaData.tdCls += (' ' + Ext.baseCSSPrefix + 'tree-checked');
                            }
                        }
                        if (record.isLast()) {
                            if (record.isExpandable())
                            {
                                buf.unshift(format(imgText, (elbowPrefix + 'end-plus ' + expanderCls), Ext.BLANK_IMAGE_URL));
                            }
                            else
                            {
                                buf.unshift(format(imgText, (elbowPrefix + 'end'), Ext.BLANK_IMAGE_URL));
                            }

                        }
                        else
                        {
                            if (record.isExpandable())
                            {
                                buf.unshift(format(imgText, (elbowPrefix + 'plus ' + expanderCls), Ext.BLANK_IMAGE_URL));
                            }
                            else
                            {
                                buf.unshift(format(imgText, (treePrefix + 'elbow'), Ext.BLANK_IMAGE_URL));
                            }
                        }
                    }
                    else
                    {
                        if (record.isLast() || record.getDepth() === 0)
                        {
                            buf.unshift(format(imgText, (elbowPrefix + 'empty'), Ext.BLANK_IMAGE_URL));
                        }
                        else if (record.getDepth() !== 0)
                        {
                            buf.unshift(format(imgText, (elbowPrefix + 'line'), Ext.BLANK_IMAGE_URL));
                        }
                    }
                }
                record = record.parentNode;
            }
            if (href)
            {
                formattedValue = format('<a href="{0}" target="{1}">{2}</a>', href, target, formattedValue);
            }
            if (cls)
            {
                metaData.tdCls += ' ' + cls;
            }
            return buf.join("") + formattedValue;
        };

        this.callParent(arguments);
    }
});

function changeIconHighlight (event)
{

    var elImg = event.getTarget('.MySched_checkbox_fake', 5, true);
    var elInput = elImg.dom.getPrevious();

    if(event.type === "mouseover")
    {
        elImg.dom.src = images.base+elInput.value+"_highlighted.png";
    }
    else
    {
        elImg.dom.src = images.base+elInput.value+".png";
    }
}

function setPublicDefaultStatus(event)
{

    var elImg = event.getTarget('.MySched_checkbox_default_fake', 5, true);
    var elInput = elImg.dom.getPrevious();
    var newStatus = null;
    var treeRoot = tree.getRootNode();

    var record = treeRoot.findChild('id',elInput.id.replace("_default", ""),true);

    if(elInput.value === "default")
    {
        newStatus = "notdefault";
    }
    else
    {
        var nodes = Ext.query('.MySched_checkbox_default_fake');
        Ext.each(nodes, function (item, index, allItems)
        {
           item.src = images.notdefault;
           item.getPrevious().value = "notdefault";
           var nodeTemp = treeRoot.findChild('id',item.id.replace("_default_fake", ""),true);
           nodeTemp.raw.publicDefault = "notdefault";
        });
        newStatus = "default";
    }

    elInput.value = newStatus;
    elImg.dom.src = images[elInput.value];

    record.raw.publicDefault = elInput.value;
}

function setStatus(event)
{

    var elImg = event.getTarget('.MySched_checkbox_fake', 5, true);
    var elInput = elImg.dom.getPrevious();

    var record = tree.getRootNode().findChild('id',elInput.id,true);

    if(record.isLeaf() === true)
    {
        if(elInput.value === "unchecked")
        {
            elInput.value = "checked";
            elImg.dom.src = images.base+elInput.value+"_highlighted.png";
        }
        else if(elInput.value === "checked")
        {
            elInput.value = "unchecked";
            elImg.dom.src = images.base+elInput.value+"_highlighted.png";
        }
    }
    else
    {
        if(elInput.value === "unchecked")
        {
            elInput.value = "checked";
            elImg.dom.src = images.base+elInput.value+"_highlighted.png";
        }
        else if(elInput.value === "checked")
        {
            elInput.value = "selected";
            elImg.dom.src = images.base+elInput.value+"_highlighted.png";
        }
        else if(elInput.value === "selected")
        {
            elInput.value = "intermediate";
            elImg.dom.src = images.base+elInput.value+"_highlighted.png";
        }
        //intermediate
        else
        {
            elInput.value = "unchecked";
            elImg.dom.src = images.base+elInput.value+"_highlighted.png";
        }
    }
    record.data.checked = elInput.value;
}

Ext.data.Tree.prototype.check = function(state, descend, bulk)
{
    this.data.checked = state;
    if(this.ui.checkbox)
    {
        this.ui.checkbox.checked = state;
    }

    if( descend && !this.isLeaf() )
    {
        var cs = this.childNodes;
        for(var i = 0; i < cs.length; i++)
        {
            cs[i].check(state, true, true);
        }
    }

    if(!bulk)
    {
        this.fireEvent('check', this, state);
    }
};

Ext.tree.Panel.prototype.getChecked = function(node, checkedArr)
{
    var checked;
    if(checkedArr === null || !Ext.isDefined(checkedArr))
    {
        checked = {};
    }
    else
    {
        checked = checkedArr;
    }
    var i;
    if( typeof node === 'undefined' )
    {
        node = this.getRootNode();
    }
    var attrname, checkedChildren;
    if( node.data.checked !== "unchecked" && node.data.checked !== null)
    {
        var nodeID = node.data.id;
        checked[nodeID] = node.data.checked;
        if(!node.isLeaf())
        {
            for( i = 0; i < node.childNodes.length; i++ )
            {
                checkedChildren = this.getChecked(node.childNodes[i]);
                for (attrname in checkedChildren)
                {
                    if (checkedChildren.hasOwnProperty(attrname))
                    {
                        checked[attrname] = checkedChildren[attrname];
                    }
                }
            }
        }
    }
    else
    {
        if( !node.isLeaf() )
        {
            for( i = 0; i < node.childNodes.length; i++ )
            {
                checkedChildren = this.getChecked(node.childNodes[i]);
                for (attrname in checkedChildren)
                {
                    if (checkedChildren.hasOwnProperty(attrname))
                    {
                        checked[attrname] = checkedChildren[attrname];
                    }
                }
            }
        }
    }
    return checked;
};

Ext.tree.Panel.prototype.getPublicDefault = function(node, checkedArr)
{
    var checked;
    if(checkedArr === null)
    {
        checked = {};
    }
    else
    {
        checked = checkedArr;
    }
    var i;
    if( typeof node === 'undefined' )
    {
        node = this.getRootNode();
    }

    if( Ext.isObject(node.raw) &&  node.raw.publicDefault === "default")
    {
        var nodeID = node.data.id;
        checked[nodeID] = node.raw.publicDefault;
    }
    else
    {
        if(!node.isLeaf())
        {
            for( i = 0; i < node.childNodes.length; i++ )
            {
                var checkedChildren = this.getPublicDefault(node.childNodes[i]);
                for (var attrname in checkedChildren)
                {
                    if (checkedChildren.hasOwnProperty(attrname))
                    {
                        checked[attrname] = checkedChildren[attrname];
                    }
                }
            }
        }
    }
    return checked;
};

Ext.tree.Panel.prototype.doGray = function(node)
{
    var elImg = null;

    if( typeof node === 'undefined' )
    {
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
    {
        node.childNodes.each(function(v, k) {
            if(v.isVisible())
            {
                var state = tree.doGray(v);
                if(state === true)
                {
                    gray = state;
                }
            }
            else
            {
            	var state = tree.needGray(v);
            	if(state === true)
                {
                    gray = state;
                    return;
                }
            }
        });
    }

    if(gray === true)
    {
        elImg = Ext.DomQuery.selectNode("[id="+id+"]", tree.dom);
        if(Ext.isDefined(elImg))
        {
            elImg.setOpacity(0.4);
            elImg.setStyle('border', '1px solid gray');
        }
    }

    if(node.data.checked === "checked" || node.data.checked === "selected" || node.data.checked === "intermediate")
    {
        gray = true;
    }

    return gray;
};

Ext.tree.Panel.prototype.needGray = function (node)
{
	if(node.data.checked === "checked" || node.data.checked === "selected" || node.data.checked === "intermediate")
    {
        return true;
    }
	
	var returnResult = false;
	
	if(node.hasChildNodes() === true)
    {
        node.childNodes.each(function(v, k) {   
        	if(returnResult === true)
        	{
        		return;
        	}
            var state = tree.needGray(v);
            if(state === true)
            {
            	returnResult = true;
            	return;
            }
        });
    }
	return returnResult;
}

var tree = null;

Ext.onReady(function()
{
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
        ddConfig: { enableDrag: true },
        layout: { type: 'fit' },
        root: {
            id: 'rootTreeNode',
            text: 'root',
            expanded: true,
            children: null
        }
    });

    // render the tree
    tree.render('tree-div');
 
    var treeView = tree.getView();
    treeView.on('itemadd', function(records, index, node, eOpts)
    {
        checkBoxEvents(node[0].getParent());
        tree.doGray();
    });
    tree.on('itemclick', function (me, rec, item, index, event, options)
    {
        if(rec.isExpanded())
        {
          rec.collapse();
        }
        else
        {
          rec.expand();
        }
    });
});

function checkBoxEvents(node)
{

    if(!Ext.isDefined(node))
    {
        node = tree.getRootNode();
    }

    var selectedFakeCheckboxes = Ext.select('.MySched_checkbox_fake', node);
    selectedFakeCheckboxes.removeAllListeners();
    selectedFakeCheckboxes.on(
    {
        'mouseover': function (e)
        {
            e.stopEvent();
            changeIconHighlight(e);
        },
        'mouseout': function (e)
        {
            e.stopEvent();
            changeIconHighlight(e);
        },
        'click': function (e)
        {
            if (e.button === 0)
            {
                e.stopEvent();
                setStatus(e);
                tree.doGray();
            }
        }
    });

    var selectedDefaultFakeCheckboxes = Ext.select('.MySched_checkbox_default_fake', node);
    selectedDefaultFakeCheckboxes.removeAllListeners();
    selectedDefaultFakeCheckboxes.on({
        'mouseover': function (e)
        {
            e.stopEvent();
            changePublicDefaultHighlight(e);
        },
        'mouseout': function (e)
        {
            e.stopEvent();
            changePublicDefaultHighlight(e);
        },
        'click': function (e)
        {
            if (e.button === 0)
            {
                e.stopEvent();
                setPublicDefaultStatus(e);
            }
        }
    });
}
