Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeePosarrahnu', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'myposarrahnudailystoragefeeview',

    partnercode: 'POSARRAHNU',
    permissionRoot: '/root/posarrahnu/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=POSARRAHNU',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
