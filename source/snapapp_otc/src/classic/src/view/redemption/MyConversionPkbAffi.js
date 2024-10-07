Ext.define('snap.view.redemption.MyConversionPkbAffi', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/pkbaffi/redemption',
    xtype: 'pkbafficonversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'myconversionrequestspkbaffi', partnercode: 'PKBAFFI',
        store: {
            type: 'MyConversion', proxy: {
                type: 'ajax',
                url: 'index.php?hdl=myconversion&action=list&partnercode=PKBAFFI',
                reader: {
                    type: 'json',
                    rootProperty: 'records',
                }
            },
        },},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'pkbaffi'},
    ]
});