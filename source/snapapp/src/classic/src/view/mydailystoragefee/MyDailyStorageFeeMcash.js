Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeMcash', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mymcashdailystoragefeeview',

    partnercode: 'MCASH',
    permissionRoot: '/root/mcash/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=MCASH',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
