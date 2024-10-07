Ext.define('snap.view.logistics.WavpayLogistic',{
    extend: 'snap.view.logistics.MyLogistic',
    xtype: 'wavpaylogisticview',

    permissionRoot: '/root/wavpay/logistic',
    partnerCode: 'WAVPAY',

    store: {
        type: 'MyLogistic', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mylogistic&action=list&partnercode=WAVPAY',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
