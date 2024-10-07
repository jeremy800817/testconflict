Ext.define('snap.view.orderdashboard.MySpotOrderSpecialBumira', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/bumira/sale',
    xtype: 'myspotorderspecialbumiraview', 
    type: 'bumira',
    partnerCode : 'BUMIRA',
    priceStreamCode : 'bumira', //not sure about this, later confirm.

    requires: [

        //'Ext.layout.container.Fit',
        //'snap.store.GopayzSalesPriceStream',
        'snap.store.bumiraSalesPriceStream'
    ],
});