Ext.define('snap.view.myaccountclosure.MyAccountClosureMbsb', {
    extend: 'snap.view.myaccountclosure.MyAccountClosure',
    xtype: 'mymbsbaccountclosureview',
    partnercode: 'MBSB',
    permissionRoot: '/root/mbsb/accountclosure',
    store: {
        type: 'MyAccountClosure', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountclosure&action=list&partnercode=MBSB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});
