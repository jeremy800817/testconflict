Ext.define('snap.view.order.IgoldOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'igoldorderview',
    partnercode: 'IGOLD',
    permissionRoot: '/root/igold/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=IGOLD',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
