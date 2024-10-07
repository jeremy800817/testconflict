Ext.define('snap.view.myaccountclosure.MyAccountClosureHope', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'myhopeaccountclosureview',
    partnercode: 'HOPE',
    permissionRoot: '/root/hope/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=HOPE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
