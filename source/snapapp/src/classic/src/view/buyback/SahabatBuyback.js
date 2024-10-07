Ext.define('snap.view.buyback.SAHABATBuyback', {
    extend:'Ext.panel.Panel',
    permissionRoot: '/root/sahabat/buyback',
    xtype: 'sahabatbuybackview',
    viewModel: {
        type: 'buyback-buyback'
    },    
    layout: {
        type: 'vbox',
        align: 'center'
    },    
    permissionRoot: '/root/sahabat/buyback', 
    items: [
        { reference: 'sahabatbuybackreq', ui: 'sahabatbuybackrequests',  xtype: 'sharedbuybackrequests', partnercode: 'SAHABAT',
            store: {
                type: 'SharedBuyback', proxy: {
                    type: 'ajax',
                    url: 'index.php?hdl=sharedbuyback&action=list&partner=sahabat',
                    reader: {
                        type: 'json',
                        rootProperty: 'records',
                    }
                },
            },
        },
        { reference: 'sahabatbuybacksummary', ui: 'sahabatbuybacksummary',  xtype: 'sharedbuybacksummary', partnercode: 'sahabat'},
    ]
});