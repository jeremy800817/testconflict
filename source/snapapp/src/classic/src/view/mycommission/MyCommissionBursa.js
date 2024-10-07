Ext.define('snap.view.mycommission.MyCommissionBursa', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'mybursacommissionview',
    partnercode: 'BURSA',
    permissionRoot: '/root/bursa/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=BURSA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});