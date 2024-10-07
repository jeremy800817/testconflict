Ext.define('snap.view.myledger.MyLedgerHope', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgerhopeview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/hope/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=HOPE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'HOPE',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
