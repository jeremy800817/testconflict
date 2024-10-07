Ext.define('snap.view.mycommission.MyCommissionWavpay', {
    extend: 'snap.view.mycommission.MyCommission',
    xtype: 'mywavpaycommissionview',
    partnercode: 'WAVPAY',
    permissionRoot: '/root/wavpay/report/commission',
    store: {
        type: 'MyCommission', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mycommission&action=list&partnercode=WAVPAY',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

});