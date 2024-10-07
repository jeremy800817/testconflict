Ext.define('snap.view.order.WavpayOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'wavpayorderview',
    partnercode: 'WAVPAY',
    permissionRoot: '/root/wavpay/goldtransaction',
    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=WAVPAY',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
