Ext.define('snap.view.myledger.MyLedgerWavpay', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgerwavpayview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/wavpay/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=WAVPAY',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'WAVPAY',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
