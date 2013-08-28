/*globals Ext, loadingIcon, teacherIcon, schedulerIcon, poolIcon, placeHolderIcon, ecollabIcon */
/*jshint strict: false */
function Curriculum (menuID, programID, horizontalGroups, languageTag, width, height,
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
    Curriculum.prototype.addECollabIcon = function(id, link)
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
                    children : [ { tag : 'img', id : 'collab-image', cls : 'tooltip', src : ecollabIcon } ]
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
    Curriculum.prototype.addInlinePool = function(color, leftMargin, topMargin)
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
    };

    /**
     * Returns a container which contains the title of a given pool (type 0)
     */
    Curriculum.prototype.addInlinePoolText = function(pool)
    {
        var subjectTitle = self.getTitleLabel(pool);
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

        var html = "<span style='color: " + linkColor +";' class='course_title' id='course_title-" + pool.id  + "'>";
        html += "<b>" + subjectTitle + ":</b>" + label;
        html += "<span style='margin-left:3px' class='toolcontainer' align='center' id='toolcontainer-" + pool.id + "'>";
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
                }
            }

        });
        return container;
    };

    /**
     * Adds a placeholder icon
     */
    Curriculum.prototype.addPlacehodlerIcon = function(container_id)
    {
        var tooltipImage = new Ext.create(
            'Ext.Component',
            {
                    xtype : 'box',
                    autoEl : {
                            tag : 'img',
                            id : 'tooltip-image',
                            src : placeHolderIcon
                    },
                    renderTo : 'toolcontainer-' + container_id
            }
        );
    };

   /**
     * Adds a Container for comp. pools
     */
    Curriculum.prototype.addPoolContainer = function(color)
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
    Curriculum.prototype.addPoolIcon = function(container_id, title, schedule)
    {
        var tooltipImage = new Ext.create(
            'Ext.Component',
            {
                xtype : 'box',
                autoEl : {
                    tag : 'img',
                    id : 'test',
                    cls : 'comp_pool_icon',
                    src : poolIcon
                },
                renderTo : 'pool_icon_container-' + container_id
            }
        );
        return tooltipImage.id;
    };

    /**
     * Adds a tooltip
     */
    Curriculum.prototype.addScheduleIcon = function(subjectID, subjectName, schedulerLink)
    {
        if(schedulerLink !== undefined)
        {
            var heading = (languageTag === 'de') ? 'Stundenplan:' : 'Schedule:';
            var tooltipAddInfo  = "", html;

            tooltipAddInfo = (languageTag === 'de') ? '<br>Klicken f&uuml;r weitere Informationen' : 'Click for additional information';

            Ext.create(
                'Ext.Component',
                {
                    xtype : 'box',
                    id : 'schedule-button-' + subjectID,
                    autoEl : {
                            tag : 'a',
                            href : schedulerLink,
                            children : [ { tag : 'img', id : 'tooltip-image', cls : 'tooltip', src : schedulerIcon } ]
                    },
                    renderTo : 'toolcontainer-' + subjectID
                }
            );

            html = "<hr style='width:130px; visibility: hidden;'>" + subjectName;
            html += "<div>"+tooltipAddInfo+"</div><br>";

            Ext.create(
                'Ext.tip.ToolTip',
                {
                    title : heading,
                    id: 'scheduletip-' + subjectID,
                    target : 'schedule-button-' + subjectID,
                    html : html,
                    dismissDelay:0,
                    autoHide : true
                }
            );
        }
    };

    /**
     * Adds a responsible icon
     */
    Curriculum.prototype.addTeacherIcon = function(id, teacherLink, teacherName)
    {
        var heading = (languageTag === 'de') ? 'Modulverantwortliche:' : 'Responsible:';
        var toolTip, tooltipAddInfo  = "", html;

        if(teacherLink)
        {
            tooltipAddInfo = (languageTag === 'de') ? 'Klicken f&uuml;r weitere Informationen' : 'Click for additional information';
            toolTip = Ext.create(
                'Ext.Component',
                {
                    xtype : 'box',
                    id : 'teacher-button-' + id,
                    autoEl : {
                        tag : 'a',
                        href : teacherLink,
                        children : [ { tag : 'img', src : teacherIcon } ]
                    },
                    renderTo : 'toolcontainer-' + id
                }
            );
        } 
        else
        {
            toolTip = Ext.create(
                'Ext.Component',
                {
                    xtype : 'box',
                    id : 'teacher-button-' + id,
                    autoEl : {
                            cls : 'tooltip',
                            tag : 'img',
                            src : teacherIcon

                    },
                    renderTo : 'toolcontainer-' + id
                }
            );    
        }
        html = "<hr style='width:130px; visibility: hidden;'>" + teacherName;
        html += "<div>"+tooltipAddInfo+"</div><br>";

        Ext.create(
            'Ext.tip.ToolTip',
            {
                title : heading,
                id: 'teachertip-' + id,
                target : 'teacher-button-' + id,
                html : html,
                dismissDelay:0,
                autoHide : true
            }
        );
    };

    /**
     * Adds a title icon
     * 
     * @param   {string}  id  the subject's id
     * @param   {string}  title  the subject's title
     */
    Curriculum.prototype.addTitleToolTip = function(id, title)
    {
        Ext.ToolTip({
            target : 'course_title-' + id ,
            html : title, 
            dismissDelay:0,
            autoHide : true
        });
    };

    Curriculum.prototype.ajaxHandler = function(response)
    {
        var program = Ext.decode(response.responseText),
            basePanel = self.getBasePanel();

        Ext.fly('programName').update(program.name);

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
    Curriculum.prototype.contrastColor = function(color)
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
    Curriculum.prototype.getAsset = function(item, parent, leftMargin, topMargin)
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
    Curriculum.prototype.getBasePanel = function()
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
    Curriculum.prototype.getContainer = function(color)
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
    Curriculum.prototype.getFirstOrderPool = function(pool)
    {
        if (!pool.color)
        {
            pool.color = '666666';
        }
        var contrastColor = self.contrastColor(pool.color);

        var textColorStyle = ' color: ' + contrastColor + ';';
        var title = (self.languageTag === "de")? pool.name : pool.name;

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
                        document.getElementById(c.el.id).firstChild.style.background = "#"+ pool.color;
                        document.getElementById(c.el.id).lastChild.style.background = "#"+ pool.color;
                        c.body.applyStyles("background-color:" + hPanelColor);    
                    }
                }
            }
        );
    };

    /**
     * Determine the childs of a pool and put them into a ExtJS container
     */
    Curriculum.prototype.getInlinePool = function(pool, leftMargin, topMargin)
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
    Curriculum.prototype.getModalContent = function (pool)
    {
        var item;
        pool.color = pool.color? pool.color : self.mPanelColor;
        var container = self.getContainer(pool.color);

        /* create the window for the childs of the given asset */
        var window = self.getModalPoolWindow(pool);

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

            item = self.getAsset(pool.children[i], pool, 2, 2);

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
    Curriculum.prototype.getModalPool = function(pool, window, tooltip, leftMargin, topMargin)
    {
        var creditpoints;
        if(pool.minCrP === pool.maxCrP)
        {
            creditpoints = pool.minCrP;
        }
        else
        {
            creditpoints = pool.minCrP + "-" + pool.maxCrP;
        }
        pool.color = pool.color? pool.color : '666666';

        /* calculate the appropriate font color, depening on the chosed pool color */
        var headerFontColor = self.contrastColor(pool.color),
            linkColor = self.contrastColor(self.iPanelColor),
            poolLabel = (self.cutTitle === 1) ? (pool.name.substring(0, titleLength) + "...") : pool.name,
            titleSpan1 = "<span style='color:" + headerFontColor + ";'class='modal_title course_title' >" + pool.abbreviation + "</span>",
            titleSpan2 = "<span style='color:" + headerFontColor + ";'class='modal_creditpoints'>" + creditpoints + " CrP</span>";

        /* create the panel */
        return Ext.create(
            'Ext.panel.Panel',
            {
                xtype : 'panel',    
                cls : "modal_pool",
                title : titleSpan1 + titleSpan2,
                width : self.itemWidth,
                height : self.itemHeight,
                margin : topMargin +" 0 0 "+ leftMargin,
                listeners : {
                    afterrender : function(c)
                    {
                        c.header.addCls('course_panel_header');
                        self.addTitleToolTip(pool.id, tooltip);
                        document.getElementById(c.el.id).firstChild.style.background = "#" + pool.color;

                        var iconId  = self.addPoolIcon(pool.id, tooltip);
                        var selector = "#" + iconId;

                        Ext.select(selector).on('click', function(e) { window.show(); });

                    }
                },
                bodyStyle : { "background-color" : self.mPanelColor },
                layout : { type : 'anchor', align : 'center' },
                items : [
                    {
                        xtype : 'container',
                        anchor : '100% 35%',
                        html : "<div id='course_title-" + pool.id  + "' align='center' class='course-title-container'>" +
                               "<a style='color: "+ linkColor+";' href='#' class='course-title pool" + pool.id + "' >" +
                               poolLabel + "</a>" + "</div>",
                        listeners : {
                            afterrender : function(c)
                            {
                                var selector = ".pool" + pool.id;

                                Ext.select(selector).on('click', function(e) { window.show(); });
                            }
                        }
                    },
                    {
                        xtype : 'container',
                        anchor : '100% 35%',
                        border: true,
                        html : "<div class='pool_icon_container' align='center' id='pool_icon_container-" + pool.id + "'></div>"
                    },
                    {
                        xtype : 'container',
                        anchor : '100% 30%',
                        html : "<div style='margin-left:35px;' class='toolcontainer' align='center' id='toolcontainer-" + pool.id + "'></div>"
                    }
                ]

            }
        );
    };

    /**
     * Returns a window which will contain the content of a elective pool
     */
    Curriculum.prototype.getModalPoolWindow = function(pool)
    {
        /* calculate the appropriate font color, depening on the chosed pool color */
        var color = self.contrastColor(self.hPanelHeaderColor);

        var titleSpan1 = "<span class='window_title' style='color: " + pool.color + ";'>",
            titleSpan2 = pool.name + " ( Min: " + pool.minCrP + " CrP, Max: " + pool.maxCrP + " CrP )"+ "</span> ";

        /* create the window object */
        var window = Ext.create(
            'Ext.window.Window',
            {
                title : titleSpan1 + titleSpan2,
                id: 'modal-pool-' + pool.id,
                cls : 'modal_window',
                autoScroll : 'true',
                layout : 'anchor',
                closeAction : 'hide',
                modal : true,
                bodyStyle : { "background-color" : self.mPanelColor },
                listeners : {
                    afterrender : function(c)
                    {
                        /* set the css class for the header panel */
                        c.header.addCls('elective_pool_window_header');

                        /* set the background color */
                        document.getElementById(c.header.id).firstChild.style.background = "#" + pool.color;
                    }
                }
        });
        if (pool.children.length)
        {
            var container = self.getContainer(self.mPanelColor);
            for (var i = 0; i < pool.children.length; i++)
            {
                var item = self.getAsset(pool.children[i], pool, 2, 2);

                if (container.items.length >= self.maxItems)
                {
                    window.add(container);
                    container = self.getContainer(hPanelColor);
                }
                container.add(item);
            }
            window.add(container);
        }
        return window;
    };

    /**
     * Returns a container used for spacing
     */
    Curriculum.prototype.getSpacer = function(color, leftMargin, topMargin)
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
    Curriculum.prototype.getSubject = function(subject, tooltipData, leftMargin, topMargin)
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

        var headerColor = this.contrastColor(subject.color);
        var linkColor = this.contrastColor(subject.color);
        if (subject.abbreviation === undefined)
        {
            subject.abbreviation = '';
        }

        // SEF-Route
        var moduleDescriptionLink = "index.php?option=com_thm_organizer&view=subject_details&languageTag=" + languageTag +"&id=" + subject.id +"&Itemid=" + menuID;
        var moduleTitle = ((self.cutTitle === 1) ? (subject.name.substring(0, titleLength) + "...") : subject.name);
        var titleSpans = "<span style='color:" + headerColor + ";"+"'class='course_title'>" + subject.abbreviation.substr(0,10) + "</span>";
        titleSpans += "<span style='color:" + headerColor + ";"+"'class='course_creditpoints'>" + subject.creditpoints + " CrP" + "</span>";
      
        /* creates the panel */
        var course = Ext.create(
            'Ext.panel.Panel',
            {
                xtype : 'panel',
                cls: 'course',
                bodyCls: 'course_body',
                title : titleSpans,
                width : self.itemWidth,
                height : self.itemHeight,
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
                        if(subject.scheduleLink !== undefined)
                        {
                            self.addScheduleIcon(subject.id, subject.name, subject.scheduleLink);
                        }

                        /* responsible icon */
                        if(subject.teacherName !== undefined)
                        {
                            self.addTeacherIcon(subject.id, subject.teacherLink, subject.teacherName); 
                        }

                        /* ecollab icon */
                        if(subject.ecollabLink !== undefined)
                        {
                            self.addECollabIcon(subject.id, subject.ecollabLink);
                        }

                        /* title tooltip */
                        self.addTitleToolTip(subject.id, tooltipData);
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
                        html : "<div id='course_title-" + subject.id  + "' align='center' class='course-title-container'>" +
                               "<a style='color:" + linkColor + ";" + "'class='course-title' href='" + moduleDescriptionLink +
                               "'>" + moduleTitle + "</a>" + "</div>"
                    },
                    {
                        xtype : 'container',
                        anchor : '100% 30%',
                        html : "<div align='center' class='toolcontainer' id='toolcontainer-"+ subject.id + "'></div>",
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
    Curriculum.prototype.getTextContainer = function(group)
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
    Curriculum.prototype.getTitleLabel = function(item)
    {
        return (languageTag === 'de')? item.name_de : item.name_en;
    };

    Curriculum.prototype.performAjaxCall = function()
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
            success : self.ajaxHandler
        });
    };
}