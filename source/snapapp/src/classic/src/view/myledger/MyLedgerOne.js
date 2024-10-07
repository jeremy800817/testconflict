Ext.define('snap.view.myledger.MyLedgerOne', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgeroneview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/one/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=ONE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'ONE',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
