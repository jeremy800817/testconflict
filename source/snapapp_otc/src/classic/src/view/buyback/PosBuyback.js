Ext.define('snap.view.buyback.PosBuyback', {
    extend:'Ext.panel.Panel',
    permissionRoot: '/root/pos/buyback',
    xtype: 'posbuybackview',
    viewModel: {
        type: 'buyback-buyback'
    },    
    layout: {
        type: 'vbox',
        align: 'center'
    },    
    items: [
        { reference: 'posbuybackreq', ui: 'posbuybackrequests',  xtype: 'posbuybackrequests', partnercode: 'POS'},
        { reference: 'posbuybacksummary', ui: 'posbuybacksummary',  xtype: 'posbuybacksummary'},
    ]
});