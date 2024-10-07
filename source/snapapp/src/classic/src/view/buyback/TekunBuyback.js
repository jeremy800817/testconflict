Ext.define('snap.view.buyback.TekunBuyback', {
    extend:'Ext.panel.Panel',
    permissionRoot: '/root/tekun/buyback',
    xtype: 'tekunbuybackview',
    viewModel: {
        type: 'buyback-buyback'
    },    
    layout: {
        type: 'vbox',
        align: 'center'
    },    
    permissionRoot: '/root/tekun/buyback',
    items: [
        { reference: 'tekunbuybackreq', ui: 'tekunbuybackrequests',  xtype: 'sharedbuybackrequests', partnercode: 'TEKUN',
            store: {
                type: 'SharedBuyback', proxy: {
                    type: 'ajax',
                    url: 'index.php?hdl=sharedbuyback&action=list&partner=tekun',
                    reader: {
                        type: 'json',
                        rootProperty: 'records',
                    }
                },
            },
        },
        { reference: 'tekunbuybacksummary', ui: 'tekunbuybacksummary',  xtype: 'sharedbuybacksummary', partnercode: 'tekun'},
    ]
});