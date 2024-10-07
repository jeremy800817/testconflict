Ext.define('snap.view.mycommission.MyCommissionBsn', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'mybsncommissionview',
    partnercode: 'BSN',
    permissionRoot: '/root/bsn/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=BSN',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});