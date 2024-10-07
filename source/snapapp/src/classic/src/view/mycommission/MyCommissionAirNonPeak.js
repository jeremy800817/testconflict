Ext.define('snap.view.mycommission.MyCommissionAirNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionAir',
    xtype: 'myaircommissionnonpeakview',
    partnercode: 'AIR',
    nonpeak: true,
    permissionRoot: '/root/air/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=AIR&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});