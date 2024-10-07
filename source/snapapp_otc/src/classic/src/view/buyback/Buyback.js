Ext.define('snap.view.buyback.Buyback', {
    extend:'Ext.panel.Panel',
    permissionRoot: '/root/mbb/buyback',
    xtype: 'buybackview',
    viewModel: {
        type: 'buyback-buyback'
    },    
    layout: {
        type: 'vbox',
        align: 'center'
    },    
    items: [
        { reference: 'buybackreq', ui: 'buybackrequests',  xtype: 'buybackrequests', partnercode: 'MIB'},
        { reference: 'buybacksummary', ui: 'buybacksummary',  xtype: 'buybacksummary'},
    ]
});