Ext.define('snap.view.redemption.MyConversionKgoldAffi', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/kgoldaffi/redemption',
    xtype: 'kgoldafficonversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'myconversionrequestsktp', partnercode: 'KGOLDAFFI',
        store: {
            type: 'MyConversion', proxy: {
                type: 'ajax',
                url: 'index.php?hdl=myconversion&action=list&partnercode=KGOLDAFFI',
                reader: {
                    type: 'json',
                    rootProperty: 'records',
                }
            },
        },},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'kgoldaffi'},
    ]
});