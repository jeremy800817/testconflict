Ext.define('snap.view.orderdashboard.MySpotOrderSpecialMib', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/mbb/sale',
    xtype: 'myspotorderspecialmibview', 
    type: 'mib',
    partnerCode : 'MIB',
    priceStreamCode : 'Mib',

    // temporarily use gopay price stream
    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.MibSalesPriceStream',

    ],
});