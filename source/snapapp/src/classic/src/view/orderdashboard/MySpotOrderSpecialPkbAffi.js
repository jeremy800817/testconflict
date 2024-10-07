Ext.define('snap.view.orderdashboard.MySpotOrderSpecialPkbAffi', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/pkbaffi/sale',
    xtype: 'myspotorderspecialpkbaffiview', 
    type: 'pkbaffi',
    partnerCode : 'PKBAFFI',
    priceStreamCode : 'pkbaffi', //not sure about this, later confirm.

    requires: [

        //'Ext.layout.container.Fit',
        //'snap.store.GopayzSalesPriceStream',
        'snap.store.pkbaffiSalesPriceStream'
    ],
});