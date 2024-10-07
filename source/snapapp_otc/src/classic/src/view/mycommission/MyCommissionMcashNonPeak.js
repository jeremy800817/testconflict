Ext.define('snap.view.mycommission.MyCommissionMcashNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionMcash',
    xtype: 'mymcashcommissionnonpeakview',
    partnercode: 'MCASH',
    nonpeak: true,
    permissionRoot: '/root/mcash/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=MCASH&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});