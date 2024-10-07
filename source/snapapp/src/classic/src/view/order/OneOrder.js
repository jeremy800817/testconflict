Ext.define('snap.view.order.OneOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'oneorderview',
    partnercode: 'ONE',
    permissionRoot: '/root/one/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=ONE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
