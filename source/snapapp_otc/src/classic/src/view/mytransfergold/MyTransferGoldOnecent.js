Ext.define('snap.view.mytransfergold.MyTransferGoldOnecent', {
    extend: 'snap.view.mytransfergold.MyTransferGold',
    xtype: 'mytransfergoldoneview',

    requires: [
        'snap.store.MyTransferGold',
        'snap.model.MyTransferGold',
        'snap.view.mytransfergold.MyTransferGoldController',
        'snap.view.mytransfergold.MyTransferGoldModel',
    ],
    permissionRoot: '/root/one/transfergold',
    store: {
        type: 'MyTransferGold',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mytransfergold&action=list&partnercode=ONE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'ONE',
    controller: 'mytransfergold-mytransfergold',

    viewModel: {
        type: 'mytransfergold-mytransfergold'
    },

});
