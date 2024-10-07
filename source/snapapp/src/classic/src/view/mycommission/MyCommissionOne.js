Ext.define('snap.view.mycommission.MyCommissionOne', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'myonecommissionview',
    partnercode: 'ONE',
    permissionRoot: '/root/one/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=ONE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});