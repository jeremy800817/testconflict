Ext.define('snap.view.mycommission.MyCommissionGoNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionGo',
    xtype: 'mygocommissionnonpeakview',
    partnercode: 'GO',
    nonpeak: true,
    permissionRoot: '/root/go/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=GO&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});