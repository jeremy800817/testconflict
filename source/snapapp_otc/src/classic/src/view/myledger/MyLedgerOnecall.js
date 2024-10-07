Ext.define('snap.view.myledger.MyLedgerOnecall', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgeronecallview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/onecall/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=ONECALL',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'ONECALL',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
