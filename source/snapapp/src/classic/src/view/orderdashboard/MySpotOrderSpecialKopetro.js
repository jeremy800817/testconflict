Ext.define('snap.view.orderdashboard.MySpotOrderSpecialKopetro', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/kopetro/sale',
    xtype: 'myspotorderspecialkopetroview', 
    type: 'kopetro',
    partnerCode : 'KOPETRO',
    priceStreamCode : 'kopetro', //not sure about this, later confirm.

    requires: [

        //'Ext.layout.container.Fit',
        //'snap.store.GopayzSalesPriceStream',
        'snap.store.kopetroSalesPriceStream'
    ],
});