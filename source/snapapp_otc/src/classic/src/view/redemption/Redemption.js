Ext.define('snap.view.redemption.Redemption', {
    extend:'Ext.panel.Panel',
    permissionRoot: '/root/mbb/redemption',
    xtype: 'redemptionview',
    viewModel: {
        type: 'redemption-redemption'
    },    
    layout: {
        type: 'vbox',
        align: 'center'
    },    
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'redemptionrequests'},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary'},
    ]
});