Ext.define('snap.view.mycommission.MyCommissionMcash', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'mymcashcommissionview',
    partnercode: 'MCASH',
    permissionRoot: '/root/mcash/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=MCASH',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});