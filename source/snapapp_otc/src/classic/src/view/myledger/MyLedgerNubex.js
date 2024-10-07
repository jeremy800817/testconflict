Ext.define('snap.view.myledger.MyLedgerNubex', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgernubexview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/nubex/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=NUBEX',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'NUBEX',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
