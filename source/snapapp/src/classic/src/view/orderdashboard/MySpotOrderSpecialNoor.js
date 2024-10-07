Ext.define('snap.view.orderdashboard.MySpotOrderSpecialNoor', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/noor/sale',
    xtype: 'myspotorderspecialnoorview', 
    type: 'noor',
    partnerCode : 'NOOR',
    priceStreamCode : 'Noor',

    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.NoorSalesPriceStream',

    ],
});