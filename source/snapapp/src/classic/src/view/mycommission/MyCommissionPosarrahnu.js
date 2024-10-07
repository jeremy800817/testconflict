Ext.define('snap.view.mycommission.MyCommissionPosarrahnu', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'myposarrahnucommissionview',
    partnercode: 'POSARRAHNU',
    permissionRoot: '/root/posarrahnu/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=POSARRAHNU',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});