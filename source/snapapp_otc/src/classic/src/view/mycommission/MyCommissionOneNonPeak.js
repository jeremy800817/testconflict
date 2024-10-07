Ext.define('snap.view.mycommission.MyCommissionOneNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionOne',
    xtype: 'myonecommissionnonpeakview',
    partnercode: 'ONE',
    nonpeak: true,
    permissionRoot: '/root/one/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=ONE&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});