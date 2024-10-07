Ext.define('snap.view.myadminfee.MyBmmbAdminFee', {
    extend: 'snap.view.myadminfee.MyAdminFee',
    xtype: 'mybmmbadminfeeview',
    partnercode: 'BMMB',
    permissionRoot: '/root/bmmb/report/adminfee',
    store: {
        type: 'MyAdminFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myadminfee&action=list&partnercode=BMMB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});