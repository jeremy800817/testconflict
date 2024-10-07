Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeToyyib', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mytoyyibdailystoragefeeview',

    partnercode: 'TOYYIB',
    permissionRoot: '/root/toyyib/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=TOYYIB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
