Ext.define('snap.view.orderdashboard.MySpotOrderSpecialBursa', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/bursa/sale',
    xtype: 'myspotorderspecialbursaview', 
    type: 'bursa',
    partnerCode : 'BURSA',
    priceStreamCode : 'Bursa',
    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.BursaSalesPriceStream',

    ],
});