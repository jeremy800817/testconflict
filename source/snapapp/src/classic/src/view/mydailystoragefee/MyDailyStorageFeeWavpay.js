Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeWavpay', {
    extend: 'snap.view.mydailystoragefee.MyDailyStorageFee',
    xtype: 'mywavpaydailystoragefeeview',

    partnercode: 'WAVPAY',
    permissionRoot: '/root/wavpay/report/storagefee',

    store: {
        type: 'MyDailyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mydailystoragefee&action=list&partnercode=WAVPAY',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
