Ext.define('snap.view.order.ToyyibOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'toyyiborderview',
    partnercode: 'TOYYIB',
    permissionRoot: '/root/toyyib/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=TOYYIB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
