Ext.define('snap.view.orderdashboard.MySpotOrderSpecialKasih', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/kasih/sale',
    xtype: 'myspotorderspecialkasihview', 
    type: 'kasih',
    partnerCode : 'KASIH',
    priceStreamCode : 'Kasih', //not sure about this, later confirm.

    requires: [

        //'Ext.layout.container.Fit',
        //'snap.store.GopayzSalesPriceStream',
        'snap.store.KasihSalesPriceStream'
    ],
});