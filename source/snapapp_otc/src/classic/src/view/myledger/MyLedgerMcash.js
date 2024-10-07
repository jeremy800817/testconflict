Ext.define('snap.view.myledger.MyLedgerMcash', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgermcashview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/mcash/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=MCASH',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'MCASH',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
