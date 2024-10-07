Ext.define('snap.view.mycommission.MyCommissionHope', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'myhopecommissionview',
    partnercode: 'HOPE',
    permissionRoot: '/root/hope/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=HOPE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});