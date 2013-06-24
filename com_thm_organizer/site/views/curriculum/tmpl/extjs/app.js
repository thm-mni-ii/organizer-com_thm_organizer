function App (menuID, programID, horizontalGroups, languageTag, totalWidth, totalHeight,
              horizontalPanelColor, itemWidth, defaultItemColor, electivePanelColor,
              cutTitle, titleLength, schedulerLink, itemLineBreak, electiveLineBreak,
              cumpulsoryLineBreak, counter, defaultInfoLink)
{
    var self = this;
    this.horizontalGroups = null;
    this.curriculumObj = null;
    this.curriculum = null;

    App.prototype.ajaxHandler = function(response)
    {
        curriculumObj = new Curriculum(menuID, programID, horizontalGroups, languageTag, totalWidth, totalHeight,
                                       horizontalPanelColor, itemWidth, defaultItemColor, electivePanelColor,
                                       cutTitle, titleLength, schedulerLink, itemLineBreak, electiveLineBreak,
                                       cumpulsoryLineBreak, counter, defaultInfoLink);
        curriculum = curriculumObj.getCurriculumPanel();
		
        var program = Ext.decode(response.responseText);
        horizontalGroups = program.children;
		
        /* iterate over first order children of the program's curriculum */
        for ( var firstOrder in horizontalGroups)
        {
            var horizontalGroup = curriculumObj.getHorizontalGroupPanel(horizontalGroups[firstOrder]);
            var items = horizontalGroups[firstOrder].children;
            var container = curriculumObj.getContainer(horizontalPanelColor);
            var compPoolFlag = false;
            var textContainer = curriculumObj.getTextContainer(horizontalGroups[firstOrder]);
            horizontalGroup.add(textContainer)
			
            /* iterate over 2nd order children */
            for ( var secondOrder in items )
            {
                var item = curriculumObj.getAsset(items[secondOrder], horizontalGroups[firstOrder], 2, 2);

                if (container.items.length >= itemLineBreak)
                {
                    container = curriculumObj.getContainer(horizontalPanelColor);
                }
                container.add(item);

                horizontalGroup.add(container);
            }
            curriculum.add(horizontalGroup);
        }
		
        curriculum.doLayout();

        var sele = "loading_"+ counter;

        Ext.Element.get(sele).destroy();
        var sele = "curriculum_"+ counter;
        curriculum.render(Ext.Element.get(sele));

    }
	
    App.prototype.performAjaxCall = function()
    {
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
            }
        );
        var requestURL = 'index.php?option=com_thm_organizer&view=curriculum_ajax&task=getCurriculum&format=raw&id=';
        requestURL += programID + '&Itemid=' + menuID + '&lang=' + languageTag;
        Ext.Ajax.request({
            url : requestURL,
            method : "GET",
            success : self.ajaxHandler
        });

    }
}

