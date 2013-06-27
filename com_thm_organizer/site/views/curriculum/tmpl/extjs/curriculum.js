function Curriculum(menuID, programID, horizontalGroups, languageTag, totalWidth, totalHeight, 
                    horizontalPanelColor, itemWidth, defaultItemColor, electivePanelColor, 
                    cutTitle, titleLength, schedulerLink, itemLineBreak, electiveLineBreak,
                    cumpulsoryLineBreak, counter, defaultInfoLink)
{
    var self = this;
    this.menuID = menuID;
    this.programID = programID;
    this.horizontalGroups = horizontalGroups;
    this.languageTag = languageTag;
    this.totalWidth = totalWidth;
    this.totalHeight = totalHeight;
    this.horizontalPanelColor = horizontalPanelColor;
    this.itemWidth = itemWidth;
    this.defaultItemColor = defaultItemColor;
    this.electivePanelColor = electivePanelColor;
    this.cutTitle = cutTitle;
    this.titleLength = titleLength;
    this.itemLineBreak = itemLineBreak;
    this.electiveLineBreak = electiveLineBreak;
    this.cumpulsoryLineBreak = cumpulsoryLineBreak;
    this.itemLineBreak = itemLineBreak;
    this.defaultInfoLink = defaultInfoLink;


	
    /**
     * Calculate the color
     * Source: http://www.nbdtech.com/Blog/archive/2008/04/27/Calculating-the-Perceived-Brightness-of-a-Color.aspx
     */
    Curriculum.prototype.contrastColor = function(hex_color)
    {
        /* convert to rgb */
        if (hex_color === undefined)
        {
            hex_color = 666666;
        }
        var rgb = this.hex2rgb(hex_color);

        var value = Math.sqrt( rgb.red * rgb.red * .241 +  rgb.green * rgb.green * .691 +  rgb.blue * rgb.blue * .068);

        if(value < 130)
        {
                return "rgb(255, 255, 255)";
        }
        else
        {
            return "rgb(0, 0, 0)";
        }
    };

    /**
     * Convert a hexadecimal color to the rgb model
     */
    Curriculum.prototype.hex2rgb = function(hex)
    {
        if (hex[0]=="#")
        {
            hex=hex.substr(1);
        }
        if (hex.length==3)
        {
            var temp=hex; hex='';
            temp = /^([a-f0-9])([a-f0-9])([a-f0-9])$/i.exec(temp).slice(1);
            for (var i=0;i<3;i++)
            {
                hex += temp[i] + temp[i];
            }
        }
        var triplets = /^([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})$/i.exec(hex).slice(1);
        return {
          red:   parseInt(triplets[0],16),
          green: parseInt(triplets[1],16),
          blue:  parseInt(triplets[2],16)
        }
    };
	
    /**
     * Add a Note icon
     */
    Curriculum.prototype.addNote = function(id, note, menu_link)
    {
        var tooltipHeading = (languageTag == 'de') ? 'Info' : 'Info';
        var tooltipAddInfo = (languageTag == 'de') ? '<br>Klicken f&uuml;r weitere Informationen' : 'Click for additional information';
		
        if(menu_link == 0)
        {	
            var noteImage = new Ext.create(
                'Ext.Component',
                {
                    xtype : 'box',
                    autoEl : {
                        tag : 'img',
                        id : 'responsible-image',
                        cls : 'tooltip',
                        src : note_icon,
                    },
                    renderTo : 'toolcontainer-' + id
                }
            );
        }
        else
        {
            var noteImage = new Ext.create(
                'Ext.Component',
                {
                    xtype : 'box',
                    autoEl : {
                            tag : 'a',
                            href : menu_link,
                            children : [ { tag : 'img', id : 'responsible-image', cls : 'tooltip', src : note_icon, } ]
                    },
                    renderTo : 'toolcontainer-' + id
                }
            );
        }

        new Ext.ToolTip(
            {
                target : noteImage.el.id,
                title : tooltipHeading,
                html : note + ((menu_link != 0) ? tooltipAddInfo : ''),
                dismissDelay:0,
                autoHide : true,
            }
        );
    };
	
    /**
     * Add a Note icon
     */
    Curriculum.prototype.addEcollab = function(id, link) {

        var tooltipHeading = (languageTag == 'de') ? 'Info' : 'Info';
        var tooltipAddInfo = (languageTag == 'de') ? '<br>Klicken f&uuml;r weitere Informationen' : 'Click for additional information';
		
        var noteImage = new Ext.create(
            'Ext.Component',
            {
                xtype : 'box',
                autoEl : {
                    tag : 'a',
                    href : link,
                    target: "_blank",
                    children : [ { tag : 'img', id : 'collab-image', cls : 'tooltip', src : collab_icon } ]
                },
                renderTo : 'toolcontainer-' + id
            }
        );
		
        new Ext.ToolTip(
            {
                target : noteImage.el.id,
                title : tooltipHeading,
                html : '<br>' + link + "<br>" +tooltipAddInfo,
                dismissDelay:0,
                autoHide : true,
            }
        );
    };
	
    /**
     * Adds a tooltip
     */
    Curriculum.prototype.addTooltip = function(container_id, title, schedule)
    {
        var tooltipAddInfo = null;
        if(schedulerLink != "")
        {
            tooltipAddInfo = (languageTag == 'de') ? '<br>Klicken f&uuml;r weitere Informationen' : 'Click for additional information';
        }
		
        var tooltipImage = new Ext.create(
            'Ext.Component',
            {
                xtype : 'box',
                autoEl : {
                        tag : 'a',
                        href : schedulerLink,
                        children : [ { tag : 'img', id : 'tooltip-image', cls : 'tooltip', src : scheduler_icon } ]
                },
                renderTo : 'toolcontainer-' + container_id
            }
        );

        new Ext.ToolTip(
            {
                target : tooltipImage.el.id,
                title : 'Details:',
                html : schedule +tooltipAddInfo,
                dismissDelay:0,
                autoHide : true,
            }
        );
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
                            src : place_holder_icon
                    },
                    renderTo : 'toolcontainer-' + container_id
            }
        );
    };

    /**
     * Adds a responsible icon
     */
    Curriculum.prototype.addResponsible = function(id, responsible_link, responsible_name, responsible_picture)
    {
        var heading = (languageTag == 'de') ? 'Modulverantwortliche:' : 'Responsible:';
        var responsilbe_tooltip = null;
        var tooltipAddInfo  = "";

        if(responsible_link)
        {
            tooltipAddInfo = (languageTag == 'de') ? 'Klicken f&uuml;r weitere Informationen' : 'Click for additional information';
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

        new Ext.ToolTip(
            {
                target : responsilbe_tooltip.el.id,
                title : heading ,
                html : "<hr style='width:130px;visibility: hidden'>"+responsible_name + "<div style='margin-top:4px;margin-bottom:4px;'align='center'>"+ (responsible_picture ? responsible_picture : "")+ "</div><div>"+tooltipAddInfo+"</div><br>",
                dismissDelay:0,
                autoHide : true,
            }
        );
    };
	

    /**
     * Adds a title icon
     */
    Curriculum.prototype.addTitleTooltip = function(id, title)
    {
        new Ext.ToolTip(
            {
                target : 'course_title-' + id ,
                html : title, 
                dismissDelay:0,
                autoHide : true,
            }
        );
    };

    /**
     * Adds a container
     */
    Curriculum.prototype.getContainer = function(color)
    {
        var container = Ext.create(
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
        return container;
    };
	
    /**
     * Adds a Container for comp. pools
     */
    Curriculum.prototype.addPoolContainer = function(color)
    {
        var container = Ext.create(
            'Ext.container.Container',
            {
                layout : 'hbox',
                width: 0,
                height : 66,
                defaultMargins:  {top: 0, right: 10, bottom: 0, left: 0},
                bodyStyle : {
                        "background-color" :  "#" + color
                },
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
        return container;
    };
	
    /**
     * Determine the title of an item
     * 
     * @param   {object}  item  the item being iterated
     */
    Curriculum.prototype.getTitleLabel = function(item)
    {
        return (languageTag == 'de')? item.name_de : item.name_en;
    };

    /**
     * Returns a container used for spacing
     */
    Curriculum.prototype.getSpacerItem = function(color, leftMargin, topMargin)
    {
        if (color == undefined)
        {
            color = "#666666";
        }
        else if (color.indexOf("#") == -1)
        {
            color = "#" + color;
        }
        
        if (leftMargin == undefined)
        {
            leftMargin = "2";
        }

        if (topMargin == undefined)
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
		 
        var mappingID = subject.mappingID;
        var subjectTitle = this.getTitleLabel(subject);
        var headerColor = this.contrastColor(subject.color);
        var linkColor = this.contrastColor(defaultItemColor);
        var abbreviation = (this.languageTag == 'de')? subject.abbreviation_de : subject.abbreviation_en;
        if (abbreviation == undefined)
        {
            abbreviation = '';
        }
        
        // SEF-Route
        var moduleDescriptionLink = "index.php?option=com_thm_organizer&view=subject_details&lang=" + languageTag +"&id=" + subject.lsfID +"&Itemid=" + menuID;
        var moduleTitle = ((cutTitle == 1) ? (subjectTitle.substring(0, titleLength) + "...") : subjectTitle);
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
                bodyStyle : { "background-color" : defaultItemColor },
                listeners : {
                    afterrender : function(c)
                    {					
                        /* set the css class for the header panel */
                        c.header.addCls('course_panel_header');

                        /* set the background color */
                        document.getElementById(c.el.id).firstChild.style.background = "#" + subject.color;

                        /* build the toolbar */
									
                        /* scheduler icon */
                        if(subject.schedule != null)
                        {
                            self.addTooltip(mappingID, subject.title, subject.schedule);
                        }
                        else
                        {
                            self.addPlacehodlerIcon(mappingID);
                        }

                        /* responsible icon */
                        if(subject.teacherName != "")
                        {
                            self.addResponsible(mappingID, subject.teacherLink, subject.teacherName, subject.teacherPicture); 
                        }
                        else 
                        {
                            self.addPlacehodlerIcon(mappingID);
                        }

                        /* ecollab icon */
                        if(subject.ecollabLink != "")
                        {
                            self.addEcollab(mappingID, subject.ecollabLink);
                        }
                        else
                        {
                            self.addPlacehodlerIcon(mappingID);
                        }

                        /* title tooltip */
                        self.addTitleTooltip(mappingID, tooltipData);
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
                        html : "<div id='course_title-" + mappingID  + "' align='center' class='course-title-container'>"
                               + "<a style='color:" + linkColor + ";" + "'class='course-title' href='"
                               + moduleDescriptionLink + "'>" + moduleTitle + "</a>" + "</div>"
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
                    src : comp_pool_icon,
                },
                renderTo : 'pool_icon_container-' + container_id
            }
        );
        return tooltipImage.id;
    };
	
	
    /**
     * Returns a Panel which represents an elective course pool
     */
    Curriculum.prototype.getModalPoolPanel = function(data, window, tooltip, leftMargin, topMargin)
    {
        if(data.min_creditpoints == data.max_creditpoints)
        {
            var creditpoints = data.min_creditpoints;
        }
        else
        {
            var creditpoints = data.min_creditpoints + "-" + data.max_creditpoints;
        }
		
        /* determine the title */
        var poolTitle = self.getTitleLabel(data);

        /* calculate the appropriate font color, depening on the chosed pool color */
        var headerFontColor = self.contrastColor(data.color);
        var linkColor = self.contrastColor(electivePanelColor);
        var poolLabel = ((cutTitle == 1) ? (data.short_title.substring(0, titleLength) + "...") : poolTitle);
        var titleSpans = "<span style='color:" + headerFontColor + ";'class='elective_pool_title course_title' >" + data.abbreviation + "</span>";
	titleSpans += "<span style='color:" + headerFontColor + ";'class='elective_pool_creditpoints'>" + creditpoints + " CrP" + "</span>"

        /* create the panel */
        var electivePool = Ext.create(
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
                        self.addTitleTooltip(data.horizontalGroups_programIDs_id, tooltip);
                        document.getElementById(c.el.id).firstChild.style.background = "#" + data.color_hex;

                        if(data.note != "" || data.menu_link != 0)
                        {
                            self.addNote(data.horizontalGroups_programIDs_id, data.note, data.menu_link);
                        }

                        var iconId  = self.addPoolIcon(data.horizontalGroups_programIDs_id, tooltip);
                        var selector = "#" + iconId;

                        Ext.select(selector).on('click', function(e) { window.show(); });

                    }
                },
                bodyStyle : { "background-color" : electivePanelColor, },
                layout : { type : 'anchor', align : 'center' },
                items : [
                    {
                        xtype : 'container',
                        anchor : '100% 35%',
                        html : "<div id='course_title-" + data.horizontalGroups_programIDs_id  + "' align='center' class='course-title-container'>"
                               + "<a style='color: "+ linkColor+";' href='#' class='course-title pool" + data.horizontalGroups_programIDs_id + "' >"
                               + poolLabel + "</a>" + "</div>",
                        listeners : {
                            afterrender : function(c)
                            {
                                var selector = ".pool" + data.horizontalGroups_programIDs_id;

                                Ext.select(selector).on('click', function(e) { window.show(); });
                            }
                        },
                    },
                    {
                        xtype : 'container',
                        anchor : '100% 35%',
                        border: true,
                        html : "<div class='pool_icon_container' align='center' id='pool_icon_container-" + data.horizontalGroups_programIDs_id + "'></div>"
                    },
                    {
                        xtype : 'container',
                        anchor : '100% 30%',
                        html : "<div style='margin-left:35px;' class='toolcontainer' align='center' id='toolcontainer-" + data.horizontalGroups_programIDs_id + "'></div>"
                    }
                ]

            }
        );
        return electivePool;
    };

	


    /**
     * Returns a Panel, which represents a semester
     */
    Curriculum.prototype.getHorizontalGroupPanel = function(horizontalGroup)
    {
        if(horizontalGroup.color)
        {
            /* calculate the appropriate font color, depening on the chosed pool color */
            var contrastColor = self.contrastColor(horizontalGroup.color);
        }
        
        var textColorStyle = contrastColor? ' color: ' + contrastColor + ';' : '';
        var label = (languageTag == "de")? horizontalGroup.name_de : horizontalGroup.name_en;
				
		/* create the window object */
        var horizontalGroupElement = Ext.create(
            'Ext.panel.Panel',
            {
                xtype : 'panel',
                title : '<span style="font-size: 12px;' + textColorStyle +'">' + label + '</span>',
                layout : 'anchor',
                cls: 'semester',
                margin : '8 0 0 0',
                autoHeight: true,
                collapsible : true,
                border : false,
                listeners : {
                    afterrender : function(c)
                    {
                        c.header.addCls('panel_curriculum_semester');
                        document.getElementById(c.el.id).firstChild.style.background = "#"+ horizontalGroup.color;
                        document.getElementById(c.el.id).lastChild.style.background = "#"+ horizontalGroup.color;
                        c.body.applyStyles("background-color:" + horizontalPanelColor);	
                    }
                }
            }
        );
        return horizontalGroupElement;
    };

    /**
     * Returns a Panel, which represents a curriculum
     */
    Curriculum.prototype.getCurriculumPanel = function()
    {
        var curriculum = Ext.create(
            'Ext.container.Container',
            {
                width : totalWidth,
                height :totalHeight,
                layout : 'anchor',
                id : 'curriculum_container_' + counter,
                cls: 'curriculum',
                autoScroll : true,
                autoHeight: true,
                border: false
            }
        );
        return curriculum;

    };

    /**
     * Returns a container which represents a compulsory coursepool
     */
    Curriculum.prototype.addCompulsoryPool = function(color, leftMargin, topMargin)
    {
        /* create the window object */
        var compulsoryPool = Ext.create(
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
     * Returns a window which will contain the content of a elective pool 
     * 
     */
    Curriculum.prototype.getModalPoolPanelWindow = function(title, min_crp, max_crp, header_color)
    {
        /* calculate the appropriate font color, depening on the chosed pool color */
        var color = self.contrastColor(header_color);

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
     *  Determine the childs of a pool and put them into a ExtJS window
     */
    Curriculum.prototype.buildModalContent = function (asset)
    {
        var parent = asset;
        var container = self.getContainer(asset.hex_color);

        /* create the window for the childs of the given asset */
        var window = self.getModalPoolPanelWindow(asset.abbreviation, asset.min_creditpoints, asset.max_creditpoints, asset.color_hex);

        /* get the children of this asset */
        var childs = asset.childs;
        var compPoolFlag = false;

        /* return an empty window, if a pool has yet no children */
        if(typeof childs === "undefined")
        {
            window.add(container.add(self.getSpacerItem()));
            return window;
        }

        /* select the correct structure of the pools object structure */
        if(typeof asset.childs[0].length === "undefined")
        {
            childs = asset.childs;
        }
        else
        {
            childs = asset.childs[0];
        }
		
        /* iterate each child of the current asset */
        for (var i = 0, len = childs.length; i < len; ++i)
        {
            var asset = self.getAsset(childs[i], parent);

            if (i == 0)
            {
                asset = self.getAsset(childs[i], parent, 2, 2);
            }
            else
            {
                if(compPoolFlag == true)
                {
                    asset = self.getAsset(childs[i], parent, 1, 2);
                }
                else
                {
                    asset = self.getAsset(childs[i], parent, 2, 2);
                }
            }

            /* apply the correct multi-row behaviour */
            if(container.items.length < electiveLineBreak)
            {
                container.add(asset);
            }
            else
            {
                asset = self.getAsset(childs[i], parent, 2, 2);
                container = self.getContainer(asset.hex_color);
                container.add(asset);
            }

            if(childs[i].asset_type_id == 2 && childs[i].pool_type == 0) {
                    compPoolFlag = true;
            }

            window.add(container);
        }

        return window;
    };
	
    /**
     * Returns a container which contains the title of a given pool (type 0)
     */
    Curriculum.prototype.addCompPoolText = function(pool)
    {
        var subjectTitle = self.getTitleLabel(pool);
        var num = new Number(pool.horizontalGroups_programIDs_id);
        var id = num.toString();
        var linkColor = self.contrastColor(pool.color);
		
        /* specify the describing text */
        if(pool.min_creditpoints == pool.max_creditpoints)
        {
            if(languageTag == "de")
            {
                var label = " insgesamt " + pool.min_creditpoints + " CrP";
            }
            else
            {
                var label = " overall " + pool.min_creditpoints + " CrP";
            }

        }
        else
        {
            if(languageTag == "de")
            {
                var label = " insgesamt " + pool.min_creditpoints + " CrP bis " + pool.max_creditpoints + " CrP";
            }
            else
            {
                var label = " overall " + pool.min_creditpoints + " CrP to " + pool.max_creditpoints + " CrP";
            }
        }
		
        var html = "<span style='color: " + linkColor +";' class='course_title' id='course_title-" + pool.horizontalGroups_programIDs_id  + "'>";
        html += "<b>" + subjectTitle + ":</b>" + label;
        html += "<span style='margin-left:3px' class='toolcontainer' align='center' id='toolcontainer-" + pool.horizontalGroups_programIDs_id + "'>" 
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
					
                    if(pool.note != "" ||  pool.menu_link != 0)
                    {
                        self.addNote(pool.mappingID, tooltipContent, pool.menu_link);
                    }
                }
            }

        });

        return container;
    };
	
	
	
    /**
     * Returns a container which contains the title of a given pool
     */
    Curriculum.prototype.getTextContainer = function(group)
    {
        var linkColor = self.contrastColor(horizontalPanelColor);
        var text = (languageTag == 'de')? group.name_de : group.name_en;
        var html = "<span style='color: " + linkColor + ";' class='course_title'>" + text + "</span>";
        return Ext.create('Ext.container.Container', { layout : 'hbox', cls: "semester_text_container", html: html });
    };
	

    /**
     * Determine the childs of a pool and put them into a ExtJS container
     */
    Curriculum.prototype.getInlinePoolPanel = function(pool_content, leftMargin, topMargin)
    {
        /* get the pool container with a certain background color and margins */
        var comPool = self.addCompulsoryPool(pool_content.color_hex,  leftMargin, topMargin);

        /* get the container element for supporting multi-row layouting */
        var container = self.addPoolContainer(pool_content.color_hex);

        /* true if a child is a pool of type 0. Used for calculating the correct margins */
        var compPoolFlag = false;

        /* apply further processing, if the pool has assigned children */
        if(pool_content.childs)
        {
            var childs = null;

            /* select the correct structure of the pools object structure */
            if(typeof pool_content.childs[0].length === "undefined")
            {
                childs = pool_content.childs;
            }
            else
            {
                childs = pool_content.childs[0];
            }
			
            /* iterate over the subtree */
            for (var i = 0, len = childs.length; i < len; i++)
            { 
                if(i == 0)
                {
                    var asset = self.getAsset(childs[i], pool_content, 1, 1);
                }
                else
                {
                    if(compPoolFlag == true)
                    {
                        var asset = self.getAsset(childs[i], pool_content, 1, 1);
                    }
                    else
                    {
                        var asset = self.getAsset(childs[i], pool_content, 2, 1);
                    }
                }
				
                /* apply the correct multi-row behaviour */
                if(container.items.length < cumpulsoryLineBreak)
                {
                    container.add(asset);
                }
                else
                {
                    container = self.getContainer(pool_content.color_hex);
                    container.add(self.getAsset(childs[i], pool_content, 1, 1 ));
                }
				
                if(asset.asset_type_id == 2 && asset.pool_type == 0)
                {
                    compPoolFlag = true;
                }
				
                comPool.add(container);
				
            }
			
        } 
		
        /* attach the name and related tooltip above the actual pool content */
        comPool.add(self.addCompPoolText(pool_content));

        return comPool;
    };
	
    /**
     * Determine the type of an asset and delegate to the corresponding method
     */
    Curriculum.prototype.getAsset = function(item, parent, leftMargin, topMargin)
    {
        /* specify the content of the mouse-over (title) */
        var title = (languageTag == 'de')? item.name_de : item.name_en;
        var courseID = (item.lsfID == '')? item.hisID : item.lsfID;
        var tooltip = title + " (" + courseID + ")";

        /* determine the type of the item and descide whoch actions must be applied */
        if(item !== undefined)
        {
            if(item.children !== undefined)
            {
                // Pool
                if(item.children.length < 6)
                {
                    // Pool type 0, child elements are displayed with a surrounded, background colored container 
                    return self.getInlinePoolPanel(item, leftMargin, topMargin);
                }
                else
                {
                    // Pool type 1, child elements are displayed within an opening (modal) window
                    return self.getModalPoolPanel(item, self.buildModalContent(item), item.title, leftMargin, topMargin);	
                }
            }
            else
            {
                return self.getSubject(item, tooltip, leftMargin, topMargin);
            }
        }
        else
        {
            return self.getSpacerItem('#666666', leftMargin, topMargin)
        }
    };
}