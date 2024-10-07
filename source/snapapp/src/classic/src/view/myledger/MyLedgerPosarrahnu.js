Ext.define('snap.view.myledger.MyLedgerPosarrahnu', {
    extend: 'snap.view.myledger.MyLedger',
    xtype: 'myledgerposarrahnuview',

    requires: [
        'snap.store.MyLedger',
        'snap.model.MyLedger',
        'snap.view.myledger.MyLedgerController',
        'snap.view.myledger.MyLedgerModel',
    ],
    permissionRoot: '/root/posarrahnu/promo',
    store: {
        type: 'MyLedger',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myledger&action=list&partnercode=POSARRAHNU',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'POSARRAHNU',
    controller: 'myledger-myledger',

    viewModel: {
        type: 'myledger-myledger'
    },

});
