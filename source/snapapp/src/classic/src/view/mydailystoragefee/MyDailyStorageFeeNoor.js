Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeNoor', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mynoordailystoragefeeview',

    partnercode: 'NOOR',
    permissionRoot: '/root/noor/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=NOOR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
