Ext.define('snap.view.myaccountclosure.MyAccountClosureNoor', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'mynooraccountclosureview',
    partnercode: 'NOOR',
    permissionRoot: '/root/noor/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=NOOR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
