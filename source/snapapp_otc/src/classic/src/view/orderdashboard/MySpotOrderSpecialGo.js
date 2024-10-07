Ext.define('snap.view.orderdashboard.MySpotOrderSpecialGo', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/go/sale',
    xtype: 'myspotorderspecialgoview', 
    type: 'go',
    partnerCode : 'GO',
    priceStreamCode : 'Gopayz',

    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.GopayzSalesPriceStream',

    ],
});