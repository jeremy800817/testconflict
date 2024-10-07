Ext.define('snap.view.orderdashboard.MySpotOrderSpecialToyyib', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/toyyib/sale',
    xtype: 'myspotorderspecialtoyyibview', 
    type: 'toyyib',
    partnerCode : 'TOYYIB',
    priceStreamCode : 'Toyyib',

    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.ToyyibSalesPriceStream',

    ],
});