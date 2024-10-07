Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeGo', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mygodailystoragefeeview',

    partnercode: 'GO',
    permissionRoot: '/root/go/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=GO',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
