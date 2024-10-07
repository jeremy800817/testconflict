Ext.define('snap.view.orderdashboard.MySpotOrderSpecialMcash', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/mcash/sale',
    xtype: 'myspotorderspecialmcashview', 
    type: 'mcash',
    partnerCode : 'MCASH',
    priceStreamCode : 'Mcash',

    // temporarily use gopay price stream
    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.McashSalesPriceStream',

    ],
});