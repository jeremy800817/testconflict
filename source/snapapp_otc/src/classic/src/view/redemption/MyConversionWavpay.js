Ext.define('snap.view.redemption.MyConversionWavpay', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/wavpay/redemption',
    xtype: 'wavpayconversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'myconversionrequests', partnercode: 'WAVPAY', partneremail: "ang@silverstream.my", personalcourier: true,
        store: {
            type: 'MyConversion', proxy: {
                type: 'ajax',
                url: 'index.php?hdl=myconversion&action=list&partnercode=WAVPAY',
                reader: {
                    type: 'json',
                    rootProperty: 'records',
                }
            },
        },},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'wavpay'},
    ]
});