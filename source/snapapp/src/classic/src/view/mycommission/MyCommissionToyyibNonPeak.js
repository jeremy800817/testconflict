Ext.define('snap.view.mycommission.MyCommissionToyyibNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionToyyib',
    xtype: 'mytoyyibcommissionnonpeakview',
    partnercode: 'TOYYIB',
    nonpeak: true,
    permissionRoot: '/root/toyyib/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=TOYYIB&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});