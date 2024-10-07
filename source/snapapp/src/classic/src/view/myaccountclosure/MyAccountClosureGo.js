Ext.define('snap.view.myaccountclosure.MyAccountClosureGo', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'mygoaccountclosureview',
    partnercode: 'GO',
    permissionRoot: '/root/go/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=GO',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
