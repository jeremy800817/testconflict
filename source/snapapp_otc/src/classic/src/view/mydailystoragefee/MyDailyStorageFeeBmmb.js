Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeBmmb', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mybmmbdailystoragefeeview',

    partnercode: 'BMMB',
    permissionRoot: '/root/bmmb/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=BMMB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
