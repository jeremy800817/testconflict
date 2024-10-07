Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeIgold', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'myigolddailystoragefeeview',

    partnercode: 'IGOLD',
    permissionRoot: '/root/igold/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=IGOLD',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
