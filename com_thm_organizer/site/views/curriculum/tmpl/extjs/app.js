function App (item_id, major, semester, lang, width, height, 
			semester_body_color, course_width, course_body_color, elective_pool_body_color, 
			title_cut_length_activate, title_cut_length, scheduler_link, asset_line_break, elective_pool_window_line_break,
			compulsory_pool_line_break, counter, default_info_link) {
	
	var self = this;

	
	var semesters = null;
	var curriculumObj = null
	var curriculum = null;
	
	var width = width;
	var height = height;
	var major_id = major;
	var itemid = item_id;
	var counter = counter;
	var lang = lang;
	var selectedSemesters = semester;
	
	App.prototype.ajaxHandler = function(response, opts) {

		curriculumObj = new Curriculum(item_id, major, semester, lang, width, height, 
									semester_body_color, course_width, course_body_color, elective_pool_body_color, 
									title_cut_length_activate, title_cut_length, scheduler_link, asset_line_break, elective_pool_window_line_break,
									compulsory_pool_line_break, counter, default_info_link);
		curriculum = curriculumObj.addCurriculum(width, height);
		
		var major = Ext.decode(response.responseText);
		semesters = major[0].childs[0];
		
		/* iterate over each semester of this curriculum */
		for ( var i = 0, len = semesters.length; i < len; ++i) {
			
			var semester = curriculumObj.addSemester(semesters[i]);

			/* get the releated assets of this semester */
			var assets = semesters[i].childs;
			
			if(typeof assets === "undefined") {
				continue;
			}
			
			var container = curriculumObj.addContainer(semester_body_color);
			var compPoolFlag = false;
			var textContainer = curriculumObj.addSemesterText(semesters[i]);
			semester.add(textContainer)
			
			
			/* iterate over each asset */
			for ( var j = 0, len2 = assets.length; j < len2; ++j) {
				
				var asset = null;

				if (j == 0) {
					asset = curriculumObj.getAsset(assets[j], semesters[i], 2, 2);
				} else {
					
					if(compPoolFlag == true) {
						asset = curriculumObj.getAsset( assets[j], semesters[i], 1, 2);
						compPoolFlag = false;
					} else {
						asset = curriculumObj.getAsset( assets[j], semesters[i], 2, 2);
					}
				}

				if (container.items.length < asset_line_break) {
					container.add(asset);
				} else {

					asset = curriculumObj.getAsset(assets[j], semesters[i], 2, 2);

					container = curriculumObj.addContainer(semester_body_color);
					container.add(asset);
				}

				semester.add(container);
				
				if(assets[j].asset_type_id == 2 && assets[j].pool_type == 0) {
					compPoolFlag = true;
				}
			}
			
			curriculum.add(semester);

		}
		
		
		curriculum.doLayout();
		
		var sele = "loading_"+ counter;
		
		Ext.Element.get(sele).destroy();
		
		
		var sele = "curriculum_"+ counter;
		curriculum.render(Ext.Element.get(sele));

	}
	
	App.prototype.performAjaxCall = function() {
		
		var sele = "loading_"+ counter;
		
		
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
							src : loading_icon,
						} ]
					},
					renderTo : sele
			});
		
		
		
		
		Ext.Ajax
				.request({
					url : 'index.php?option=com_thm_organizer&task=curriculum.getJSONCurriculum&tmpl=component&id='
							+ major_id
							+ '&Itemid='
							+ itemid
							+ '&lang='
							+ lang 
							+ '&semesters=' 
							+ selectedSemesters,
					method : "GET",
					success : self.ajaxHandler
				});
	
	}
}

