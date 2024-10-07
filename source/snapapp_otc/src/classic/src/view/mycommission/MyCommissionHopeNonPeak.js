Ext.define('snap.view.mycommission.MyCommissionHopeNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionHope',
    xtype: 'myhopecommissionnonpeakview',
    partnercode: 'HOPE',
    nonpeak: true,
    permissionRoot: '/root/hope/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=HOPE&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});