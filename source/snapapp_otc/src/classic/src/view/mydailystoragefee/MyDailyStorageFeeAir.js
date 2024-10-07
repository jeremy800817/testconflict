Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeAir', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'myairdailystoragefeeview',

    partnercode: 'AIR',
    permissionRoot: '/root/air/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=AIR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
