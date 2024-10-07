Ext.define('snap.view.redemption.MyConversionOnecall', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/onecall/redemption',
    xtype: 'onecallconversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'myconversionrequests', partnercode: 'ONECALL',
        store: {
            type: 'MyConversion', proxy: {
                type: 'ajax',
                url: 'index.php?hdl=myconversion&action=list&partnercode=ONECALL',
                reader: {
                    type: 'json',
                    rootProperty: 'records',
                }
            },
        },},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'onecall'},
    ]
});