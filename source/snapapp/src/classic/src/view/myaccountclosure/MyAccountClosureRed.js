Ext.define('snap.view.myaccountclosure.MyAccountClosureRed', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'myredaccountclosureview',
    partnercode: 'RED',
    permissionRoot: '/root/red/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=RED',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
