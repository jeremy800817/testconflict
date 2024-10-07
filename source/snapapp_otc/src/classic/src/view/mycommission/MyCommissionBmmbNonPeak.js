Ext.define('snap.view.mycommission.MyCommissionBmmbNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionBmmb',
    xtype: 'mybmmbcommissionnonpeakview',
    partnercode: 'BMMB',
    nonpeak: true,
    permissionRoot: '/root/bmmb/report/commission/list',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=BMMB&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});