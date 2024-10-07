Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeOnecall', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'myonecalldailystoragefeeview',

    partnercode: 'ONECALL',
    permissionRoot: '/root/onecall/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=ONECALL',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
