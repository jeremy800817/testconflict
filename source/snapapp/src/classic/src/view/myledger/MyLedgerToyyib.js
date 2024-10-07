Ext.define('snap.view.myledger.MyLedgerToyyib', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgertoyyibview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/toyyib/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=TOYYIB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'TOYYIB',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
