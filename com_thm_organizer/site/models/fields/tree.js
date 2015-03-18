/*global Ext, MySched, MySchedLanguage, changePublicDefaultHighlight, images */
/*jshint strict: false */

// TODO: Something in this function destroys the tree panel and nothing will be shown
Ext.override(Ext.tree.Column,
//Ext.define('TreeColumOverride',
{
    //override: 'Ext.tree.Column',
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
    //console.log(record);
            if(record.isLeaf())
            {
                if(record.raw.publicDefault === undefined || record.raw.publicDefault === '')
                {
                    record.raw.publicDefault = 'notdefault';
                }
                checkboxText += '<input id="'+record.data.id+'_default" type="hidden" value="'+record.raw.publicDefault+'" role="checkbox" />';
                checkboxText += '<img id="'+record.data.id+'_default_fake" class="MySched_checkbox_default_fake" src="'+images[record.raw.publicDefault]+'">';
            }

            if(record.data.checked)
            {
                checkboxText += '<input id="' + record.data.id + '" type="hidden" value="' + record.data.checked + '" role="checkbox" class="{0}" {1} />';
                checkboxText += '<img id="' + record.data.id + '_fake" class="MySched_checkbox_fake" src="' + images[record.data.checked] + '">';
            }

            //var formattedValue = origRenderer.apply(origScope, arguments);
            var formattedValue = record.data.text;
            var href = record.get('href');
            var target = record.get('hrefTarget');
            var cls = record.get('cls');

            while (record)
            {
                //console.log(record);
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
                //buf.unshift(record.data.text);
                record = record.parentNode;
                //console.log(buf);
            }
            if (href)
            {
                formattedValue = format('<a href="{0}" target="{1}">{2}</a>', href, target, formattedValue);
            }
            if (cls)
            {
                metaData.tdCls += ' ' + cls;
            }
            //console.log(arguments);
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

function changePublicDefaultHighlight (event)
{
    //Not implemented ( Keine macht mir nen Highlighted Icon :( )
}

function setPublicDefaultStatus(event)
{

    var clickBox = event.getTarget('.MySched_checkbox_default_fake', 5, true);
    var inputField = clickBox.dom.getPrevious();
    var newStatus = null;
    var treeRoot = tree.getRootNode();

    var recordID = inputField.id.replace("_default", "");
    var record = treeRoot.findChild('id', recordID, true);

    if(inputField.value === "default")
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
           var otherRecordID = item.id.replace("_default_fake", "");
           var nodeTemp = treeRoot.findChild('id', otherRecordID, true);
           nodeTemp.raw.publicDefault = "notdefault";
        });
        newStatus = "default";
    }

    inputField.value = newStatus;
    clickBox.dom.src = images[inputField.value];

    record.raw.publicDefault = inputField.value;
}

function setStatus(event)
{
    console.log("setStatus");
    var clickBox = event.getTarget('.MySched_checkbox_fake', 5, true);
    var inputField = clickBox.dom.getPrevious();

    var record = tree.getRootNode().findChild('id',inputField.id,true);

    if(record.isLeaf() === true)
    {
        if(inputField.value === "unchecked")
        {
            inputField.value = "checked";
            clickBox.dom.src = images.base+inputField.value+"_highlighted.png";
        }
        else if(inputField.value === "checked")
        {
            inputField.value = "hidden";
            clickBox.dom.src = images.base+inputField.value+"_highlighted.png";
        }
        else if(inputField.value === "hidden")
        {
            inputField.value = "unchecked";
            clickBox.dom.src = images.base+inputField.value+"_highlighted.png";
        }
    }
    else
    {
        if(inputField.value === "unchecked")
        {
            inputField.value = "checked";
            clickBox.dom.src = images.base+inputField.value+"_highlighted.png";
        }
        else if(inputField.value === "checked")
        {
            inputField.value = "selected";
            clickBox.dom.src = images.base+inputField.value+"_highlighted.png";
        }
        else if(inputField.value === "selected")
        {
            inputField.value = "intermediate";
            clickBox.dom.src = images.base+inputField.value+"_highlighted.png";
        }
        else if(inputField.value === "intermediate")
        {
            inputField.value = "hidden";
            clickBox.dom.src = images.base+inputField.value+"_highlighted.png";
        }
        // hide
        else
        {
            inputField.value = "unchecked";
            clickBox.dom.src = images.base+inputField.value+"_highlighted.png";
        }
    }
    record.data.checked = inputField.value;
}
// TODO check if this is correct => changed Ext.data.Tree.prototype.check into Ext.data.TreeModel.prototype.check

Ext.data.TreeStore.prototype.check = function(state, descend, bulk)
{
    //console.log(state);
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
    if(!Ext.isDefined(checkedArr) || checkedArr === null)
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
    var clickBox;

    if( typeof node === 'undefined' )
    {
        node = this.getRootNode();
    }
    var id = node.data.id+"_fake";
    clickBox = Ext.dom.Query.selectNode("[id="+id+"]", tree.dom);
    //console.log(Ext.isDefined(clickBox));
    if(clickBox)
    {
        clickBox.setStyle('opacity', '1');
        clickBox.setStyle('border', 'none');
    }
    var gray = false;
    if(node.hasChildNodes() === true)
    {
        node.childNodes.each(function(v, k) {
            var state = null;
            if(v.isVisible())
            {
                state = tree.doGray(v);
                if(state === true)
                {
                    gray = state;
                }
                return;
            }
            state = tree.needGray(v);
            if(state === true)
            {
                gray = state;
            }
        });
    }

    if(gray === true)
    {
        clickBox = Ext.DomQuery.selectNode("[id="+id+"]", tree.dom);
        if(clickBox)
        {
            clickBox.setStyle('opacity', '0.4');
            clickBox.setStyle('border', '1px solid gray');
        }
    }

    if(node.data.checked === "checked" || node.data.checked === "selected" || node.data.checked === "intermediate" || node.data.checked === "hidden")
    {
        gray = true;
    }

    return gray;
};

Ext.tree.Panel.prototype.needGray = function (node)
{
    if(node.data.checked === "checked" || node.data.checked === "selected" || node.data.checked === "intermediate" || node.data.checked === "hidden")
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
            }
        });
    }
    return returnResult;
};

var tree = null;

Ext.onReady(function()
{
    Ext.QuickTips.init();

    tree = Ext.create(
        'Ext.tree.Panel',
        {
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
        }
    );

    // render the tree
    //tree.render('tree-div');

    var treeView = tree.getView();
    tree.on('itemappend', function(me, node, refNode, eOpts){
            /*console.log(me);
            console.log(eOpts);
            console.log(node);*/
            checkBoxEvents(node.parenNode);
            tree.doGray();
        }
    );
    treeView.on('itemadd', function(records, index, node, eOpts)
    {
        console.log("on item add");
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
    //console.log(node);
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
