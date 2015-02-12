/**
 * Shows information for a chosen lecture
 * TODO: Maybe obsolete, it seems to be never used
 *
 * @class MySched.InfoPanel
 * @constructor
 */
MySched.InfoPanel = function ()
{
    "use strict";

    var el = null;
    return {
        /**
         * Initialize InfoPanel
         *
         * @method init
         */
        init: function ()
        {
            console.log("MySched.InfoPanel init: init is the only method that is called form InfoPanel. Is it in use anymore?");
            this.el = Ext.get('infoPanel');
        },
        /**
         * Zeigt eine Info in dem Info Panel unterhalb des Baumes an
         *
         * @param {Object} el HTML Element welches selektiert wurde
         */
        showInfo: function (el)
        {
            console.log("MySched.InfoPanel showInfo: maybe never used?");
            var text = false;
            if (Ext.type(el) === 'object')
            {

                var l = MySched.Base.getLecture(el.id);
                if (l)
                {
                    text = l.showInfoPanel();
                }
            }
            else
            {
                text = el;
            }
            if (!text)
            {
                this.el.update(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_ERROR);
            }
            else
            {
                this.el.update(text);
            }
            // Updated Handler fuer Detailinfos
            this.updateDetailInfoClickHandler();
        },
        /**
         * Erneuert die onClick events der InfoIcons innerhalb des InfoPanels
         */
        updateDetailInfoClickHandler: function ()
        {
            console.log("MySched.InfoPanel updateDetailInfoClickHandler: maybe never used?");
            this.el.select('.detailInfoBtn')
                .on('click', this.detailInfoClick,
                this);
        },
        /**
         * Wird aufgerufen wenn ein blaues Informationsicon fuer Detailinfos
         * geklickt wird
         *
         * @param {Object} e Event welches ausgeloest wurde
         */
        detailInfoClick: function (e)
        {
            console.log("MySched.InfoPanel detailInfoClick: maybe never used?");
            // Splitte Id - zb. info_room_i136
            var tmp = e.target.id.split('_');
            // Holt die geforderte Info vom Server ab.
            Ext.Ajax.request(
                {
                    url: _C('infoUrl'),
                    params: {
                        type: tmp[1],
                        key: tmp[2],
                        viewMode: _C('infoMode')
                    },
                    method: 'POST',
                    failure: function ()
                    {
                        Ext.Msg.alert(
                            MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_NOTICE,
                            MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_NOTICE_ERROR);
                    },
                    scope: this,
                    success: function (resp)
                    {
                        try
                        {
                            var json = Ext.decode(resp.responseText);
                            if (!json.success)
                            {
                                if (!json.error)
                                {
                                    json.error = MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_UNKNOWN_ERROR;
                                }
                                this.showDetailInfo(json.error, MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR);
                                return;
                            }
                            // Zeigt ermittelte Info an
                            this.showDetailInfo( Ext.Template(json.template).apply(json.data), MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_INFO);
                        }
                        catch (e)
                        {}
                    }
                });
        },
        /**
         * Zeigt Detailierte Info an
         *
         * @param {Object} text
         * @param {Object} title
         */
        showDetailInfo: function (text, title)
        {
            console.log("MySched.InfoPanel showDetailInfo: maybe never used?");
            var mode = _C('infoMode');
            // Je nach Mode wird es im normalen InfoFenster oder als Popup
            // angezeigt.
            if (mode === 'layout')
            {
                this.showInfo(text);
            }
            else if (mode === 'popup')
            {
                Ext.Msg.show(
                    {
                        title: title,
                        buttons: {
                            cancel: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_CLOSE
                        },
                        msg: text,
                        width: 400,
                        modal: false,
                        closable: true
                    });
            }
        },
        /**
         * Leert das InfoFenster
         */
        clearInfo: function ()
        {
            console.log("MySched.InfoPanel clearInfo: maybe never used?");
            Ext.get('infoPanel')
                .update('');
        }
    };
}();
