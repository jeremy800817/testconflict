Ext.define('snap.view.myaccountclosure.MyAccountClosureBsn', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'mybsnaccountclosureview',
    partnercode: 'BSN',
    permissionRoot: '/root/bsn/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=BSN',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
