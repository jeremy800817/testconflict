Ext.define('snap.view.redemption.MyConversionMbsb', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/mbsb/redemption',
    xtype: 'mbsbconversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'myconversionrequests', partnercode: 'MBSB',
        store: {
            type: 'MyConversion', proxy: {
                type: 'ajax',
                url: 'index.php?hdl=myconversion&action=list&partnercode=MBSB',
                reader: {
                    type: 'json',
                    rootProperty: 'records',
                }
            },
        },},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'mbsb'},
    ]
});