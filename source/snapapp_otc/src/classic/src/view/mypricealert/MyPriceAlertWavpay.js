Ext.define('snap.view.mypricealert.MyPriceAlertWavpay', {
    extend: 'snap.view.mypricealert.MyPriceAlert',
    xtype: 'wavpaypricealertview',
    permissionRoot: '/root/wavpay/pricealert',
    partnercode: 'WAVPAY',
    store: {
        type: 'MyPriceAlert', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mypricealert&action=list&partnercode=WAVPAY',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});
