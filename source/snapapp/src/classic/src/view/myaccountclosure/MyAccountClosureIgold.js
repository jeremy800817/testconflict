Ext.define('snap.view.myaccountclosure.MyAccountClosureIgold', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'myigoldaccountclosureview',
    partnercode: 'IGOLD',
    permissionRoot: '/root/igold/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=IGOLD',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
