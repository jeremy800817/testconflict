Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeWavpay', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mywavpaymonthlystoragefeeview',

    partnercode: 'WAVPAY',
    permissionRoot: '/root/wavpay/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=WAVPAY',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});