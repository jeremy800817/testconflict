Ext.define('snap.view.order.MbsbOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'mbsborderview',
    partnercode: 'MBSB',
    permissionRoot: '/root/mbsb/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=MBSB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
