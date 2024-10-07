Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeNubex', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mynubexdailystoragefeeview',

    partnercode: 'NUBEX',
    permissionRoot: '/root/nubex/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=NUBEX',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
