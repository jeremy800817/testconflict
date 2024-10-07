Ext.define('snap.view.mytransfergold.MyTransferGoldToyyib', {
    extend: 'snap.view.mytransfergold.MyTransferGold',
    xtype: 'mytransfergoldtoyyibview',

    requires: [
        'snap.store.MyTransferGold',
        'snap.model.MyTransferGold',
        'snap.view.mytransfergold.MyTransferGoldController',
        'snap.view.mytransfergold.MyTransferGoldModel',
    ],
    permissionRoot: '/root/toyyib/transfergold',
    store: {
        type: 'MyTransferGold',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mytransfergold&action=list&partnercode=TOYYIB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'TOYYIB',
    controller: 'mytransfergold-mytransfergold',

    viewModel: {
        type: 'mytransfergold-mytransfergold'
    },

});
