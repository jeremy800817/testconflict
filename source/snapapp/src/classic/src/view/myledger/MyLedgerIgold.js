Ext.define('snap.view.myledger.MyLedgerIgold', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgerigoldview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/igold/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=IGOLD',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'IGOLD',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
