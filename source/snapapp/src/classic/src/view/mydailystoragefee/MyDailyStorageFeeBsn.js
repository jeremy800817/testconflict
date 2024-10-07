Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeBsn', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mybsndailystoragefeeview',

    partnercode: 'BSN',
    permissionRoot: '/root/bsn/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=BSN',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
