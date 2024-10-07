Ext.define('snap.view.mytransfergold.MyTransferGoldPosarrahnu', {
    extend: 'snap.view.mytransfergold.MyTransferGold',
    xtype: 'mytransfergoldposarrahnuview',

    requires: [
        'snap.store.MyTransferGold',
        'snap.model.MyTransferGold',
        'snap.view.mytransfergold.MyTransferGoldController',
        'snap.view.mytransfergold.MyTransferGoldModel',
    ],
    permissionRoot: '/root/posarrahnu/transfergold',
    store: {
        type: 'MyTransferGold',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mytransfergold&action=list&partnercode=POSARRAHNU',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'POSARRAHNU',
    controller: 'mytransfergold-mytransfergold',

    viewModel: {
        type: 'mytransfergold-mytransfergold'
    },

});
