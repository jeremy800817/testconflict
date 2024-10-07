Ext.define('snap.view.mycommission.MyCommissionBursaNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionBursa',
    xtype: 'mybursacommissionnonpeakview',
    partnercode: 'BURSA',
    nonpeak: true,
    permissionRoot: '/root/bursa/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=BURSA&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});