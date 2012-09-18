function Curriculum(itemid, major, semesters, lang, width, height, 
		semester_body_color, course_width, course_body_color, elective_pool_body_color, 
		title_cut_length_activate, title_cut_length, scheduler_link, asset_line_break, elective_pool_window_line_break,
		compulsory_pool_line_break, counter) {

	var self = this;
	
	
	var semester_body_color = semester_body_color;
	var course_width = course_width;
	var course_body_color = course_body_color;
	var elective_pool_body_color = elective_pool_body_color;
	var title_cut_length_activate = title_cut_length_activate;
	var title_cut_length = title_cut_length;
	var scheduler_link = scheduler_link;
	var asset_line_break = asset_line_break;
	var elective_pool_window_line_break = elective_pool_window_line_break;
	var compulsory_pool_line_break = compulsory_pool_line_break;
	var counter = counter;
	
	/**
	 * Convert a hexadecimal color to the rgb model
	 */
	Curriculum.prototype.hex2rgb = function(hex) {
		  if (hex[0]=="#") hex=hex.substr(1);
		  if (hex.length==3) {
		    var temp=hex; hex='';
		    temp = /^([a-f0-9])([a-f0-9])([a-f0-9])$/i.exec(temp).slice(1);
		    for (var i=0;i<3;i++) hex+=temp[i]+temp[i];
		  }
		  var triplets = /^([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})$/i.exec(hex).slice(1);
		  return {
		    red:   parseInt(triplets[0],16),
		    green: parseInt(triplets[1],16),
		    blue:  parseInt(triplets[2],16)
		  }
		};

	
	/**
	 * Calculate the color
	 * Source: http://www.nbdtech.com/Blog/archive/2008/04/27/Calculating-the-Perceived-Brightness-of-a-Color.aspx
	 */
		Curriculum.prototype.contrastColor = function(hex_color) {
			
			/* convert to rgb */
			var rgb = this.hex2rgb(hex_color);
		
			var value = Math.sqrt(
					rgb.red * rgb.red * .241 + 
					rgb.green * rgb.green * .691 + 
					rgb.blue * rgb.blue * .068);
			
			if(value < 130) {
				return "rgb(255, 255, 255)";
			} else {
				return "rgb(0, 0, 0)";
			}
	};
	
	/**
	 * Add a Note icon
	 */
	Curriculum.prototype.addNote = function(id, note, menu_link) {
		
		var tooltipHeading = (lang == 'de') ? 'Info' : 'Info';
		var tooltipAddInfo = (lang == 'de') ? '<br>Klicken f&uuml;r weitere Informationen' : 'Click for additional information';
		
		if(menu_link == 0) {	
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
						renderTo : 'toolcontainer-'
							+ id
				});
			
		} else {
			var noteImage = new Ext.create(
					'Ext.Component',
					{
						xtype : 'box',
						autoEl : {
							tag : 'a',
							href : menu_link,
							children : [ {
								tag : 'img',
								id : 'responsible-image',
								cls : 'tooltip',
								src : note_icon,
							} ]
						},
						renderTo : 'toolcontainer-'
							+ id
				});
		}
		

		new Ext.ToolTip({
			target : noteImage.el.id,
			title : tooltipHeading,
			html : note + ((menu_link != 0) ? tooltipAddInfo : ''),
			dismissDelay:0,
			autoHide : true,
		});	
		
		
		
		
	};
	
	/**
	 * Add a Note icon
	 */
	Curriculum.prototype.addEcollab = function(id, link) {
		
		var tooltipHeading = (lang == 'de') ? 'Info' : 'Info';
		var tooltipAddInfo = (lang == 'de') ? '<br>Klicken f&uuml;r weitere Informationen' : 'Click for additional information';
		
		var noteImage = new Ext.create(
							'Ext.Component',
							{
								xtype : 'box',
								autoEl : {
									tag : 'a',
									href : link,
									target: "_blank",
									children : [ {
										tag : 'img',
										id : 'collab-image',
										cls : 'tooltip',
										src : collab_icon,
									} ]
								},
								renderTo : 'toolcontainer-'
									+ id
						});
		
		new Ext.ToolTip({
			target : noteImage.el.id,
			title : tooltipHeading,
			html : '<br>' + link + "<br>" +tooltipAddInfo,
			dismissDelay:0,
			autoHide : true,
		});	
		
	};
	
	/**
	 * Adds a tooltip
	 */
	Curriculum.prototype.addTooltip = function(container_id, title, schedule) {
		
		var tooltipAddInfo = null;
		if(scheduler_link != "") {
			tooltipAddInfo = (lang == 'de') ? '<br>Klicken f&uuml;r weitere Informationen' : 'Click for additional information';
		}
		
		
		var tooltipImage = new Ext.create(
								'Ext.Component',
								{
									xtype : 'box',
									autoEl : {
										tag : 'a',
										href : scheduler_link,
										children : [ {
											tag : 'img',
											id : 'tooltip-image',
											cls : 'tooltip',
											src : scheduler_icon,
										} ]
									},
									renderTo : 'toolcontainer-'
										+ container_id
							});
		

		new Ext.ToolTip({
			target : tooltipImage.el.id,
			title : 'Details:',
			html : schedule +tooltipAddInfo,
			dismissDelay:0,
			autoHide : true,
		});
			
	};
	
	/**
	 * Adds a placeholder icon
	 */
	Curriculum.prototype.addPlacehodlerIcon = function(container_id) {
		
		var tooltipImage = new Ext.create(
								'Ext.Component',
								{
									xtype : 'box',
									autoEl : {
										tag : 'img',
										id : 'tooltip-image',
										src : place_holder_icon
									},
									renderTo : 'toolcontainer-'
										+ container_id
							});
	};

	
	/**
	 * Adds a responsible icon
	 */
	Curriculum.prototype.addResponsible = function(id, responsible_link, responsible_name, responsible_picture) {
		
		var heading = (lang == 'de') ? 'Modulverantwortliche:' : 'Responsible:';

		
		
		var responsilbe_tooltip = null;
		var tooltipAddInfo  = "";

		if(responsible_link) {
			tooltipAddInfo = (lang == 'de') ? 'Klicken f&uuml;r weitere Informationen' : 'Click for additional information';
			responsilbe_tooltip = new Ext.create(
					'Ext.Component',
					{
						xtype : 'box',
						autoEl : {
							tag : 'a',
							href : responsible_link,
							children : [ {
								tag : 'img',
								src : responsible_icon
							} ]
						},
						renderTo : 'toolcontainer-' + id
					});
		} else {
			
			
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
					});
			
			
		}
		
		

		
		new Ext.ToolTip({
			target : responsilbe_tooltip.el.id,
			title : heading ,
			html : "<hr style='width:130px;visibility: hidden'>"+responsible_name + "<div style='margin-top:4px;margin-bottom:4px;'align='center'>"+ (responsible_picture ? responsible_picture : "")+ "</div><div>"+tooltipAddInfo+"</div><br>",
			dismissDelay:0,
			autoHide : true,
		});
		
	

				
			
	};
	

	/**
	 * Adds a title icon
	 */
	Curriculum.prototype.addTitleTooltip = function(id, title) {
		
		new Ext.ToolTip({
			target : 'course_title-' + id ,
			//title : 'Details:',
			html : title, 
			dismissDelay:0,
			autoHide : true,
		});
	};
	

	/**
	 * Adds a container
	 */
	Curriculum.prototype.addContainer = function(color) {
		
		var container = Ext.create('Ext.container.Container', {
			layout : 'hbox',
			width: 0,
			height : 66,
			bodyStyle : {
				"background-color" :  color
			},
			defaultMargins : {top: 0, right: 0, bottom: 0, left: 0},
			listeners : {
				afterrender : function(c) {
				
					/* set the correct size of the container */
					var width = 0;
					var height = 0;

					for (var k = 0; k < this.items.length; k++) {
						width += this.items.get(k).getWidth() + 4;

						if (this.items.get(k).getHeight() > height)  {
							height = this.items.get(k).getHeight() + 4;
						}
					}

					if (width > c.width) {
						this.setWidth(width);
					}
					
					if (height > c.height) {
						this.setHeight(height);
					}
				}
			}
		});
		
		return container;
	};
	
	/**
	 * Adds a Container for comp. pools
	 */
	Curriculum.prototype.addPoolContainer = function(color) {
		
		var container = Ext.create('Ext.container.Container', {
			layout : 'hbox',
			width: 0,
			height : 66,
			defaultMargins:  {top: 0, right: 10, bottom: 0, left: 0},
			bodyStyle : {
				"background-color" :  "#" + color
			},
			listeners : {
				afterrender : function(c) {
				
					/* set the correct size of the container */
					var width = 0;
					var height = 0;

					for (var k = 0; k < this.items.length; k++) {
						width += this.items.get(k).getWidth() + 4;

						if (this.items.get(k).getHeight() > height) {
							height = this.items.get(k).getHeight() + 4;
						}
					}

					if (width > c.width) {
						this.setWidth(width);
					}
					
					if (height > c.height) {
						this.setHeight(height);
					}
				}
			}
		});
		
		return container;
	};
	
	/**
	 * Determine the title of an asset
	 */
	Curriculum.prototype.getTitleLabel = function(asset) {
			
		/* set the correct label */
		var title = null;

		if (asset.short_title == "") {
			title = asset.title;
		} else {
			title = asset.short_title;
		}
			
		return title;
	};

	/**
	 * Returns a dummy container
	 */
	Curriculum.prototype.addDummy = function(color, margin_left, margin_top) {
		
		//console.debug(color);
		
		if (color.indexOf("#") == -1) {
			color = "#" + color;
		}

		/* creates the panel */
		var dummy = Ext
				.create(
						'Ext.panel.Panel',
						{
							xtype : 'panel',
							cls: 'dummy',
							bodyCls: 'dummy_body',
							margin : margin_top +" 0 0 "+ margin_left,
							width : course_width,
							height : 65,
							border:false,
							
							bodyStyle : {
								"background-color" : color,
								"top": "-1px"
							}
						});

		return dummy;

	};

	
	/**
	 * Returns a panel which represents a course
	 */
	Curriculum.prototype.addCourse = function(data, tooltipData, margin_left, margin_top) {
		
		//console.debug(data);
		 
		 /* set the default margin behavoiur */
		 if (margin_left === undefined ) {
			 margin_left = '2';
		 }
		 
		 if (margin_top === undefined ) {
			 margin_top = '2';
		 }
		 
		/* Convert the course id to a string */
		var num = new Number(data.semesters_majors_id);
		var id = num.toString();
		var courseTitle = this.getTitleLabel(data);
        var headerColor = this.contrastColor(data.color_hex);
        var linkColor = this.contrastColor(course_body_color);
        var moduleDescriptionLink = "index.php?option=com_thm_organizer&view=details&lang=" // SEF-Route
										+ lang +"&id=" + data.lsf_course_id +"&Itemid=" + itemid;
        var moduleTitle = ((title_cut_length_activate == 1) ? (courseTitle.substring(0, title_cut_length) + "...") : courseTitle);
      
		/* creates the panel */
		var course = Ext
				.create(
						'Ext.panel.Panel',
						{
							xtype : 'panel',
							cls: 'course',
							bodyCls: 'course_body',
							title : "<span style='color:" + headerColor + ";"+"'class='course_title'>"
									+ data.abbreviation.substr(0,10)
									+ "</span>"
									+"<span style='color:" + headerColor + ";"+"'class='course_creditpoints'>"
									+ data.min_creditpoints + " CrP"
									+"</span>",
							width :course_width,
							height : 65,
							margin : margin_top +" 0 0 "+ margin_left,
							bodyStyle : {
								"background-color" : course_body_color
							},
							listeners : {
								afterrender : function(c) {
									
									/* set the css class for the header panel */
									c.header.addCls('course_panel_header');
									
									/* set the background color */
									document.getElementById(c.el.id).firstChild.style.background = "#" + data.color_hex;
									
									/* build the toolbar */
									
									/* scheduler icon */
									if(data.schedule != null) {
										self.addTooltip(id, data.title, data.schedule);
									} else {
										self.addPlacehodlerIcon(id);
									}
									
									/* responsible icon */
									if(data.responsible != "") {
										self.addResponsible(id, data.responsible_link, data.responsible_name, data.responsible_picture); 
									} else {
										self.addPlacehodlerIcon(id);
									}
									
									/* note icon */
									if(data.note != "" ||  data.menu_link != 0) {
										self.addNote(id, data.note, data.menu_link);
									}else {
										self.addPlacehodlerIcon(id);
									}
									
									/* ecollab icon */
									if(data.ecollaboration_link != "") {
										self.addEcollab(id, data.ecollaboration_link);
									}else {
										self.addPlacehodlerIcon(id);
									}
								
									/* title tooltip */
									self.addTitleTooltip(id, tooltipData);

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
										html : "<div id='course_title-" + id  + "' align='center' class='course-title-container'>"
													+"<a style='color:" + linkColor + ";"+"'class='course-title' href='"
													+ moduleDescriptionLink + "'>" + moduleTitle
													+ "</a>"
												+"</div>"
									},
									{
										xtype : 'container',
										anchor : '100% 30%',
										html : "<div align='center' class='toolcontainer' id='toolcontainer-"+ id + "'></div>",
										margin : '-3 0 0 0'
									} ]
						});

		return course;

	};
	
	
	/**
	 * 
	 */
	Curriculum.prototype.addPoolIcon = function(container_id, title, schedule) {
		
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
					renderTo : 'pool_icon_container-'
						+ container_id
				});
		

		return tooltipImage.id;

	};
	
	
	/**
	 * Returns a Panel which represents an elective course pool
	 */
	Curriculum.prototype.addElectivePool = function(data, window, tooltip, margin_left, margin_top) {
		
		if(data.min_creditpoints == data.max_creditpoints) {
			var creditpoints = data.min_creditpoints;
		} else {
			var creditpoints = data.min_creditpoints + "-" + data.max_creditpoints;
		}
		
		/* determine the title */
		var poolTitle = this.getTitleLabel(data);
		
		/* calculate the appropriate font color, depening on the chosed pool color */
		var headerFontColor = this.contrastColor(data.color_hex);
		var linkColor = this.contrastColor(elective_pool_body_color);
		var poolLabel = ((title_cut_length_activate == 1) ? (data.short_title.substring(0, title_cut_length) + "...") : poolTitle);
		
		/* create the panel */
		var electivePool = Ext.create(
				'Ext.panel.Panel',
				{
						xtype : 'panel',	
						cls : "elective_pool",
						title : "<span style='color:" + headerFontColor + ";'class='elective_pool_title course_title' >"
								+ data.abbreviation
								+ "</span>"
								+"<span style='color:" + headerFontColor + ";'class='elective_pool_creditpoints'>"
								+ creditpoints + " CrP"
								+"</span>",
						width : course_width,
						height : 65,
						margin : margin_top +" 0 0 "+ margin_left,
						listeners : {
							afterrender : function(c) {
								c.header.addCls('course_panel_header');
								
								self.addTitleTooltip(data.semesters_majors_id, tooltip);
								
								document.getElementById(c.el.id).firstChild.style.background = "#" + data.color_hex;
								
								if(data.note != "" || data.menu_link != 0) {
									self.addNote(data.semesters_majors_id, data.note, data.menu_link);
								}
								
								var iconId  = self.addPoolIcon(data.semesters_majors_id, tooltip);
								var selector = "#" + iconId;
								
								Ext.select(selector).on('click', function(e) {
									window.show();
								})
								
							}
						},
						bodyStyle : { 
							"background-color" : elective_pool_body_color,
							},
							layout : {
								type : 'anchor',
								align : 'center'
							},
						items : [
								{
									xtype : 'container',
									anchor : '100% 35%',
									html : "<div id='course_title-" + data.semesters_majors_id  + "' align='center' class='course-title-container'>"
												+"<a style='color: "+ linkColor+";' href='#' class='course-title pool" + data.semesters_majors_id + "' >"
												+ poolLabel
												+ "</a>"
											+"</div>",
									listeners : {
										afterrender : function(c) {
											var selector = ".pool" + data.semesters_majors_id;

											Ext.select(selector).on('click', function(e) {
													window.show();
											})
										}
									},
								},
								{
									xtype : 'container',
									anchor : '100% 35%',
									border: true,
									html : "<div class='pool_icon_container' align='center' id='pool_icon_container-" + data.semesters_majors_id + "'></div>"
								},
								{
									xtype : 'container',
									anchor : '100% 30%',
									html : "<div style='margin-left:35px;' class='toolcontainer' align='center' id='toolcontainer-"
											+ data.semesters_majors_id + "'></div>"
								}]

						});

		return electivePool;
	};

	


	/**
	 * Returns a Panel, which represents a semester
	 */
	Curriculum.prototype.addSemester = function(semester) {
		 
		var semesterBodyColor = semester_body_color;
		
		if(semester.color) {
			/* calculate the appropriate font color, depening on the chosed pool color */
			var color = this.contrastColor(semester.color);
		}
	
		if(lang="de") {
			var label = semester.short_title_de;
		} else {
			var label = semester.short_title_en;
		}
				
		/* create the window object */
		var semesterElement = Ext.create('Ext.panel.Panel', {
			xtype : 'panel',
			title : '<span style="font-size: 12px; color: ' + color +';">' + label + '</span>',
			layout : 'anchor',
			cls: 'semester',
			margin : '8 0 0 0', //margin-top, abstand zwischen semestern
			autoHeight: true,
			collapsible : true,
			border : false,
			listeners : {
				afterrender : function(c) {
					c.header.addCls('panel_curriculum_semester');
		
					/* set the background color */
					document.getElementById(c.el.id).firstChild.style.background = "#"+ semester.color;
					document.getElementById(c.el.id).lastChild.style.background = "#"+ semester.color;
					
					//console.debug(semester_body_color);
					c.body.applyStyles("background-color:" + semesterBodyColor);	
				}
			}

		});

		return semesterElement;
	};



	/**
	 * Returns a Panel, which represents a curriculum
	 */
	Curriculum.prototype.addCurriculum = function(width, height) {

		/* create the window object */
		var curriculum = Ext.create('Ext.container.Container', {
			width : width,
			height :height,
			layout : 'anchor',
			id : 'curriculum_container_' + counter,
			cls: 'curriculum',
			autoScroll : true,
			autoHeight: true,
			border: false
		});
		
		return curriculum;

	};

	/**
	 * Returns a container which represents a compulsory coursepool
	 */
	Curriculum.prototype.addCompulsoryPool = function(color, margin_left, margin_top) {
		 
		/* create the window object */
		var compulsoryPool = Ext.create('Ext.container.Container', {
			xtype : 'panel',	
			layout : 'anchor',
			border: false,
			margin : (margin_top - 1) + " 0 0 "+ (margin_left - 1),
			cls: 'compulsory-pool',
			style : {
				"background-color" : "#" + color
			}
		});

		return compulsoryPool;

	};

	/**
	 * Returns a window which will contain the content of a elective pool 
	 * 
	 */
	Curriculum.prototype.addElectivePoolWindow = function(title, min_crp, max_crp, header_color) {
		
		/* calculate the appropriate font color, depening on the chosed pool color */
		var color = this.contrastColor(header_color);

		/* create the window object */
		var window = Ext
				.create(
						'Ext.window.Window',
						{
							title : "<span class='window_title' style='color: " + color + ";'>" 
										+ title + " (Min: " + min_crp + " CrP, Max:" + max_crp + " CrP)"+
									"</span> ",
							cls : 'elective_pool_window',
							autoScroll : 'true',
							layout : 'anchor',
							closeAction : 'hide',
							modal : true,			
							bodyStyle : {
								"background-color" : "white"
							},
							listeners : {
								afterrender : function(c) {
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
	Curriculum.prototype.buildElectiveContent = function (asset) {

		var parent = asset;
		var container = this.addContainer(asset.hex_color);

		/* create the window for the childs of the given asset */
		var window = this.addElectivePoolWindow(asset.abbreviation,
				asset.min_creditpoints, asset.max_creditpoints, asset.color_hex);
		

		/* get the children of this asset */
		var childs = asset.childs;
		var compPoolFlag = false;
		
		/* return an empty window, if a pool has yet no children */
		if(typeof childs === "undefined") {
			window.add(container.add(this.addDummy()));
			return window;
		}
		
		/* select the correct structure of the pools object structure */
		if(typeof asset.childs[0].length === "undefined") {
			childs = asset.childs;
		}else {
			childs = asset.childs[0];
		}
		
		/* iterate each child of the current asset */
		for (var i = 0, len = childs.length; i < len; ++i) {
		
			var asset = this.getAsset(childs[i], parent);
			
			if (i == 0) {
				asset = this.getAsset(childs[i], parent, 2, 2);
			} else {
				
				if(compPoolFlag == true) {
					asset = this.getAsset(childs[i], parent, 1, 2);
				} else {
					asset = this.getAsset(childs[i], parent, 2, 2);
				}
			}

			/* apply the correct multi-row behaviour */
			if(container.items.length < elective_pool_window_line_break) {
				container.add(asset);
			} else {
				asset = this.getAsset(childs[i], parent, 2, 2);
				container = this.addContainer(asset.hex_color);
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
	Curriculum.prototype.addCompPoolText = function(asset) {
		
		var courseTitle = this.getTitleLabel(asset);
		var num = new Number(asset.semesters_majors_id);
		var id = num.toString();
		var linkColor = this.contrastColor(asset.color_hex);
		
		/* specify the describing text */
		if(asset.min_creditpoints == asset.max_creditpoints) {
			
			if(lang == "de") {
				var label = " insgesamt " + asset.min_creditpoints + " CrP";
			} else {
				var label = " overall " + asset.min_creditpoints + " CrP";
			}
			
		} else {
			if(lang == "de") {
				var label = " insgesamt " + asset.min_creditpoints + " CrP bis " + asset.max_creditpoints + " CrP";
			} else {
				var label = " overall " + asset.min_creditpoints + " CrP to " + asset.max_creditpoints + " CrP";
			}
		}
		
		var html = "<span style='color: "+ linkColor +";' class='course_title' id='course_title-" + asset.semesters_majors_id  + "'>" 
						+ "<b>" + courseTitle + ":</b>" 
						+ label 
						+ "<span style='margin-left:3px' class='toolcontainer' align='center' id='toolcontainer-"
						+ asset.semesters_majors_id + "'>" 
					+"</span>";
					+"</span>"
					
		
		
		
		/* create the container */
		var container = Ext.create('Ext.container.Container', {
			layout : 'hbox',
			minHeight : 17,
			margin: "0 0 3 4",
			cls: "comp_pool_text_container",
			html: html ,
			listeners : {
				afterrender : function(c) {
					
					var tooltipContent = asset.title;
					
					if(asset.note) {
						tooltipContent += "<br><br>" + asset.note;
					}
					
					if(asset.note != "" ||  asset.menu_link != 0) {
						self.addNote(asset.semesters_majors_id, tooltipContent, asset.menu_link);
					}
					
					
					//instanceOfCurriculum.addTitleTooltip(id, tooltipContent);
				}
			}
			
		});
		
		return container;
	};
	
	
	
	/**
	 * Returns a container which contains the title of a given pool (type 0)
	 */
	Curriculum.prototype.addSemesterText = function(semester) {
		
		var linkColor = this.contrastColor(semester_body_color);
		
		var html = "<span style='color: "+ linkColor +";' class='course_title'>" 
						+ semester.note 
						+"</span>";
						
		
		/* create the container */
		var container = Ext.create('Ext.container.Container', {
			layout : 'hbox',
			cls: "semester_text_container",
			html: html
			
		});
		
		return container;
	};
	

	/**
	 * Determine the childs of a pool and put them into a ExtJS container
	 */
	Curriculum.prototype.buildCompulsoryContent = function(pool_content, margin_left, margin_top) {
		
		/* get the pool container with a certain background color and margins */
		var comPool = this.addCompulsoryPool(pool_content.color_hex,  margin_left, margin_top);
		
		/* get the container element for supporting multi-row layouting */
		var container = this.addPoolContainer(pool_content.color_hex);
		
		/* true if a child is a pool of type 0. Used for calculating the correct margins */
		var compPoolFlag = false;
		

		/* apply further processing, if the pool has assigned children */
		if(pool_content.childs) {
			
			var childs = null;
			 
			/* select the correct structure of the pools object structure */
			if(typeof pool_content.childs[0].length === "undefined") {
				childs = pool_content.childs;
			}else {
				childs = pool_content.childs[0];
			}
			
			/* iterate over the subtree */
			for (var i = 0, len = childs.length; i < len; i++) { 
				 
				if(i == 0) {
					var asset = this.getAsset(childs[i], pool_content, 1, 1);
				} else {
					if(compPoolFlag == true) {
						var asset = this.getAsset(childs[i], pool_content, 1, 1);
					} else {
						var asset = this.getAsset(childs[i], pool_content, 2, 1);
					}
				}
				
				/* apply the correct multi-row behaviour */
				if(container.items.length < compulsory_pool_line_break) {
					container.add(asset);
				} else {
					container = this.addContainer(pool_content.color_hex);
					container.add(this.getAsset(childs[i], pool_content, 1, 1 ));
				}
				
				if(asset.asset_type_id == 2 && asset.pool_type == 0) {
					compPoolFlag = true;
				}
				
				comPool.add(container);
				
			}
			
		} 
		
		/* attach the name and related tooltip above the actual pool content */
		comPool.add(this.addCompPoolText(pool_content));
		
		return comPool;
	};

	
	/**
	 * Determine the type of an asset and delegate to the corresponding method
	 */
	Curriculum.prototype.getAsset = function(asset, parent, margin_left, margin_top) {
		
		/* specify the content of the mouse-over (title) */
		var tooltip_content = tooltip_content = asset.title + " (" + ((asset.lsf_course_code != "") ? asset.lsf_course_code : asset.his_course_code) + ")";

		/* determine the type of the asset and descide whoch actions must be applied */
		if(asset.asset_type_id == 1) { // Module
	 		return this.addCourse(asset, tooltip_content, margin_left, margin_top);
	 	} else if(asset.asset_type_id == 2) {  // Pool
	 		
	 		if(asset.pool_type == 0) {  // Pool type 0, child elements are displayed with a surrounded, background colored container 
	 			return this.buildCompulsoryContent(asset, margin_left, margin_top);
	 		} else { // Pool type 1, child elements are displayed within an opening (modal) window
				return this.addElectivePool(asset, this.buildElectiveContent(asset), asset.title, margin_left, margin_top);	
	 		}
	 	
	 	} else if(asset.asset_type_id == 3) {
	 		var color = null;
	 		
	 		if(typeof parent.color_hex === "undefined") {
	 			color = semester_body_color;
	 		} else {
	 			color = parent.color_hex;
	 		}

	 		return this.addDummy(color, margin_left, margin_top);
	 	}

	};

	
}