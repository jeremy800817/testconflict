Ext.define('snap.view.mycommission.MyCommissionNoor', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'mynoorcommissionview',
    partnercode: 'NOOR',
    permissionRoot: '/root/noor/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=NOOR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});