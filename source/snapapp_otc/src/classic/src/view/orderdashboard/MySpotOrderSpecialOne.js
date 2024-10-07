Ext.define('snap.view.orderdashboard.MySpotOrderSpecialOne', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/one/sale',
    xtype: 'myspotorderspecialoneview', 
    type: 'one',
    partnerCode : 'ONE',
    priceStreamCode : 'One',
    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.OneSalesPriceStream',

    ],
});