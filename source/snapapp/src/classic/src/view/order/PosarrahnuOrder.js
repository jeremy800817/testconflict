Ext.define('snap.view.order.PosarrahnuOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'posarrahnuorderview',
    partnercode: 'POSARRAHNU',
    permissionRoot: '/root/posarrahnu/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=POSARRAHNU',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
