Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeOne', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'myonedailystoragefeeview',

    partnercode: 'ONE',
    permissionRoot: '/root/one/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=ONE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
