Ext.define('snap.view.mycommission.MyCommissionIgoldNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionIgold',
    xtype: 'myigoldcommissionnonpeakview',
    partnercode: 'IGOLD',
    nonpeak: true,
    permissionRoot: '/root/igold/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=IGOLD&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});