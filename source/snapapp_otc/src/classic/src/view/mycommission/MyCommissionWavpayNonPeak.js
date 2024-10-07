Ext.define('snap.view.mycommission.MyCommissionWavpayNonPeak', {
    extend: 'snap.view.mycommission.MyCommissionWavpay',
    xtype: 'mywavpaycommissionnonpeakview',
    partnercode: 'WAVPAY',
    nonpeak: true,
    permissionRoot: '/root/wavpay/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=WAVPAY&nonpeak=true',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});