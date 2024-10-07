Ext.define('snap.view.mycommission.MyCommissionGo', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'mygocommissionview',
    partnercode: 'GO',
    permissionRoot: '/root/go/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=GO',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});