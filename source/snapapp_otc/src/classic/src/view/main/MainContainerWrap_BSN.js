Ext.define('snap.view.main.MainContainerWrap_BSN', {
    extend: 'Ext.container.Container',
    xtype: 'maincontainerwrap_BSN',

    requires : [
        'Ext.layout.container.HBox'
    ],

layout: {
        type: 'hbox',
        align: 'stretch',

        // Tell the layout to animate the x/width of the child items.
        animate: true,
        animatePolicy: {
            x: true,
            width: true
        }
    }
});