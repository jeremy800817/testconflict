Ext.define('snap.view.order.BsnOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'bsnorderview',
    partnercode: 'BSN',
    permissionRoot: '/root/bsn/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=BSN',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
