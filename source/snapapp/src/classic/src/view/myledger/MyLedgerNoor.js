Ext.define('snap.view.myledger.MyLedgerNoor', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgernoorview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/noor/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=NOOR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'NOOR',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
