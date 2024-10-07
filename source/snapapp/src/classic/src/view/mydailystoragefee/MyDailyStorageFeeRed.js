Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeRed', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'myreddailystoragefeeview',

    partnercode: 'RED',
    permissionRoot: '/root/red/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=RED',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
