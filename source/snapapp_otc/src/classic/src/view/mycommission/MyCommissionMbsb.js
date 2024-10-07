Ext.define('snap.view.mycommission.MyCommissionMbsb', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'mymbsbcommissionview',
    partnercode: 'MBSB',
    permissionRoot: '/root/mbsb/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=MBSB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});