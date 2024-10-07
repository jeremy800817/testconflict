Ext.define('snap.view.mycommission.MyCommissionNubex', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'mynubexcommissionview',
    partnercode: 'NUBEX',
    permissionRoot: '/root/nubex/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=NUBEX',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});