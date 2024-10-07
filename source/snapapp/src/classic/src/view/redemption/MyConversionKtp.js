Ext.define('snap.view.redemption.MyConversionKtp', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/ktp/redemption',
    xtype: 'ktpconversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'myconversionrequestsktp', partnercode: 'KTP',
        store: {
            type: 'MyConversion', proxy: {
                type: 'ajax',
                url: 'index.php?hdl=myconversion&action=list&partnercode=KTP',
                reader: {
                    type: 'json',
                    rootProperty: 'records',
                }
            },
        },},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'ktp'},
    ]
});