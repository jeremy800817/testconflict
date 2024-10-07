Ext.define('snap.view.mycommission.MyCommissionBmmb', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'mybmmbcommissionview',
    partnercode: 'BMMB',
    permissionRoot: '/root/bmmb/report/commission/list',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=BMMB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});