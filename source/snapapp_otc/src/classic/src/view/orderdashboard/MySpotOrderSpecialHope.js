Ext.define('snap.view.orderdashboard.MySpotOrderSpecialHope', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/hope/sale',
    xtype: 'myspotorderspecialhopeview', 
    type: 'hope',
    partnerCode : 'HOPE',
    priceStreamCode : 'Hope',

    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.HopeSalesPriceStream',

    ],
});