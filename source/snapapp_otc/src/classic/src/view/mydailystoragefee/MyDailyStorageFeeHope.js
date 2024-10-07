Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeHope', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'myhopedailystoragefeeview',

    partnercode: 'HOPE',
    permissionRoot: '/root/hope/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=HOPE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
