Ext.define('snap.util.PagingToolbarResizer', {
    extend: 'Ext.AbstractPlugin',
    alias: 'plugin.pagingtoolbarresizer',

    requires: [
        'Ext.form.field.ComboBox'
    ],
    // options: [5, 10, 15, 20, 25, 30, 50, 75, 100, 200, 300, 500, 1000],
    options: [10, 20, 50, 75, 100, 200, 300, 500, 1000, 5000],

    mode: 'remote',

    displayText: 'Records per Page',

    recordsPerPageCmb: null,

    constructor: function (config) {

        Ext.apply(this, config);

        this.callParent(arguments);
    },

    init: function (pagingToolbar) {
        var me = this;
        var comboStore = me.options;

        me.recordsPerPageCmb = Ext.create('Ext.form.field.ComboBox', {
            typeAhead: false,
            triggerAction: 'all',
            forceSelection: true,
            lazyRender: true,
            editable: false,
            mode: me.mode,
            value: pagingToolbar.store.pageSize,
            width: 80,
            store: comboStore,
            listeners: {
                select: function (combo, value, i) {
                    pagingToolbar.store.pageSize = value.data.field1;
                    pagingToolbar.store.load();
                }
            }
        });

        var index = pagingToolbar.items.indexOf(pagingToolbar.refresh);
        pagingToolbar.insert(++index, me.displayText);
        pagingToolbar.insert(++index, me.recordsPerPageCmb);
        pagingToolbar.insert(++index, '-');

        //destroy combobox before destroying the paging toolbar
        pagingToolbar.on({
            beforedestroy: function () {
                me.recordsPerPageCmb.destroy();
            }
        });
    }
});