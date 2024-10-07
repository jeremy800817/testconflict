Ext.define('snap.view.orderdashboard.MySpotOrderSpecialMbsb', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/mbsb/sale',
    xtype: 'myspotorderspecialmbsbview', 
    type: 'mbsb',
    partnerCode : 'MBSB',
    priceStreamCode : 'Mbsb', //not sure about this, later confirm.

    requires: [

        //'Ext.layout.container.Fit',
        //'snap.store.GopayzSalesPriceStream',
        'snap.store.MbsbSalesPriceStream'
    ],
});