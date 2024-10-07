Ext.define('snap.view.redemption.MyConversionIgold', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/igold/redemption',
    xtype: 'igoldconversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'myconversionrequests', partnercode: 'IGOLD', partneremail: "ang@silverstream.my", personalcourier: true,
        store: {
            type: 'MyConversion', proxy: {
                type: 'ajax',
                url: 'index.php?hdl=myconversion&action=list&partnercode=IGOLD',
                reader: {
                    type: 'json',
                    rootProperty: 'records',
                }
            },
        },},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'igold'},
    ]
});