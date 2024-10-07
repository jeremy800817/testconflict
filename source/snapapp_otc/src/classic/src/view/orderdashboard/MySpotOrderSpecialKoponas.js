Ext.define('snap.view.orderdashboard.MySpotOrderSpecialKoponas', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/koponas/sale',
    xtype: 'myspotorderspecialkoponasview', 
    type: 'koponas',
    partnerCode : 'KOPONAS',
    priceStreamCode : 'Koponas', //not sure about this, later confirm.

    requires: [

        //'Ext.layout.container.Fit',
        //'snap.store.GopayzSalesPriceStream',
        'snap.store.KoponasSalesPriceStream'
    ],
});