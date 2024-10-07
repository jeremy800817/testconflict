Ext.define('snap.view.mycommission.MyCommissionBsnNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionBsn',
    xtype: 'mybsncommissionnonpeakview',
    partnercode: 'BSN',
    nonpeak: true,
    permissionRoot: '/root/bsn/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=BSN&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});