Ext.define('snap.view.myaccountclosure.MyAccountClosureMcash', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'mymcashaccountclosureview',
    partnercode: 'MCASH',
    permissionRoot: '/root/mcash/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=MCASH',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
