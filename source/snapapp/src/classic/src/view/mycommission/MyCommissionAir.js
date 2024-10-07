Ext.define('snap.view.mycommission.MyCommissionAir', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'myaircommissionview',
    partnercode: 'AIR',
    permissionRoot: '/root/air/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=AIR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});