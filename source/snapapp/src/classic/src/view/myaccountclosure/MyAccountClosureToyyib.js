Ext.define('snap.view.myaccountclosure.MyAccountClosureToyyib', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'mytoyyibaccountclosureview',
    partnercode: 'TOYYIB',
    permissionRoot: '/root/toyyib/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=TOYYIB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
