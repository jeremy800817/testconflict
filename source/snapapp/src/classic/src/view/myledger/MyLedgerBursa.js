Ext.define('snap.view.myledger.MyLedgerBursa', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgerbursaview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/bursa/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=BURSA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'BURSA',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
