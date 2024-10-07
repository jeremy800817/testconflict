Ext.define('snap.view.order.OnecallOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'onecallorderview',
    partnercode: 'ONECALL',
    permissionRoot: '/root/onecall/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=ONECALL',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
