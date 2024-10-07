Ext.define('snap.view.orderdashboard.MySpotOrderSpecialWavpay', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/wavpay/sale',
    xtype: 'myspotorderspecialwavpayview', 
    type: 'wavpay',
    partnerCode : 'WAVPAY',
    priceStreamCode : 'Wavpay',
    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.WavpaySalesPriceStream',

    ],
});