Ext.define('snap.view.myledger.MyLedgerGo', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgergoview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/go/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=GO',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'GO',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
