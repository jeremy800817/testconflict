Ext.define('snap.view.myaccountclosure.MyAccountClosureBursa', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'mybursaaccountclosureview',
    partnercode: 'BURSA',
    permissionRoot: '/root/bursa/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=BURSA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
