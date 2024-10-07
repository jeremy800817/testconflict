Ext.define('snap.view.order.RedOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'redorderview',
    partnercode: 'RED',
    permissionRoot: '/root/red/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=RED',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
