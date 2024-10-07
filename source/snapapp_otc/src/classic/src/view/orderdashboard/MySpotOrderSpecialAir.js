Ext.define('snap.view.orderdashboard.MySpotOrderSpecialAir', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/air/sale',
    xtype: 'myspotorderspecialairview', 
    type: 'air',
    partnerCode : 'AIR',
    priceStreamCode : 'Air',

    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.AirSalesPriceStream',
    ],
});