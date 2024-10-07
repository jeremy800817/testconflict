Ext.define('snap.view.order.HopeOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'hopeorderview',
    partnercode: 'HOPE',
    permissionRoot: '/root/hope/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=HOPE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
