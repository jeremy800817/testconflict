Ext.define('snap.view.myledger.MyLedgerRed', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgerredview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/red/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=RED',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'RED',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
