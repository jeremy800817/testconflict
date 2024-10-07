Ext.define('snap.view.orderdashboard.MySpotOrderSpecialKtp', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/ktp/sale',
    xtype: 'myspotorderspecialktpview', 
    type: 'ktp',
    partnerCode : 'KTP',
    priceStreamCode : 'ktp', //not sure about this, later confirm.

    requires: [

        //'Ext.layout.container.Fit',
        //'snap.store.GopayzSalesPriceStream',
        'snap.store.ktpSalesPriceStream'
    ],
});