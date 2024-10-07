Ext.define('snap.view.orderdashboard.MySpotOrderSpecialBsn', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/bsn/sale',
    xtype: 'myspotorderspecialbsnview', 
    type: 'bsn',
    partnerCode : 'BSN',
    priceStreamCode : 'Bsn',
    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.BsnSalesPriceStream',

    ],
});