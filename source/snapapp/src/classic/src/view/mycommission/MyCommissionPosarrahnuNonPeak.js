Ext.define('snap.view.mycommission.MyCommissionPosarrahnuNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionPosarrahnu',
    xtype: 'myposarrahnucommissionnonpeakview',
    partnercode: 'POSARRAHNU',
    nonpeak: true,
    permissionRoot: '/root/posarrahnu/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=POSARRAHNU&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});