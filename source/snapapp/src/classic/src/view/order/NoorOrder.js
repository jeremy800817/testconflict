Ext.define('snap.view.order.NoorOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'noororderview',
    partnercode: 'NOOR',
    permissionRoot: '/root/noor/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=NOOR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
