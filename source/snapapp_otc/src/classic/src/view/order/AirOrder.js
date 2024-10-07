Ext.define('snap.view.order.AirOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'airorderview',
    partnercode: 'AIR',
    permissionRoot: '/root/air/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=AIR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
