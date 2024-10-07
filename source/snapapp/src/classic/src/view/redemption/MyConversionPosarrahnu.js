Ext.define('snap.view.redemption.MyConversionPosarrahnu', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/posarrahnu/redemption',
    xtype: 'posarrahnuconversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'myconversionrequestsktp', partnercode: 'POSARRAHNU',
        store: {
            type: 'MyConversion', proxy: {
                type: 'ajax',
                url: 'index.php?hdl=myconversion&action=list&partnercode=POSARRAHNU',
                reader: {
                    type: 'json',
                    rootProperty: 'records',
                }
            },
        },},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'posarrahnu'},
    ]
});