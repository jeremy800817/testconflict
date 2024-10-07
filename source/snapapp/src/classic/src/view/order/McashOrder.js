Ext.define('snap.view.order.McashOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'mcashorderview',
    partnercode: 'MCASH',
    permissionRoot: '/root/mcash/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=MCASH',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
