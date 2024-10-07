Ext.define('snap.view.orderdashboard.SalesBorderList', {
    extend: 'Ext.panel.Panel',
    xtype: 'sales-border',
    requires: [
        'Ext.layout.container.Border'
    ],
    profiles: {
        classic: {
            itemHeight: 100
        },
        neptune: {
            itemHeight: 100
        },
        graphite: {
            itemHeight: 120
        },
        'classic-material': {
            itemHeight: 120
        }
    },
    layout: 'border',
    width: 500,
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',

    bodyBorder: false,

    defaults: {
        collapsible: true,
        split: true,
        bodyPadding: 10
    },

    items: [
        {
            //title: 'Vault',
            header: {
                style: {
                        backgroundColor: 'white',
                        display: 'inline-block',
                        color: '#000000',
                        
                    }
            },
            region: 'center',
            collapsible: false,
            margin: '5 0 0 0',
            xtype: 'salesview'
        },
        /*{
            title: 'Sales Order Handling',
            reference: 'salesorderhandlingpanel',
            region: 'east',
            collapsible: true,
            collapsed: true,
            animCollapse: false,
            //collapseDirection: Ext.Component.DIRECTION_BOTTOM,
            titleCollapse: true,
            floatable: false,
            margin: '5 0 0 0',
            id: 'salesorderhandlingpanel',
            xtype: 'salesorderhandlingview',
        },*/
        
    ]
});