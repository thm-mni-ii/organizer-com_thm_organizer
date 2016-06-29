/**
 * Subscribe handler for enlist in a course
 *
 * @class MySched.Subscribe
 * @constructor
 * TODO I think this is obsolete
 */
MySched.Subscribe = function ()
{
    var data, grid, store, window;
    var grid1;
    return {
        /**
         * Speichert die uebergebenen Daten
         *
         * @param {Object} data
         */
        setData: function (data)
        {
            this.data = [];
            Ext.each(data, function (v)
            {
                if (v.subscribe_possible)
                {
                    this.push(v);
                }
            }, this.data);
        },
        /**
         * Zeigt das Fenster zur Auswahl der Veranstaltungen in die
         * Eingeschriebene werden soll an
         *
         * @param {Object} data Aktueller "Mein Stundenplan"
         */
        show: function (data)
        {
            this.setData(data);

            // Erstellt Fenstern
            this.window = Ext.create('Ext.Window',
                {
                    title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SUBSCRIBE,
                    id: 'subscribeWindow',
                    width: 410,
                    height: 250,
                    modal: true,
                    plain: true,
                    resizable: false,
                    layout: 'fit',
                    items: this.buildGrid()
                });
            this.window.show();
        },
        /**
         * Erstellt die Tabelle zur Auswahl
         */
        buildGrid: function ()
        {
            var sm = new Ext.grid.CheckboxSelectionModel();
            // Daten zum Einschreiben holen
            this.store = Ext.create('Ext.data.JsonStore',
                {
                    fields: [{name: 'name'}, 'subscribe', 'subscribe_info', 'subscribe_type']
                });
            this.store.loadData(this.data);

            // Erstellt die Tabelle
            this.grid = Ext.create('Ext.grid.GridPanel',
                {
                    store: this.store,
                    columns: [
                        sm,
                        {
                            dataIndex: 'name',
                            header: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON,
                            width: 120,
                            align: 'left'
                        },
                        {
                            dataIndex: 'subscribe_type',
                            header: "Typ",
                            width: 40,
                            align: 'left'
                        }],
                    stripeRows: true,
                    selModel: sm,
                    height: 250,
                    width: 400,
                    viewConfig: {
                        forceFit: true,
                        enableRowBody: true,
                        showPreview: true,
                        getRowClass: function (record, rowIndex, p, store)
                        {
                            if (this.showPreview)
                            {
                                p.body = '<p style="padding-left:25px; text-decoration:italic;">' + record.data.subscribe_info + '</p>';
                                return 'x-grid3-row-expanded';
                            }
                            return 'x-grid3-row-collapsed';
                        }
                    },
                    bbar: [
                        {
                            text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SAVE,
                            id: 'btnSave',
                            iconCls: 'tbSave',
                            handler: this.save,
                            scope: this
                        }],
                    title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_SUBSCRIBE
                });

            return this.grid;
        }
    };
}();
