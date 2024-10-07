Ext.define('snap.view.orderdashboard.MySpotOrderSpecialNubex', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/nubex/sale',
    xtype: 'myspotorderspecialnubexview', 
    type: 'nubex',
    partnerCode : 'NUBEX',
    priceStreamCode : 'Nubex',
    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.NubexSalesPriceStream',

    ],
});