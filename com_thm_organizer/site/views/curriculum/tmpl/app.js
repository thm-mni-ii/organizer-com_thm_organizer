Curriculum = function() {
	var asset_counter = null;
	var instance = null;
	
	this.constructor = function() {
		
		this.instance = new Curriculum();
		
		this.asset_counter = new Array();
		
		return this.instance;
	};
	
	this.addNote = function(id, note) {

		var noteImage = new Ext.create(
				'Ext.Component',
				{
					xtype : 'box',
					autoEl : {
						tag : 'a',
						href : note_link,
						children : [ {
							tag : 'img',
							id : 'responsible-image',
							cls : 'tooltip',
							src : 'http://cdn1.iconfinder.com/data/icons/lullacons/info.png',
						} ]
					},
					renderTo : 'toolcontainer-'
						+ id
				});
		
		new Ext.ToolTip({
			target : noteImage.el.id,
			title : (lang == 'de') ? 'Info' : 'Note',
			html : '<br>' + note,
			autoHide : true
			});	
		
	};
	
	this.addTooltip = function(container_id, title, schedule) {
		
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
							src : 'http://cdn1.iconfinder.com/data/icons/crystalproject/16x16/mimetypes/schedule.png',
						} ]
					},
					renderTo : 'toolcontainer-'
						+ container_id
				});
		

		new Ext.ToolTip({
			target : tooltipImage.el.id,
			html : schedule,
			autoHide : true
		});
			
		
		
	};

	
	this.addResponsible = function(id, responsible, responsible_name) {
		
		var responsilbe_tooltip = new Ext.create(
				'Ext.Component',
				{
					xtype : 'box',
					autoEl : {
						tag : 'a',
						href : responsible,
						children : [ {
							tag : 'img',
							src : 'http://cdn1.iconfinder.com/data/icons/humano2/16x16/emblems/emblem-people.png'
						} ]
					},
					renderTo : 'toolcontainer-' + id
				});
		
		
		new Ext.ToolTip({
			target : responsilbe_tooltip.el.id,
			html : responsible_name,
			autoHide : true
			});
				
			
	};

	this.addTitleTooltip = function(id, title, course_code) {
		
		
		new Ext.ToolTip({
			target : 'course_title-' + id ,
			title : 'Details:',
			//html : title + " (" + course_code + ")", //@TODO: fallunterscheidung
			html : title,
			autoHide : true
			});

	};

	this.addDummy = function() {

		/* creates the panel */
		var dummy = Ext
				.create(
						'Ext.panel.Panel',
						{
							xtype : 'panel',
							cls: 'dummy',
							bodyCls: 'dummy_body',
							width : course_width,
							height : 65,
							margin : '0 2 0 0'
						});

		return dummy;

	};




	/**
	 * Returns a panel which represents a course 
	 * 
	 * @param abbreviation
	 * @param short_title
	 * @param title
	 * @param creditpoints
	 * @param lsf_course_id
	 * @param asset_count
	 * @param schedule
	 * @param responsible
	 * @param color
	 * @param note
	 * @param idd
	 * @returns
	 */
	 this.addCourse = function(abbreviation, short_title, title, creditpoints,
			lsf_course_id, schedule, responsible, color, note, id, course_code, responsible_name) {

		/* Convert the course id to a string */
		var num = new Number(id);
		var id = num.toString();
		
		var instanceOfCurriculum = this;
		
		/* creates the panel */
		var course = Ext
				.create(
						'Ext.panel.Panel',
						{
							xtype : 'panel',
							cls: 'course',
							bodyCls: 'course_body',
							title : "<span class='course_title'>"
									+ abbreviation
									+ "</span><span class='course_creditpoints'>"
									+ creditpoints + " CP</span>",
							width : course_width,
							height : 65,
							margin : '0 2 0 0',
							bodyStyle : {
								"background-color" : "#E5E5E5",
								"border-top-style" : "none"
							},
							listeners : {
								afterrender : function(c) {
									
									/* set the css class for the header panel */
									c.header.addCls('course_panel_header');
									
									/* set the background color */
									document.getElementById(c.el.id).firstChild.style.background = "#" + color;
									
									/* add the toolbar */
									
									if(schedule != null) {
									
										instanceOfCurriculum.addTooltip(id, title, schedule);
									}
									
									if(note != "") {
										instanceOfCurriculum.addNote(id, note);
									}
									
									if(responsible != "") {
										instanceOfCurriculum.addResponsible(id, responsible,responsible_name); 
									}
									
									instanceOfCurriculum.addTitleTooltip(id, title, course_code);

								}
							},
							layout : {
								type : 'vbox',
								align : 'center'
							},
							items : [
									{
										xtype : 'container',
										anchor : '100% 50%',
										html : "<div id='course_title-" + id  + "' align='center' class='course-title-container'><a class='course-title' href='http://website.mni.fh-giessen.de/index.php/studium/informatik-bachelor/modulhandbuch-informatik-bsc/details/"
												+ lsf_course_id
												+ "'>"
												+ ((title_cut_length_activate == 1) ? (short_title.substring(0, title_cut_length) + "...") : short_title)  + "</a></div>",
										flex : 1,
										margins : '-9 0 0 0',
									},
									{
										xtype : 'container',
										anchor : '70% 50%',
										html : "<div class='toolcontainer' id='toolcontainer-"
												+ id + "'></div>",
										margins : '25 20 10 23',
										flex : 1
									} ]

						});

		return course;

	};


	/**
	 * Returns a Panel which represents an elective course pool
	 * 
	 * @param abbreviation
	 * @param short_title
	 * @param min_crp
	 * @param max_crp
	 * @param window
	 * @param assetid
	 * @param color
	 * @param note
	 * @returns
	 */
	 this.addElectivePool = function(abbreviation, short_title, min_crp, max_crp, window,
			assetid, color, note, id, tooltip, parent_color) {
		
		/* set the creditpoints label */
		var creditpoints = '';
		
		var instanceOfCurriculum = this;
		
		if(min_crp == max_crp) {
			creditpoints = min_crp;
		} else {
			creditpoints =  min_crp + "-" + max_crp;
		}

		var electivePool = Ext
				.create(
						'Ext.panel.Panel',
						{
							xtype : 'container',
							width : 100,
							height : 65,
							cls: 'elective-pool',
							layout : 'absolute',
							margin : '0 2 0 0',
							bodyStyle : {
								"border" : "none",
								"background-color" : "#" + parent_color
							},
							items : [
									{
										xtype : 'panel',
										title : '',
										x : 20,
										y : 10,
										height : 65,
										width : 90,
										shadow : 'drop',
										shadowOffset : 20,
										bodyStyle : {
											"background-color" : "#E5E5E5"
										},
									},
									{
										xtype : 'panel',
										title : '',
										x : 8,
										y : 8,
										height : 55,
										width : 90,
										bodyStyle : {
											"background-color" : "#E5E5E5"
										},
									},
									{
										xtype : 'panel',
										title : "<span class='elective_pool_title course_title' >"
												+ abbreviation
												+ "</span><span class='elective_pool_creditpoints'>"
												+ creditpoints
												+ " CP</span>",
										x : 0,
										y : 0,
										width : 95,
										height : 60,
										plain : true,
										listeners : {
											afterrender : function(c) {
												c.header.addCls('elective_pool_panel_header');
												document.getElementById(c.el.id).firstChild.style.background = "#" + color;
												
												
												if(note != "") {
													instanceOfCurriculum.addNote(assetid, note);
												}
												
												instanceOfCurriculum.addTitleTooltip(id, tooltip, '');
												
											}
										},
										bodyStyle : {
											"background-color" : "#E5E5E5"
										},
										items : [
												{
													xtype : 'container',
													anchor : '70% 50%',
													html : "<div id='course_title-" + id  + "' align='center' class='course-title-container'><a href='#' class='course-title pool"
															+ assetid
															+ "' >"
															+ ((title_cut_length_activate == 1) ? (short_title.substring(0, title_cut_length) + "...") : short_title)
															+ "</a></div>",
													margins : '0 0 0 0',
													flex : 1,
													listeners : {
														afterrender : function(c) {
															var selector = ".pool"
																	+ assetid;

															Ext.select(selector).on('click', function(e) {
																				window.show();
																	})
														}
													},
												},
												{
													xtype : 'container',
													anchor : '70% 110%',
													html : "<div class='toolcontainer' align='center' id='toolcontainer-"
															+ assetid + "'></div>",
													margins : '-10 0 0 0',
													flex : 1
												}, ]
									} ]

						});

		return electivePool;

	};


	/**
	 * Returns a Panel which represents a semester
	 * 
	 * @param semester
	 * @returns
	 */
	 this.addSemester = function(semester) {

		var semester = Ext.create('Ext.panel.Panel', {
			xtype : 'panel',
			title : '<font style="font-size: 12px;">' + semester + '</font>',
			layout : 'anchor',
			cls: 'semester',
			margin : '3 8 0 8',
			//autoScroll : true,
			autoHeight: true,
			collapsible : true,
			listeners : {
				afterrender : function(c) {
					c.header.addCls('panel_curriculum_semester');
				}

			}

		});

		return semester;

	};



	/**
	 * Returns a Panel which represents a curricuculum
	 * 
	 * @returns
	 */
	 this.addCurriculum = function(width, height) {

		var curriculum = Ext.create('Ext.panel.Panel', {
			width : width,
			height : height,
			layout : 'anchor',
			id : 'curriculum_container',
			cls: 'curriculum',
			autoScroll : true,
			autoHeight: true,
			listeners : {
				afterrender : function(c) {
					c.body.applyStyles("background-color: #EFEFEF");
				}
			}

		});
		return curriculum;

	};

	/**
	 * Returns a Panel which represents a compulsory coursepool
	 * 
	 * @param name
	 * @param min_creditpoints
	 * @param max_creditpoints
	 * @returns
	 */
	 this.addCompulsoryPool = function(name, min_creditpoints, max_creditpoints, color) {

		var compulsoryPool = Ext.create('Ext.panel.Panel', {
			xtype : 'panel',	
			layout : 'anchor',
			margin : '0 1 1 1',
			cls: 'compulsory-pool',
			bodyStyle : {
				"background-color" : "#" + color
			},
			listeners : {
				afterrender : function(c) {
					
				}
			}

		});

		return compulsoryPool;

	};

	/**
	 * Returns a Window which will contain the content of a elective pool 
	 * 
	 * @param title
	 * @param min_crp
	 * @param max_crp
	 * @returns
	 */
	 this.addElectivePoolWindow = function(title, min_crp, max_crp, header_color) {

		var window = Ext.create('Ext.window.Window', {
			title : '<font class="window_title">' + title
					+ '</font><span class="window_creditpoints" >' + min_crp + ' - '
					+ max_crp + ' CP</span>',
			cls: 'elective-pool-window',
			layout : 'anchor',
			closeAction : 'hide',
			listeners : {
				afterrender : function(c) {	
					/* set the css class for the header panel */
					c.header.addCls('elective_pool_window_header');
					
					/* set the background color */
					document.getElementById(c.header.id).firstChild.style.background = "#" + header_color;
				}

			},
			modal : true,
			bodyStyle : {
				"background-color" : "white"
			}
		});

		return window;

	};



	/**
	 * 
	 * @param asset
	 * @returns
	 */
	this.buildNestedStructure = function (asset) {
		
		var container = this.addContainer();

		/* create the window for the childs of the given asset */
		var window = this.addElectivePoolWindow(asset.abbreviation,
				asset.min_creditpoints, asset.max_creditpoints, asset.color_hex);

		/* get the children of this asset */
		var sub_assets = asset.childs;

		
		
		if(typeof asset.childs[0] === "undefined") {
			return;
		} else {
			sub_assets = asset.childs[0];
		}
			
			/* iterate each child of the current asset */
			for ( var i = 0, len = sub_assets.length; i < len; ++i) {
				
				var asset = null;
				
				if (sub_assets[i].asset_type_id == 1) {

					var course = this.addCourse(sub_assets[i].abbreviation,
							sub_assets[i].short_title, sub_assets[i].title_de,
							sub_assets[i].min_creditpoints,
							sub_assets[i].lsf_course_id,
							sub_assets[i].schedule, sub_assets[i].responsible,sub_assets[i].color_hex,
							sub_assets[i].note,
							sub_assets[i].semesters_majors_id,
							sub_assets[i].lsf_course_code,
							sub_assets[i].responsible_name);

					asset = course;

				} else if (sub_assets[i].asset_type_id == 2) { 
					
				
					if(sub_assets[i].min_creditpoints != sub_assets[i].max_creditpoints) {
						var wp_pool = addElectivePool(sub_assets[i].abbreviation,
								sub_assets[i].short_title,
								sub_assets[i].min_creditpoints,
								sub_assets[i].max_creditpoints,
								buildNestedStructure(sub_assets[i]), sub_assets[i].id,sub_assets[i].color_hex,
								sub_assets[i].note);
			
		
						asset = wp_pool;
					} else {
						
						
						/* the current asset is a compulsory pool */
						var compPool = addCompulsoryPool(sub_assets[i].title_de, sub_assets[i].min_creditpoints, sub_assets[i].min_creditpoints, sub_assets[i].color_hex);
						var pf_pool_assets = sub_assets[i].childs[0];
						
						/* iterate over each child of this pool */
						for ( var k = 0, len3 = pf_pool_assets.length; k < len3; ++k) {
							
						
							
							var title = getTitleLabel(pf_pool_assets[k]);
							var tooltip_content = pf_pool_assets[k].title_de 
													+ "<br><br>" + "Pool:" + "<br>" + sub_assets[i].title_de
													+ " (" + sub_assets[i].min_creditpoints + "-" + sub_assets[i].max_creditpoints + " CP)"
													+ "<br><br>"+  "Info:" + "<br>"
													+ sub_assets[i].note;
							
							if (pf_pool_assets[k].asset_type_id == 1) {
		
								var course = addCourse(
										pf_pool_assets[k].abbreviation,
										title, tooltip_content,
										pf_pool_assets[k].min_creditpoints,
										pf_pool_assets[k].lsf_course_id,
										pf_pool_assets[k].schedule,
										pf_pool_assets[k].responsible,
										pf_pool_assets[k].color_hex,
										pf_pool_assets[k].note,
										pf_pool_assets[k].semesters_majors_id,
										pf_pool_assets[k].lsf_course_code,
										pf_pool_assets[k].responsible_name);
								
								/* add the course as a child of the current pool */
								compPool.add(course);
							} else if (sub_assets[i].asset_type_id == 2) {
								var wp_pool = addElectivePool(
										pf_pool_assets[k].abbreviation,
										pf_pool_assets[k].short_title,
										pf_pool_assets[k].min_creditpoints,
										pf_pool_assets[k].max_creditpoints,
										buildNestedStructure(pf_pool_assets[k]),
										pf_pool_assets[k].semesters_majors_id,
										pf_pool_assets[k].color_hex,
										pf_pool_assets[k].note,
										pf_pool_assets[k].semesters_majors_id,
										tooltip_content,
										sub_assets[i].color_hex);
		
								/* add the elective pool as a child of the current pool */
								compPool.add(wp_pool);
		
							} else if (sub_assets[i].asset_type_id == 3) {
								var dummy = addDummy();
								
								/* add the elective pool as a child of the current pool */
								compPool.add(dummy);
							}
						}
		
						asset = compPool;
						
						
					}
						
				} else if (sub_assets[i].asset_type_id == 3) {
					
					asset = addDummy();
					
				}
				
				
				
				
				
				if(container.items.length < elective_pool_window_line_break) {
					container.add(asset);
				} else {

					container = addContainer();
					container.add(asset);
				}
				
				window.add(container);

			}


		return window;
	};


	 this.addContainer = function(color) {
		
		var container = Ext.create('Ext.panel.Panel', {
			layout : 'hbox',
			border : 0,
			width: 0,
			height : 66,
			margin: '2 2 2 2',
			bodyStyle : {
				"background-color" : "#" + color
			},
			listeners : {
				afterrender : function(c) {
				
					/* set the correct size of the container */
					var width = 0;
					var height = 0;

					for (var k = 0; k < this.items.length; k++) {
						width += this.items.get(k).getWidth() + 3 ;

						if (this.items.get(k).getHeight() > height) {
							height = this.items.get(k).getHeight() ;
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

	this.getTitleLabel = function(assetAttribute) {
		
		/* set the correct label */
		var title = null;

		if (assetAttribute.short_title == "") {
			title = assetAttribute.title;
		} else {
			title = assetAttribute.short_title;
		}
		
		return title;
	};

	 this.addCoursepool = function(asset) {
		 
		 var instanceOfCurriculum = new Curriculum();
		
		if(asset.min_creditpoints == asset.max_creditpoints) {
			
			var container = this.addContainer(asset.color_hex);
		
			/* the current asset is a compulsory pool */
			var compPool = this.addCompulsoryPool(asset.title_de, asset.min_creditpoints, asset.min_creditpoints, asset.color_hex);
			var pf_pool_assets = asset.childs;
			
			/* iterate over each child of this pool */
			for ( var k = 0, len3 = pf_pool_assets.length; k < len3; ++k) {
				
				var title = this.getTitleLabel(pf_pool_assets[k]);
				var tooltip_content = pf_pool_assets[k].title_de 
										+ "<br><br>" + "Pool:" + "<br>" + asset.title_de
										+ " (" + asset.min_creditpoints + "-" + asset.max_creditpoints + " CP)"
										+ "<br><br>"+  "Info:" + "<br>"
										+ asset.note;
				var asset2 = null;
				
				if (pf_pool_assets[k].asset_type_id == 1) {

					var course = this.addCourse(
							pf_pool_assets[k].abbreviation,
							title, tooltip_content,
							pf_pool_assets[k].min_creditpoints,
							pf_pool_assets[k].lsf_course_id,
							pf_pool_assets[k].schedule,
							pf_pool_assets[k].responsible,
							pf_pool_assets[k].color_hex,
							pf_pool_assets[k].note,
							pf_pool_assets[k].semesters_majors_id,
							pf_pool_assets[k].lsf_course_code,
							pf_pool_assets[k].responsible_name);
					
					/* add the course as a child of the current pool */
						
					asset2 = course;

				} else if (pf_pool_assets[k].asset_type_id == 2) {
					//alert(pf_pool_assets[k].abbreviation);

					var wp_pool = this.addElectivePool(
							pf_pool_assets[k].abbreviation,
							pf_pool_assets[k].short_title,
							pf_pool_assets[k].min_creditpoints,
							pf_pool_assets[k].max_creditpoints,
							this.buildNestedStructure(pf_pool_assets[k]),
							pf_pool_assets[k].semesters_majors_id,
							pf_pool_assets[k].color_hex,
							pf_pool_assets[k].note,
							pf_pool_assets[k].semesters_majors_id,
							tooltip_content,
							asset.color_hex);

					/* add the elective pool as a child of the current pool */
					
					
					asset2 = wp_pool;

				} else if (pf_pool_assets[k].asset_type_id == 3) {
					asset2 = this,addDummy();
				}
				
				if(container.items.length < compulsory_pool_line_break) {
					container.add(asset2);
				} else {
					
					container = this.addContainer(asset.color_hex);
					container.add(asset2);
				}
				compPool.add(container);
			}
			
			
			
			

			return compPool;
			

		} else {
			
			

			var wp_pool = this.addElectivePool(
					asset.abbreviation,
					asset.short_title,
					asset.min_creditpoints,
					asset.max_creditpoints,
					this.buildNestedStructure(asset),
					asset.semesters_majors_id,
					asset.color_hex,
					asset.note);
			
			return wp_pool;

		
		}
		
	};
	
}



Ext.Loader.setConfig({
	enabled : true,
	paths : {
		'Ext' : 'components/com_thm_organizer/views/curriculum/tmpl/ext-4.0'
	}
});

Ext.application({
			name : 'AM',
			appFolder : 'app',
			launch : function() {
				
				var curriculumObj = new Curriculum();
				curriculumObj.constructor();
				console.debug(curriculumObj);
				
				var curriculum = curriculumObj.addCurriculum(width, height);
				
				console.debug(curriculum);
				var asset_list = new Array();

				 Ext.Ajax
						.request({
							url : 'index.php?option=com_thm_organizer&task=curriculum.getJSONCurriculum&tmpl=component&id=' + major_id + '&Itemid=' + itemid ,
							method : "GET",
							success : function(response) {
								var major = Ext.decode(response.responseText);
								var semesters = major[0].childs[0];

								/* iterate over each semester of this curriculum */
								for ( var i = 0, len = semesters.length; i < len; ++i) {

									/* add this semester to this curriculum */
									
									//curriculum.addContainer();
									var semester = curriculumObj.addSemester(semesters[i].name);
									
									/* get the releated assets of this semester */
									var assets = semesters[i].childs;
									
									var container = curriculumObj.addContainer();
											
									/* iterate over each asset */
									for ( var j = 0, len2 = assets.length; j < len2; ++j) {
									
										
										/* check whether the current asset is already in a previous semester */
										if(Ext.Array.contains(asset_list, assets[j].title_de)) {
											//asset_counter[assets[j].title_de] = 0;
											
										
												
											//assets[j].title_de += (lang == 'de') ? ' Teil ' + asset_counter[assets[j].title_de] : ' Part 2';	// adjust the asset title
			
										}
										
										/* save the current title */
										asset_list.push(assets[j].title_de);
										
										/* set the correct label */
										var title = curriculumObj.getTitleLabel(assets[j]);

										/* Course */
										if (assets[j].asset_type_id == 1) { 
											
											var course = curriculumObj.addCourse(
													assets[j].abbreviation,
													title, assets[j].title_de,
													assets[j].min_creditpoints,
													assets[j].lsf_course_id,
													assets[j].schedule,
													assets[j].responsible,
													assets[j].color_hex,
													assets[j].note,
													assets[j].semesters_majors_id,
													assets[j].lsf_course_code,
													assets[j].responsible_name);
											
											/* add the course to the semester */
	
											if(container.items.length < asset_line_break) {
												container.add(course);
											} else {
												container = curriculumObj.addContainer();
												container.add(course);
											}
		
											semester.add(container);
											
										} else if (assets[j].asset_type_id == 2) { // Coursepool
											
											var pool = curriculumObj.addCoursepool(assets[j]);
											
											/* attach the compulsory pool to the semester */
											
											if(container.items.length < asset_line_break) {
												container.add(pool);
											} else {
												container = curriculumObj.addContainer();
												container.add(pool);
											}
											semester.add(container);
											
						
										}  else if (assets[j].asset_type_id == 3) { // Dummy
											
											var dummy = curriculumObj.addDummy();
		
											if(container.items.length < asset_line_break) {
												container.add(dummy);
											} else {
												container = addContainer();
												container.add(dummy);
											}
											semester.add(container);
											
											
										}
									
									}

									curriculum.add(semester);

								}
							}
						});

				curriculum.doLayout();
				
				curriculum.render(Ext.Element.get('curriculum'));

			}
		});