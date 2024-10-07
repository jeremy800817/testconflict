Ext.define('snap.view.mycommission.MyCommissionNubexNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionNubex',
    xtype: 'mynubexcommissionnonpeakview',
    partnercode: 'NUBEX',
    nonpeak: true,
    permissionRoot: '/root/nubex/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=NUBEX&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});