Ext.define('snap.view.order.BmmbOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'bmmborderview',
    partnercode: 'BMMB',
    permissionRoot: '/root/bmmb/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=BMMB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
