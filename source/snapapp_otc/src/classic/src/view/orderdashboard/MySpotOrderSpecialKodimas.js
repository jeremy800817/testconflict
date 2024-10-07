Ext.define('snap.view.orderdashboard.MySpotOrderSpecialKodimas', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/kodimas/sale',
    xtype: 'myspotorderspecialkodimasview', 
    type: 'kodimas',
    partnerCode : 'KODIMAS',
    priceStreamCode : 'Kodimas', //not sure about this, later confirm.

    requires: [

        //'Ext.layout.container.Fit',
        //'snap.store.GopayzSalesPriceStream',
        'snap.store.KodimasSalesPriceStream'
    ],
});