Ext.define('snap.view.orderdashboard.MySpotOrderSpecialIgold', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/igold/sale',
    xtype: 'myspotorderspecialigoldview', 
    type: 'igold',
    partnerCode : 'IGOLD',
    priceStreamCode : 'Igold',
    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.IgoldSalesPriceStream',

    ],
});