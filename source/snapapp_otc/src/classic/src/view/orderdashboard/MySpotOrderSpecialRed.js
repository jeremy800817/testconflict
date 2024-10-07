Ext.define('snap.view.orderdashboard.MySpotOrderSpecialRed', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/red/sale',
    xtype: 'myspotorderspecialredview', 
    type: 'red',
    partnerCode : 'RED',
    priceStreamCode : 'Red',

    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.RedSalesPriceStream',

    ],
});