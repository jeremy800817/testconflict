Ext.define('snap.view.orderdashboard.MySpotOrderSpecialWaqaf', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/waqaf/sale',
    xtype: 'myspotorderspecialwaqafview', 
    type: 'waqaf',
    partnerCode : 'WAQAF',
    priceStreamCode : 'Waqaf', //not sure about this, later confirm.

    requires: [

        //'Ext.layout.container.Fit',
        //'snap.store.GopayzSalesPriceStream',
        'snap.store.WaqafSalesPriceStream'
    ],
});