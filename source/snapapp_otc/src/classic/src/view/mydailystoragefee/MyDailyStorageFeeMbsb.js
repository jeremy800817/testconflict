Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeMbsb', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mymbsbdailystoragefeeview',

    partnercode: 'MBSB',
    permissionRoot: '/root/mbsb/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=MBSB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
