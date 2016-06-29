/**
 * TODO It seems to be not in use anymore. So I did not comment
 */
Ext.define('Ext.ux.FitToParent',
    {
        /**
         * @cfg {HTMLElement/Ext.Element/String} parent The element to fit the
         *      component size to (defaults to the element the component is rendered
         *      to).
         */
        /**
         * @cfg {Boolean} fitWidth If the plugin should fit the width of the
         *      component to the parent element (default <tt>true</tt>).
         */
        fitWidth: true,
        /**
         * @cfg {Boolean} fitHeight If the plugin should fit the height of the
         *      component to the parent element (default <tt>true</tt>).
         */
        fitHeight: true,
        /**
         * @cfg {Boolean} offsets Decreases the final size with [width, height]
         *      (default <tt>[0, 0]</tt>).
         */
        offsets: [0, 0],
        /**
         * @constructor
         * @param {HTMLElement/Ext.Element/String/Object}
         *            config The parent element or configuration options.
         * @ptype fittoparent
         */
        constructor: function (config)
        {
            config = config || {};
            if (config.tagName || config.dom || Ext.isString(config))
            {
                config = {
                    parent: config
                };
            }
            Ext.apply(this, config);
        },
        init: function (c)
        {
            this.component = c;
            c.on('render', function (c)
                {
                    this.parent = Ext.get(this.parent || c.getPositionEl()
                            .dom.parentNode);
                    if (c.doLayout)
                    {
                        c.monitorResize = true;
                        c.doLayout = c.doLayout.createInterceptor(this.fitSize, this);
                    }
                    else
                    {
                        this.fitSize();
                        Ext.EventManager.onWindowResize(this.fitSize, this);
                    }
                }, this,
                {
                    single: true
                });
        },
        fitSize: function ()
        {
            var pos = this.component.getPosition(true),
                size = this.parent.getViewSize();
            this.component.setSize(this.fitWidth ? size.width - pos[0] - this.offsets[0] : undefined, this.fitHeight ? size.height - pos[1] - this.offsets[1] : undefined);
        }
    });