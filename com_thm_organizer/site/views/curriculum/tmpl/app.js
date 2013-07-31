/*globals Ext, loading_icon, Curriculum */
/*jshint strict: false */
function App (menuID, programID, horizontalGroups, languageTag, width, height,
              hPanelHeaderColor, hPanelColor, iPanelColor, mPanelColor,
              itemWidth, itemHeight, itemColor, titleCut, titleLength, maxItems,
              spacing, horizontalSpacing, inlineSpacing, modalSpacing)
{
    var self = this;
    this.menuID = menuID;
    this.programID = programID;
    this.horizontalGroups = horizontalGroups;
    this.languageTag = languageTag;
    this.width = width;
    this.height = height;
    this.hPanelHeaderColor = hPanelHeaderColor.substring(1);
    this.hPanelColor = hPanelColor.substring(1);
    this.iPanelColor = iPanelColor.substring(1);
    this.mPanelColor = mPanelColor.substring(1);
    this.itemWidth = itemWidth;
    this.itemHeight = itemHeight;
    this.itemColor = itemColor.substring(1);
    this.titleCut = titleCut;
    this.titleLength = titleLength;
    this.maxItems = maxItems;
    this.spacing = spacing;
    this.horizontalSpacing = horizontalSpacing;
    this.inlineSpacing = inlineSpacing;
    this.modalSpacing = modalSpacing;

    /**
     * Add a Note icon
     */
    App.prototype.addEcollab = function(id, link)
    {
        var tooltipHeading = (languageTag === 'de') ? 'Info' : 'Info';
        var tooltipAddInfo = (languageTag === 'de') ? '<br>Klicken f&uuml;r weitere Informationen' : 'Click for additional information';

        var noteImage = new Ext.create(
            'Ext.Component',
            {
                xtype : 'box',
                autoEl : {
                    tag : 'a',
                    href : link,
                    target: "_blank",
                    children : [ { tag : 'img', id : 'collab-image', cls : 'tooltip', src : 'collab_icon' } ]
                },
                renderTo : 'toolcontainer-' + id
            }
        );

        Ext.ToolTip({
            target : noteImage.el.id,
            title : tooltipHeading,
            html : '<br>' + link + "<br>" +tooltipAddInfo,
            dismissDelay:0,
            autoHide : true
        });
    };

    /**
     * Returns a container which represents a compulsory coursepool
     */
    App.prototype.addInlinePool = function(color, leftMargin, topMargin)
    {
        return Ext.create(
            'Ext.container.Container',
            {
                xtype : 'panel',	
                layout : 'anchor',
                border: false,
                margin : (topMargin - 1) + " 0 0 "+ (leftMargin - 1),
                cls: 'compulsory-pool',
                style : { "background-color" : "#" + color }
            }
        );
        return compulsoryPool;
    };

    /**
     * Returns a container which contains the title of a given pool (type 0)
     */
    App.prototype.addInlinePoolText = function(pool)
    {
        var subjectTitle = self.getTitleLabel(pool);
        var num = Number(pool.horizontalGroups_programIDs_id);
        var linkColor = self.contrastColor(pool.color);

        /* specify the describing text */
        var label;
        if(pool.min_creditpoints === pool.max_creditpoints)
        {
            if(languageTag === "de")
            {
                label = " insgesamt " + pool.min_creditpoints + " CrP";
            }
            else
            {
                label = " overall " + pool.min_creditpoints + " CrP";
            }

        }
        else
        {
            if(languageTag === "de")
            {
                label = " insgesamt " + pool.min_creditpoints + " CrP bis " + pool.max_creditpoints + " CrP";
            }
            else
            {
                label = " overall " + pool.min_creditpoints + " CrP to " + pool.max_creditpoints + " CrP";
            }
        }

        var html = "<span style='color: " + linkColor +";' class='course_title' id='course_title-" + pool.horizontalGroups_programIDs_id  + "'>";
        html += "<b>" + subjectTitle + ":</b>" + label;
        html += "<span style='margin-left:3px' class='toolcontainer' align='center' id='toolcontainer-" + pool.horizontalGroups_programIDs_id + "'>";
        html += "</span></span>";

        /* create the container */
        var container = Ext.create('Ext.container.Container', {
            layout : 'hbox',
            minHeight : 17,
            margin: "0 0 3 4",
            cls: "comp_pool_text_container",
            html: html ,
            listeners : {
                afterrender : function(c)
                {
                    var tooltipContent = pool.title;

                    if(pool.note)
                    {
                        tooltipContent += "<br><br>" + pool.note;
                    }

                    if(pool.note !== "" ||  pool.menu_link !== 0)
                    {
                        self.addNote(pool.mappingID, tooltipContent, pool.menu_link);
                    }
                }
            }

        });

        return container;
    };

    /**
     * Adds a placeholder icon
     */
    App.prototype.addPlacehodlerIcon = function(container_id)
    {
        var tooltipImage = new Ext.create(
            'Ext.Component',
            {
                    xtype : 'box',
                    autoEl : {
                            tag : 'img',
                            id : 'tooltip-image',
                            src : 'place_holder_icon'
                    },
                    renderTo : 'toolcontainer-' + container_id
            }
        );
    };

   /**
     * Adds a Container for comp. pools
     */
    App.prototype.addPoolContainer = function(color)
    {
        return  Ext.create(
            'Ext.container.Container',
            {
                layout : 'hbox',
                width: 0,
                height : 66,
                defaultMargins: {top: 0, right: 10, bottom: 0, left: 0},
                bodyStyle : { "background-color" :  "#" + color },
                listeners : {
                    afterrender : function(c)
                    {
                        /* set the correct size of the container */
                        var width = 0;
                        var height = 0;

                        for (var k = 0; k < this.items.length; k++)
                        {
                            width += this.items.get(k).getWidth() + 4;

                            if (this.items.get(k).getHeight() > height)
                            {
                                height = this.items.get(k).getHeight() + 4;
                            }
                        }
                        if (width > c.width)
                        {
                            this.setWidth(width);
                        }
                        if (height > c.height)
                        {
                            this.setHeight(height);
                        }
                    }
                }
            }
        );
    };

    /**
     * Adds a pool icon to the item
     * 
     */
    App.prototype.addPoolIcon = function(container_id, title, schedule)
    {
        var tooltipImage = new Ext.create(
            'Ext.Component',
            {
                xtype : 'box',
                autoEl : {
                    tag : 'img',
                    id : 'test',
                    cls : 'comp_pool_icon',
                    src : 'comp_pool_icon'
                },
                renderTo : 'pool_icon_container-' + container_id
            }
        );
        return tooltipImage.id;
    };

    /**
     * Adds a responsible icon
     */
    App.prototype.addResponsibleIcon = function(id, responsible_link, responsible_name, responsible_picture)
    {
        var heading = (languageTag === 'de') ? 'Modulverantwortliche:' : 'Responsible:';
        var responsilbe_tooltip = null;
        var tooltipAddInfo  = "";

        if(responsible_link)
        {
            tooltipAddInfo = (languageTag === 'de') ? 'Klicken f&uuml;r weitere Informationen' : 'Click for additional information';
            responsilbe_tooltip = new Ext.create(
                'Ext.Component',
                {
                    xtype : 'box',
                    autoEl : {
                        tag : 'a',
                        href : responsible_link,
                        children : [ { tag : 'img', src : responsible_icon } ]
                    },
                    renderTo : 'toolcontainer-' + id
                }
            );
        } 
        else
        {
            responsilbe_tooltip = new Ext.create(
                'Ext.Component',
                {
                        xtype : 'box',
                        autoEl : {
                                cls : 'tooltip',
                                        tag : 'img',
                                        src : responsible_icon

                        },
                        renderTo : 'toolcontainer-' + id
                }
            );	
        }

        Ext.ToolTip({
            target : responsilbe_tooltip.el.id,
            title : heading ,
            html : "<hr style='width:130px;visibility: hidden'>"+responsible_name + "<div style='margin-top:4px;margin-bottom:4px;'align='center'>"+ (responsible_picture ? responsible_picture : "")+ "</div><div>"+tooltipAddInfo+"</div><br>",
            dismissDelay:0,
            autoHide : true
        });
    };

    /**
     * Adds a title icon
     * 
     * @param   {string}  id  the subject's id
     * @param   {string}  title  the subject's title
     */
    App.prototype.addTitleToolTip = function(id, title)
    {
        Ext.ToolTip({
            target : 'course_title-' + id ,
            html : title, 
            dismissDelay:0,
            autoHide : true
        });
    };

    /**
     * Adds a tooltip
     */
    App.prototype.addToolTip = function(container_id, title, schedule)
    {
        var tooltipAddInfo = null;
        
        if(schedulerLink !== "")
        {
            tooltipAddInfo = (languageTag === 'de') ? '<br>Klicken f&uuml;r weitere Informationen' : 'Click for additional information';
        }

        var tooltipImage = new Ext.create(
            'Ext.Component',
            {
                xtype : 'box',
                autoEl : {
                        tag : 'a',
                        href : schedulerLink,
                        children : [ { tag : 'img', id : 'tooltip-image', cls : 'tooltip', src : 'scheduler_icon' } ]
                },
                renderTo : 'toolcontainer-' + container_id
            }
        );

        Ext.ToolTip({
            target : tooltipImage.el.id,
            title : 'Details:',
            html : schedule +tooltipAddInfo,
            dismissDelay:0,
            autoHide : true
        });
    };

    App.prototype.ajaxHandler = function(response)
    {
        var basePanel = self.getBasePanel();
        var program = Ext.decode(response.responseText);

        /* iterate over first order children of the program's curriculum */
        for ( var firstOrder in program.children)
        {
            if (program.children.hasOwnProperty(firstOrder))
            {
                var firstOrderPool = self.getFirstOrderPool(program.children[firstOrder]);
                firstOrderPool.color = firstOrderPool.color? firstOrderPool.color : self.hPanelColor;
                var items = program.children[firstOrder].children;
                if (items.length === 0)
                {
                    continue;
                }
                var container = self.getContainer(firstOrderPool.color? firstOrderPool.color : hPanelColor);
                var textContainer = self.getTextContainer(program.children[firstOrder]);
                firstOrderPool.add(textContainer);

                /* iterate over 2nd order children */
                for ( var secondOrder in items )
                {
                    if (items.hasOwnProperty(secondOrder))
                    {
                        var item = self.getAsset(items[secondOrder], program.children[firstOrder], 2, 2);

                        if (container.items.length >= self.maxItems)
                        {
                            container = self.getContainer(hPanelColor);
                        }
                        container.add(item);

                        firstOrderPool.add(container);
                    }
                }
                basePanel.add(firstOrderPool);
            }
        }

        basePanel.doLayout();

        var sele = "loading";

        Ext.Element.get(sele).destroy();
        sele = "curriculum";
        basePanel.render(Ext.Element.get(sele));
    };

    /**
     * Calculate the contrasting color
     * Source: http://www.nbdtech.com/Blog/archive/2008/04/27/Calculating-the-Perceived-Brightness-of-a-Color.aspx
     * 
     * @param   {string}  color  the background color of the item
     */
    App.prototype.contrastColor = function(color)
    {
        var red = parseInt(color.substring(0, 2), 16);
        var green = parseInt(color.substring(2, 4), 16);
        var blue = parseInt(color.substring(4), 16);
        var percievedRed = Math.pow(red, 2) * 0.241;
        var percievedblue = Math.pow(blue, 2) * 0.068;
        var percievedgreen = Math.pow(green, 2) * 0.691;
        var value = Math.sqrt( percievedRed + percievedblue + percievedgreen );
        return value < 130 ? "FFFFFF" : "000000";
    };

    /**
     * Determine the type of an asset and delegate to the corresponding method
     */
    App.prototype.getAsset = function(item, parent, leftMargin, topMargin)
    {
        if(item !== undefined)
        {
            var title = (self.languageTag === 'de')? item.name_de : item.name_en;
            var courseID = (item.externalID === '')? item.lsfID : item.externalID;
            var tooltip = title + " (" + courseID + ")";
            if(item.children !== undefined)
            {
                // Pool
                if(item.isInline)
                {
                    return self.getInlinePool(item, leftMargin, topMargin);
                }
                else
                {
                    return self.getModalPool(item, self.getModalContent(item), item.title, leftMargin, topMargin);	
                }
            }
            else
            {
                return self.getSubject(item, tooltip, leftMargin, topMargin);
            }
        }
        else
        {
            return self.getSpacer('#666666', leftMargin, topMargin);
        }
    };

    /**
     * Returns the base panel for curriculum display
     */
    App.prototype.getBasePanel = function()
    {
        return Ext.create(
            'Ext.container.Container',
            {
                width : self.width,
                height :self.height,
                layout : 'anchor',
                id : 'curriculum',
                cls: 'curriculum',
                autoScroll : true,
                autoHeight: true,
                border: false
            }
        );
    };

    /**
     * Adds a container
     * 
     * @param   {string}  color  the background color of the item
     */
    App.prototype.getContainer = function(color)
    {
        return Ext.create(
            'Ext.container.Container',
            {
                layout : 'hbox',
                width: 0,
                height : 66,
                bodyStyle : { "background-color" :  color },
                defaultMargins : {top: 0, right: 0, bottom: 0, left: 0},
                listeners : {
                    afterrender : function(c)
                    {
                        /* set the correct size of the container */
                        var width = 0;
                        var height = 0;

                        for (var k = 0; k < this.items.length; k++)
                        {
                            width += this.items.get(k).getWidth() + 4;

                            if (this.items.get(k).getHeight() > height)
                            {
                                height = this.items.get(k).getHeight() + 4;
                            }
                        }

                        if (width > c.width)
                        {
                            this.setWidth(width);
                        }

                        if (height > c.height)
                        {
                            this.setHeight(height);
                        }
                    }
                }
            }
        );
    };

    /**
     * Returns a Panel, which represents a first order child subject pool
     * 
     * @param   {object}  horizontalGroup  the first order child of the program
     */
    App.prototype.getFirstOrderPool = function(horizontalGroup)
    {
        var contrastColor = self.contrastColor(horizontalGroup.color ? horizontalGroup.color : self.hPanelColor);

        var textColorStyle = ' color: ' + contrastColor + ';';
        var title = (self.languageTag === "de")? horizontalGroup.name_de : horizontalGroup.name_en;

        // Create the window object
        return Ext.create(
            'Ext.panel.Panel',
            {
                xtype : 'panel',
                title : '<span style="font-size: 12px;' + textColorStyle +'">' + title + '</span>',
                layout : 'anchor',
                cls: 'semester',
                margin : '8 0 0 0',
                autoHeight: true,
                collapsible : true,
                border : false,
                listeners : {
                    afterrender : function(c)
                    {
                        c.header.addCls('horizontalPanel');
                        document.getElementById(c.el.id).firstChild.style.background = "#"+ horizontalGroup.color;
                        document.getElementById(c.el.id).lastChild.style.background = "#"+ horizontalGroup.color;
                        c.body.applyStyles("background-color:" + hPanelColor);	
                    }
                }
            }
        );
    };

    /**
     * Determine the childs of a pool and put them into a ExtJS container
     */
    App.prototype.getInlinePool = function(pool, leftMargin, topMargin)
    {
        pool.color = pool.color? pool.color : self.iPanelColor;

        /* get the pool container with a certain background color and margins */
        var comPool = self.addInlinePool(pool.color,  leftMargin, topMargin);

        /* get the container element for supporting multi-row layouting */
        var container = self.addPoolContainer(pool.color);

        /* apply further processing, if the pool has assigned children */
        if(pool.children)
        {
            for (var i = 0, len = pool.children.length; i < len; i++)
            {
                var asset;
                if(i === 0)
                {
                    asset = self.getAsset(pool.children[i], pool, 1, 1);
                }
                else
                {
                    asset = self.getAsset(pool.children[i], pool, 2, 1);
                }

                /* apply the correct multi-row behaviour */
                if(container.items.length < self.maxItems)
                {
                    container.add(asset);
                }
                else
                {
                    container = self.getContainer(pool.color);
                    container.add(self.getAsset(pool.children[i], pool, 1, 1 ));
                }

                comPool.add(container);
            }
        } 

        /* attach the name and related tooltip above the actual pool content */
        comPool.add(self.addInlinePoolText(pool));

        return comPool;
    };

    /**
     *  Determine the childs of a pool and put them into a ExtJS window
     */
    App.prototype.getModalContent = function (pool)
    {
        var item;
        pool.color = pool.color? pool.color : self.mPanelColor;
        var container = self.getContainer(pool.color);

        /* create the window for the childs of the given asset */
        var window = self.getModalPoolWindow(pool.abbreviation, pool.min_creditpoints, pool.max_creditpoints, pool.color);

        /* get the children of this asset */
        var childs = pool.children;
        var compPoolFlag = false;

        /* return an empty window, if a pool has yet no children */
        if(typeof pool.children === "undefined")
        {
            window.add(container.add(self.getSpacerItem()));
            return window;
        }

        /* iterate each child of the current asset */
        for (var i = 0, len = pool.children.length; i < len; ++i)
        {
            item = self.getAsset(pool.children[i], pool);

            if (i === 0)
            {
                item = self.getAsset(pool.children[i], pool, 2, 2);
            }
            else
            {
                item = self.getAsset(pool.children[i], pool, 2, 2);
            }

            /* apply the correct multi-row behaviour */
            if(container.items.length < self.maxItems)
            {
                container.add(item);
            }
            else
            {
                item = self.getAsset(pool.children[i], pool, 2, 2);
                container = self.getContainer(item.color);
                container.add(item);
            }

            window.add(container);
        }

        return window;
    };

    /**
     * Returns a Panel which represents an elective course pool
     */
    App.prototype.getModalPool = function(pool, window, tooltip, leftMargin, topMargin)
    {
        var creditpoints;
        if(pool.min_creditpoints === pool.max_creditpoints)
        {
            creditpoints = pool.min_creditpoints;
        }
        else
        {
            creditpoints = pool.min_creditpoints + "-" + pool.max_creditpoints;
        }
        pool.color = pool.color? pool.color : self.mPanelColor;

        /* determine the title */
        var poolTitle = self.getTitleLabel(pool);

        /* calculate the appropriate font color, depening on the chosed pool color */
        var headerFontColor = self.contrastColor(pool.color);
        var linkColor = self.contrastColor(pool.color);
        var poolLabel = ((self.cutTitle === 1) ? (pool.short_title.substring(0, titleLength) + "...") : poolTitle);
        var titleSpans = "<span style='color:" + headerFontColor + ";'class='elective_pool_title course_title' >" + pool.abbreviation + "</span>";
        titleSpans += "<span style='color:" + headerFontColor + ";'class='elective_pool_creditpoints'>" + creditpoints + " CrP" + "</span>";

        /* create the panel */
        return Ext.create(
            'Ext.panel.Panel',
            {
                xtype : 'panel',	
                cls : "elective_pool",
                title : titleSpans,
                width : itemWidth,
                height : 65,
                margin : topMargin +" 0 0 "+ leftMargin,
                listeners : {
                    afterrender : function(c)
                    {
                        c.header.addCls('course_panel_header');
                        self.addTitleToolTip(pool.horizontalGroups_programIDs_id, tooltip);
                        document.getElementById(c.el.id).firstChild.style.background = "#" + pool.color;

                        if(pool.note !== "" || pool.menu_link !== 0)
                        {
                            self.addNote(pool.horizontalGroups_programIDs_id, pool.note, pool.menu_link);
                        }

                        var iconId  = self.addPoolIcon(pool.horizontalGroups_programIDs_id, tooltip);
                        var selector = "#" + iconId;

                        Ext.select(selector).on('click', function(e) { window.show(); });

                    }
                },
                bodyStyle : { "background-color" : pool.color },
                layout : { type : 'anchor', align : 'center' },
                items : [
                    {
                        xtype : 'container',
                        anchor : '100% 35%',
                        html : "<div id='course_title-" + pool.horizontalGroups_programIDs_id  + "' align='center' class='course-title-container'>" +
                               "<a style='color: "+ linkColor+";' href='#' class='course-title pool" + pool.horizontalGroups_programIDs_id + "' >" +
                               poolLabel + "</a>" + "</div>",
                        listeners : {
                            afterrender : function(c)
                            {
                                var selector = ".pool" + pool.horizontalGroups_programIDs_id;

                                Ext.select(selector).on('click', function(e) { window.show(); });
                            }
                        }
                    },
                    {
                        xtype : 'container',
                        anchor : '100% 35%',
                        border: true,
                        html : "<div class='pool_icon_container' align='center' id='pool_icon_container-" + pool.horizontalGroups_programIDs_id + "'></div>"
                    },
                    {
                        xtype : 'container',
                        anchor : '100% 30%',
                        html : "<div style='margin-left:35px;' class='toolcontainer' align='center' id='toolcontainer-" + pool.horizontalGroups_programIDs_id + "'></div>"
                    }
                ]

            }
        );
    };

    /**
     * Returns a window which will contain the content of a elective pool
     */
    App.prototype.getModalPoolWindow = function(title, min_crp, max_crp, header_color)
    {
        /* calculate the appropriate font color, depening on the chosed pool color */
        var color = self.contrastColor(self.hPanelHeaderColor);

        var titleSpan = "<span class='window_title' style='color: " + color + ";'>";
        titleSpan += title + " (Min: " + min_crp + " CrP, Max:" + max_crp + " CrP)"+ "</span> ";

        /* create the window object */
        var window = Ext.create(
            'Ext.window.Window',
            {
                title : titleSpan,
                cls : 'elective_pool_window',
                autoScroll : 'true',
                layout : 'anchor',
                closeAction : 'hide',
                modal : true,
                bodyStyle : { "background-color" : "white" },
                listeners : {
                    afterrender : function(c)
                    {
                        /* set the css class for the header panel */
                        c.header.addCls('elective_pool_window_header');

                        /* set the background color */
                        document.getElementById(c.header.id).firstChild.style.background = "#" + header_color;
                    }
                }
        });
        return window;
    };

    /**
     * Returns a container used for spacing
     */
    App.prototype.getSpacer = function(color, leftMargin, topMargin)
    {
        if (color === undefined)
        {
            color = "#666666";
        }
        else if (color.indexOf("#") === -1)
        {
            color = "#" + color;
        }
        
        if (leftMargin === undefined)
        {
            leftMargin = "2";
        }

        if (topMargin === undefined)
        {
            topMargin = "2";
        }

        /* creates the panel */
        return Ext.create(
            'Ext.panel.Panel',
            {
                xtype : 'panel',
                cls: 'dummy',
                bodyCls: 'dummy_body',
                margin : topMargin +" 0 0 "+ leftMargin,
                width : itemWidth,
                height : 65,
                border:false,
                bodyStyle : { "background-color" : color, "top": "-1px" }
            }
        );
    };

    /**
     * Returns a panel which represents a course
     */
    App.prototype.getSubject = function(subject, tooltipData, leftMargin, topMargin)
    {
        /* set the default margin behavoiur */
        if (leftMargin === undefined )
        {
            leftMargin = '2';
        }

        if (topMargin === undefined )
        {
            topMargin = '2';
        }
        subject.color = subject.color? subject.color : self.itemColor;

        var mappingID = subject.mappingID;
        var subjectTitle = this.getTitleLabel(subject);
        var headerColor = this.contrastColor(subject.color);
        var linkColor = this.contrastColor(subject.color);
        var abbreviation = (this.languageTag === 'de')? subject.abbreviation_de : subject.abbreviation_en;
        if (abbreviation === undefined)
        {
            abbreviation = '';
        }

        // SEF-Route
        var moduleDescriptionLink = "index.php?option=com_thm_organizer&view=subject_details&languageTag=" + languageTag +"&id=" + subject.id +"&Itemid=" + menuID;
        var moduleTitle = ((self.cutTitle === 1) ? (subjectTitle.substring(0, titleLength) + "...") : subjectTitle);
        var titleSpans = "<span style='color:" + headerColor + ";"+"'class='course_title'>" + abbreviation.substr(0,10) + "</span>";
        titleSpans += "<span style='color:" + headerColor + ";"+"'class='course_creditpoints'>" + subject.creditpoints + " CrP" + "</span>";
      
        /* creates the panel */
        var course = Ext.create(
            'Ext.panel.Panel',
            {
                xtype : 'panel',
                cls: 'course',
                bodyCls: 'course_body',
                title : titleSpans,
                width : itemWidth,
                height : 65,
                margin : topMargin + " 0 0 " + leftMargin,
                bodyStyle : { "background-color" : self.itemColor },
                listeners : {
                    afterrender : function(c)
                    {
                        /* set the css class for the header panel */
                        c.header.addCls('course_panel_header');

                        /* set the background color */
                        document.getElementById(c.el.id).firstChild.style.background = "#" + subject.color;

                        /* build the toolbar */

                        /* scheduler icon */
                        if(subject.schedule !== null)
                        {
                            self.addToolTip(mappingID, subject.title, subject.schedule);
                        }
                        else
                        {
                            self.addPlacehodlerIcon(mappingID);
                        }

                        /* responsible icon */
                        if(subject.teacherName !== "")
                        {
                            self.addResponsibleIcon(mappingID, subject.teacherLink, subject.teacherName, subject.teacherPicture); 
                        }
                        else 
                        {
                            self.addPlacehodlerIcon(mappingID);
                        }

                        /* ecollab icon */
                        if(subject.ecollabLink !== "")
                        {
                            self.addEcollab(mappingID, subject.ecollabLink);
                        }
                        else
                        {
                            self.addPlacehodlerIcon(mappingID);
                        }

                        /* title tooltip */
                        self.addTitleToolTip(mappingID, tooltipData);
                    }
                },
                layout : {
                    type : 'anchor',
                    align : 'center'
                },
                items : [
                    {
                        xtype : 'container',
                        anchor : '100% 70%',
                        html : "<div id='course_title-" + mappingID  + "' align='center' class='course-title-container'>" +
                               "<a style='color:" + linkColor + ";" + "'class='course-title' href='" + moduleDescriptionLink +
                               "'>" + moduleTitle + "</a>" + "</div>"
                    },
                    {
                        xtype : 'container',
                        anchor : '100% 30%',
                        html : "<div align='center' class='toolcontainer' id='toolcontainer-"+ mappingID + "'></div>",
                        margin : '-3 0 0 0'
                    }
                ]
            }
        );
        return course;
    };

    /**
     * Returns a container which contains the title of a given pool
     * 
     * @param   {object}  group  the group for which the text container is being created
     */
    App.prototype.getTextContainer = function(group)
    {
        var linkColor = self.contrastColor(group.color);
        var text = (languageTag === 'de')? group.name_de : group.name_en;
        var html = "<span style='color: " + linkColor + ";' class='course_title'>" + text + "</span>";
        return Ext.create('Ext.container.Container',
        {
            layout : 'hbox',
            cls: "semester_text_container",
            html: html
        });
    };

    /**
     * Determine the title of an item
     * 
     * @param   {object}  item  the item being iterated
     */
    App.prototype.getTitleLabel = function(item)
    {
        return (languageTag === 'de')? item.name_de : item.name_en;
    };

    App.prototype.performAjaxCall = function()
    {
        var sele = "loading";
        var image = new Ext.create(
            'Ext.Component',
            {
                xtype : 'box',
                autoEl : {
                    tag : 'a',
                    href : "",
                    children : [ {
                        tag : 'img',
                        id : 'responsible-image',
                        cls : 'tooltip',
                        src : loadingIcon
                    } ]
                },
                renderTo : sele
            }
        );
        var requestURL = 'index.php?option=com_thm_organizer&view=curriculum_ajax&task=getCurriculum&format=raw&id=';
        requestURL += programID + '&Itemid=' + menuID + '&languageTag=' + languageTag;
        Ext.Ajax.request({
            url : requestURL,
            method : "GET",
            success : this.ajaxHandler
        });
    };
}