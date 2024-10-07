Ext.define('snap.view.orderdashboard.MySpotOrderSpecialOneCall', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/onecall/sale',
    xtype: 'myspotorderspecialonecallview', 
    type: 'onecall',
    partnerCode : 'ONECALL',
    priceStreamCode : 'Onecall',
    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.OnecallSalesPriceStream',

    ],
});