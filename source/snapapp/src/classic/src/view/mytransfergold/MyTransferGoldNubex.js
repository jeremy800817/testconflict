Ext.define('snap.view.mytransfergold.MyTransferGoldNubex', {
    extend: 'snap.view.mytransfergold.MyTransferGold',
    xtype: 'mytransfergoldnubexview',

    requires: [
        'snap.store.MyTransferGold',
        'snap.model.MyTransferGold',
        'snap.view.mytransfergold.MyTransferGoldController',
        'snap.view.mytransfergold.MyTransferGoldModel',
    ],
    permissionRoot: '/root/nubex/transfergold',
    store: {
        type: 'MyTransferGold',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mytransfergold&action=list&partnercode=NUBEX',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'NUBEX',
    controller: 'mytransfergold-mytransfergold',

    viewModel: {
        type: 'mytransfergold-mytransfergold'
    },

});
