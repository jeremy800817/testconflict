Ext.define('snap.view.myaccountclosure.MyAccountClosureWavpay', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'mywavpayaccountclosureview',
    partnercode: 'WAVPAY',
    permissionRoot: '/root/wavpay/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=WAVPAY',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
