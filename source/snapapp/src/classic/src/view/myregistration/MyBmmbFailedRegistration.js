Ext.define('snap.view.myregistration.MyBmmbFailedRegistration', {
    extend: 'snap.view.myregistration.MyRegistration',
    xtype: 'mybmmbfailedregistrationview',
    partnercode: 'BMMB',
    status: 'FAILED',
    permissionRoot: '/root/bmmb/report/registration',
    store: {
        type: 'MyRegistration', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myregistration&action=list&partnercode=BMMB&status=FAILED',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});