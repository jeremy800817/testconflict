Ext.define('snap.view.order.NubexOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'nubexorderview',
    partnercode: 'NUBEX',
    permissionRoot: '/root/nubex/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=NUBEX',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
