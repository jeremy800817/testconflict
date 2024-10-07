Ext.define('snap.view.buyback.KOPONASBuyback', {
    extend:'Ext.panel.Panel',
    permissionRoot: '/root/koponas/buyback',
    xtype: 'koponasbuybackview',
    viewModel: {
        type: 'buyback-buyback'
    },    
    layout: {
        type: 'vbox',
        align: 'center'
    },    
    permissionRoot: '/root/koponas/buyback', 
    items: [
        { reference: 'koponasbuybackreq', ui: 'koponasbuybackrequests',  xtype: 'sharedbuybackrequests', partnercode: 'KOPONAS',
            store: {
                type: 'SharedBuyback', proxy: {
                    type: 'ajax',
                    url: 'index.php?hdl=sharedbuyback&action=list&partner=koponas',
                    reader: {
                        type: 'json',
                        rootProperty: 'records',
                    }
                },
            },
        },
        { reference: 'koponasbuybacksummary', ui: 'koponasbuybacksummary',  xtype: 'sharedbuybacksummary', partnercode: 'koponas'},
    ]
});