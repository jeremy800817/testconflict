Ext.define('snap.view.mycommission.MyCommissionRed', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'myredcommissionview',
    partnercode: 'RED',
    permissionRoot: '/root/red/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=RED',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});