/*global MySched */
/**
 * Create the select boxes with schedule data
 *
 * @class MySched.SelectBoxes
 */
MySched.headerPanel = function ()
{
    return {
        /**
         * Initialization. Create panel and model. Make ajax request to get schedule data
         *
         * @method init
         * @return {Ext.panel.Panel} * Returns the main panel
         */
        init: function ()
        {
            this.headerPanel = Ext.create(
                'Ext.panel.Panel',
                {
                    id: 'headerPanel',
                    region: 'north',
                    editable: false,
                    bodyPadding: 5,
                    height: 44,
                    scroll: false
                }
            );
            return this.headerPanel;
        },

        /**
         * Set the title of the panel
         *
         * @method setTitle
         * @param {String} title The title of the panel
         * @param {Boolean} append Switch if the text should append or replace
         */
        setTitle: function(title, append)
        {
            if(append)
            {
                this.headerPanel.setTitle(this.selectPanel.title + title);
            }
            else
            {
                this.headerPanel.setTitle(title);
            }
        }
    }
}();
