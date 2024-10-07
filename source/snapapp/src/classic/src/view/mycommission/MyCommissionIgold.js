Ext.define('snap.view.mycommission.MyCommissionIgold', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'myigoldcommissionview',
    partnercode: 'IGOLD',
    permissionRoot: '/root/igold/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=IGOLD',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});