Ext.define('snap.view.redemption.MyConversionNubex', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/nubex/redemption',
    xtype: 'nubexconversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'myconversionrequests', partnercode: 'NUBEX', partneremail: "ang@silverstream.my", personalcourier: true,
        store: {
            type: 'MyConversion', proxy: {
                type: 'ajax',
                url: 'index.php?hdl=myconversion&action=list&partnercode=NUBEX',
                reader: {
                    type: 'json',
                    rootProperty: 'records',
                }
            },
        },},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'nubex'},
    ]
});