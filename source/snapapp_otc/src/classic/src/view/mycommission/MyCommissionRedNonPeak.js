Ext.define('snap.view.mycommission.MyCommissionRedNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionRed',
    xtype: 'myredcommissionnonpeakview',
    partnercode: 'RED',
    nonpeak: true,
    permissionRoot: '/root/red/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=RED&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});