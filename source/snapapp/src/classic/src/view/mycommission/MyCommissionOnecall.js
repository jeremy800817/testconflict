Ext.define('snap.view.mycommission.MyCommissionOnecall', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'myonecallcommissionview',
    partnercode: 'ONECALL',
    permissionRoot: '/root/onecall/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=ONECALL',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});