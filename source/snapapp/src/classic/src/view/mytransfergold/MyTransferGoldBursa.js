Ext.define('snap.view.mytransfergold.MyTransferGoldBursa', {
    extend: 'snap.view.mytransfergold.MyTransferGold',
    xtype: 'mytransfergoldbursaview',

    requires: [
        'snap.store.MyTransferGold',
        'snap.model.MyTransferGold',
        'snap.view.mytransfergold.MyTransferGoldController',
        'snap.view.mytransfergold.MyTransferGoldModel',
    ],
    permissionRoot: '/root/bursa/transfergold',
    store: {
        type: 'MyTransferGold',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mytransfergold&action=list&partnercode=BURSA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'BURSA',
    controller: 'mytransfergold-mytransfergold',

    viewModel: {
        type: 'mytransfergold-mytransfergold'
    },

});
