Ext.define('snap.view.mycommission.MyCommissionNoorNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionNoor',
    xtype: 'mynoorcommissionnonpeakview',
    partnercode: 'NOOR',
    nonpeak: true,
    permissionRoot: '/root/noor/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=NOOR&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});