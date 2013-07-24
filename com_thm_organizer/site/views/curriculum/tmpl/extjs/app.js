/*globals Ext, loading_icon, Curriculum */
/*jshint strict: false */
function App (menuID, programID, horizontalGroups, languageTag, totalWidth, totalHeight,
              horizontalPanelColor, itemWidth, defaultItemColor, electivePanelColor,
              cutTitle, titleLength, schedulerLink, itemLineBreak, electiveLineBreak,
              cumpulsoryLineBreak, counter, defaultInfoLink)
{
    this.horizontalGroups = null;
    this.curriculumObj = null;
    this.curriculum = null;

    App.prototype.ajaxHandler = function(response)
    {
        this.curriculumObj = new Curriculum(menuID, programID, horizontalGroups, languageTag, totalWidth, totalHeight,
                                       horizontalPanelColor, itemWidth, defaultItemColor, electivePanelColor,
                                       cutTitle, titleLength, schedulerLink, itemLineBreak, electiveLineBreak,
                                       cumpulsoryLineBreak, counter, defaultInfoLink);
        this.curriculum = this.curriculumObj.getCurriculumPanel();

        var program = Ext.decode(response.responseText);
        horizontalGroups = program.children;

        /* iterate over first order children of the program's curriculum */
        for ( var firstOrder in horizontalGroups)
        {
            if (horizontalGroups.hasOwnProperty(firstOrder))
            {
                var horizontalGroup = this.curriculumObj.getHorizontalGroupPanel(horizontalGroups[firstOrder]);
                var items = horizontalGroups[firstOrder].children;
                if (items.length === 0)
                {
                    continue;
                }
                var container = this.curriculumObj.getContainer(horizontalPanelColor);
                var compPoolFlag = false;
                var textContainer = this.curriculumObj.getTextContainer(horizontalGroups[firstOrder]);
                horizontalGroup.add(textContainer);

                /* iterate over 2nd order children */
                for ( var secondOrder in items )
                {
                    if (items.hasOwnProperty(secondOrder))
                    {
                        var item = this.curriculumObj.getAsset(items[secondOrder], horizontalGroups[firstOrder], 2, 2);

                        if (container.items.length >= itemLineBreak)
                        {
                            container = this.curriculumObj.getContainer(horizontalPanelColor);
                        }
                        container.add(item);

                        horizontalGroup.add(container);
                    }
                }
                this.curriculum.add(horizontalGroup);
            }
        }

        this.curriculum.doLayout();

        var sele = "loading_"+ counter;

        Ext.Element.get(sele).destroy();
        sele = "curriculum_"+ counter;
        this.curriculum.render(Ext.Element.get(sele));
    };

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
                        src : loading_icon
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