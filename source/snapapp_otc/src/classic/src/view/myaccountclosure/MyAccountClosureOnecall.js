Ext.define('snap.view.myaccountclosure.MyAccountClosureOnecall', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'myonecallaccountclosureview',
    partnercode: 'ONECALL',
    permissionRoot: '/root/onecall/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=ONECALL',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
