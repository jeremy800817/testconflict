Ext.define('snap.util.PagingToolbar', {
    extend: 'Ext.toolbar.Paging',
    // alias: 'widget.resizer.pagingtoolbar',
    alias: 'widget.pagingtoolbarresizer',

    requires: [
        'snap.util.PagingToolbarResizer'
    ],

    toolbarResizer: null,

    initComponent: function () {
        var me = this;
        me.callParent(arguments);

        var pluginClassName = "snap.util.PagingToolbarResizer";

        me.toolbarResizer = Ext.create(pluginClassName);

        if (Ext.isEmpty(me.plugins)) {
            me.plugins = [me.toolbarResizer];
        }
        else {
            var pushTbResizer = true;
            Ext.each(me.plugins, function (plugin) {
                if (Ext.getClassName(plugin) == pluginClassName) {
                    pushTbResizer = false;
                }
            });
            if (pushTbResizer) {
                me.plugins.push(me.toolbarResizer);
            }
        }
    },

    bindStore: function (store, initial, propertyName) {
        var me = this;
        me.callParent(arguments);

        if (!Ext.isEmpty(me.toolbarResizer) && 
            !Ext.isEmpty(me.toolbarResizer.recordsPerPageCmb) && !Ext.isEmpty(store)) {
            me.toolbarResizer.recordsPerPageCmb.setValue(store.pageSize);
        }
    }
});
