Ext.define('snap.view.redemption.MyConversionBsn', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/bsn/redemption',
    xtype: 'bsnconversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'myconversionrequests', partnercode: 'BSN', partneremail: "ang@silverstream.my", personalcourier: true,
        store: {
            type: 'MyConversion', proxy: {
                type: 'ajax',
                url: 'index.php?hdl=myconversion&action=list&partnercode=BSN',
                reader: {
                    type: 'json',
                    rootProperty: 'records',
                }
            },
        },},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'bsn'},
    ]
});