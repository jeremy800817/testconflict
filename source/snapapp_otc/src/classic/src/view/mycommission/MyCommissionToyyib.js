Ext.define('snap.view.mycommission.MyCommissionToyyib', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'mytoyyibcommissionview',
    partnercode: 'TOYYIB',
    permissionRoot: '/root/toyyib/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=TOYYIB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});