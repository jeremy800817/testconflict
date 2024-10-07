Ext.define('snap.view.mycommission.MyCommissionOnecallNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionOnecall',
    xtype: 'myonecallcommissionnonpeakview',
    partnercode: 'ONECALL',
    nonpeak: true,
    permissionRoot: '/root/onecall/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=ONECALL&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});