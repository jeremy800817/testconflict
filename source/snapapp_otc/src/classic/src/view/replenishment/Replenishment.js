Ext.define('snap.view.replenishment.Replenishment', {
    extend:'Ext.panel.Panel',
    permissionRoot: '/root/mbb/replenishment',
    xtype: 'replenishmentview',
    viewModel: {
        type: 'replenishment-replenishment'
    },    
    layout: {
        type: 'vbox',
        align: 'center'
    },    
    items: [
        { reference: 'replenishmentreq', ui: 'replenishmentrequests',  xtype: 'replenishmentrequests'},
        // { reference: 'buybacksummary', ui: 'buybacksummary',  xtype: 'buybacksummary'},
    ]
});