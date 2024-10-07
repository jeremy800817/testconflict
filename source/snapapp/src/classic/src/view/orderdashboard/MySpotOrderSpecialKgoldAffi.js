Ext.define('snap.view.orderdashboard.MySpotOrderSpecialKgoldAffi', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/kgoldaffi/sale',
    xtype: 'myspotorderspecialkgoldaffiview', 
    type: 'kgoldaffi',
    partnerCode : 'KGOLDAFFI',
    priceStreamCode : 'kgoldaffi', //not sure about this, later confirm.

    requires: [

        //'Ext.layout.container.Fit',
        //'snap.store.GopayzSalesPriceStream',
        'snap.store.kgoldaffiSalesPriceStream'
    ],
});