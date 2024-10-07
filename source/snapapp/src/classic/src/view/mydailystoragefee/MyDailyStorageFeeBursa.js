Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeBursa', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mybursadailystoragefeeview',

    partnercode: 'BURSA',
    permissionRoot: '/root/bursa/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=BURSA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
