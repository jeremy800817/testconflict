Ext.define('snap.view.orderdashboard.MySpotOrderSpecialKopttr', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/kopttr/sale',
    xtype: 'myspotorderspecialkopttrview', 
    type: 'kopttr',
    partnerCode : 'KOPTTR',
    priceStreamCode : 'kopttr', //not sure about this, later confirm.

    requires: [

        //'Ext.layout.container.Fit',
        //'snap.store.GopayzSalesPriceStream',
        'snap.store.kopttrSalesPriceStream'
    ],
});