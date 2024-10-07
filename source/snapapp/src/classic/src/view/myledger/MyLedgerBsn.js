Ext.define('snap.view.myledger.MyLedgerBsn', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgerbsnview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/bsn/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=BSN',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'BSN',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
