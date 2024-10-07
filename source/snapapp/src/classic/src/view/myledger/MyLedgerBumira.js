Ext.define('snap.view.myledger.MyLedgerBumira', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgerbumiraview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/bumira/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=BUMIRA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'BUMIRA',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
